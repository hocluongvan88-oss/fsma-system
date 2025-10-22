<?php

namespace App\Http\Controllers;

use App\Models\RetentionPolicy;
use App\Models\RetentionLog;
use App\Services\DataRetentionService;
use Illuminate\Http\Request;

class RetentionPolicyController extends Controller
{
    protected DataRetentionService $retentionService;

    public function __construct(DataRetentionService $retentionService)
    {
        $this->retentionService = $retentionService;
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show retention policies management page
     */
    public function index()
    {
        $policies = RetentionPolicy::all();
        $stats = $this->retentionService->getRetentionStats();
        $recentLogs = RetentionLog::latest('executed_at')->limit(10)->get();

        $protectedDataTypes = [
            'trace_records' => 'Core traceability data required for FSMA 204 compliance',
            'cte_events' => 'Immutable Critical Tracking Events per FSMA 204 Section 204.6',
            'audit_logs' => 'Compliance and regulatory audit requirement',
            'e_signatures' => 'Legal requirement per 21 CFR Part 11 (Electronic Records)',
        ];

        $deletableDataTypes = [
            'error_logs' => 'System error logs (can be deleted after retention period)',
            'notifications' => 'User notifications (can be deleted after retention period)',
        ];

        return view('admin.retention.index', [
            'policies' => $policies,
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'protectedDataTypes' => $protectedDataTypes,
            'deletableDataTypes' => $deletableDataTypes,
        ]);
    }

    /**
     * Create new retention policy
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'policy_name' => 'required|string|unique:retention_policies',
            'data_type' => 'required|string|in:error_logs,notifications',
            'retention_months' => 'required|integer|min:0|max:120',
            'backup_before_deletion' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->user()->email;

        $validation = $this->retentionService->validateRetentionPolicy(
            $validated['data_type'],
            $validated['retention_months']
        );

        if (!$validation['valid']) {
            return redirect()->back()
                ->withErrors($validation['errors'])
                ->withInput();
        }

        try {
            $this->retentionService->createPolicy($validated);
            return redirect()->route('retention.index')
                ->with('success', __('messages.retention_policy_created'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['data_type' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Get policy data for editing (returns JSON)
     */
    public function edit(RetentionPolicy $policy)
    {
        return response()->json($policy);
    }

    /**
     * Update retention policy
     */
    public function update(Request $request, RetentionPolicy $policy)
    {
        $validated = $request->validate([
            'retention_months' => 'required|integer|min:0|max:120',
            'backup_before_deletion' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->user()->email;

        $policy->update($validated);

        return redirect()->route('retention.index')
            ->with('success', __('messages.retention_policy_updated'));
    }

    /**
     * Execute retention cleanup
     */
    public function execute(Request $request, RetentionPolicy $policy)
    {
        $dryRun = $request->boolean('dry_run', false);
        $result = $this->retentionService->executeCleanup($policy->data_type, $dryRun);

        if ($result['status'] === 'blocked') {
            return redirect()->route('retention.index')
                ->withErrors(['execution' => $result['reason']]);
        }

        $message = $dryRun 
            ? __('messages.dry_run_completed', ['count' => $result['records_deleted']])
            : __('messages.cleanup_completed', ['count' => $result['records_deleted']]);

        return redirect()->route('retention.index')
            ->with('success', $message);
    }

    /**
     * View retention logs
     */
    public function logs()
    {
        $logs = RetentionLog::latest('executed_at')->paginate(20);

        return view('admin.retention.logs', [
            'logs' => $logs,
        ]);
    }
}
