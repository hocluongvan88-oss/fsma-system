<?php

namespace App\Http\Controllers;

use App\Models\RetentionPolicy;
use App\Models\RetentionLog;
use App\Models\AuditLog;
use App\Services\DataRetentionService;
use Illuminate\Http\Request;

class RetentionPolicyController extends Controller
{
    protected DataRetentionService $retentionService;

    public function __construct(DataRetentionService $retentionService)
    {
        $this->retentionService = $retentionService;
        $this->middleware('ensure.compliance.officer.access');
    }

    public function index()
    {
        $policies = RetentionPolicy::withoutTrashed()->get();
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'policy_name' => 'required|string|unique:retention_policies',
            'retention_months' => 'required|integer|min:' . DataRetentionService::FSMA_204_MINIMUM_RETENTION_MONTHS . '|max:120',
            'backup_before_deletion' => 'boolean',
            'description' => 'nullable|string',
            'data_type' => 'required|string|in:' . implode(',', DataRetentionService::DELETABLE_DATA_TYPES),
        ]);

        $validated['organization_id'] = auth()->user()?->organization_id;
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
            $policy = $this->retentionService->createPolicy($validated);
            
            AuditLog::createLog([
                'user_id' => auth()->user()->id,
                'action' => 'retention_policy_created',
                'table_name' => 'retention_policies',
                'record_id' => $policy->id,
                'organization_id' => auth()->user()->organization_id,
                'new_values' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return redirect()->route('admin.retention.index')
                ->with('success', __('messages.retention_policy_created'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['data_type' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(RetentionPolicy $policy)
    {
        $this->authorize('view', $policy);
        
        return response()->json([
            'success' => true,
            'data' => $policy,
        ], 200);
    }

    public function update(Request $request, RetentionPolicy $policy)
    {
        $this->authorize('update', $policy);
        
        $validated = $request->validate([
            'retention_months' => 'required|integer|min:' . DataRetentionService::FSMA_204_MINIMUM_RETENTION_MONTHS . '|max:120',
            'backup_before_deletion' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->user()->email;
        
        // Store original values for audit log
        $originalValues = $policy->only(array_keys($validated));
        
        $policy->update($validated);

        AuditLog::createLog([
            'user_id' => auth()->user()->id,
            'action' => 'retention_policy_updated',
            'table_name' => 'retention_policies',
            'record_id' => $policy->id,
            'organization_id' => auth()->user()->organization_id,
            'old_values' => $originalValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.retention.index')
            ->with('success', __('messages.retention_policy_updated'));
    }

    public function destroy(RetentionPolicy $policy)
    {
        $this->authorize('delete', $policy);
        
        AuditLog::createLog([
            'user_id' => auth()->user()->id,
            'action' => 'retention_policy_deleted',
            'table_name' => 'retention_policies',
            'record_id' => $policy->id,
            'organization_id' => auth()->user()->organization_id,
            'old_values' => $policy->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        $policy->delete();

        return redirect()->route('admin.retention.index')
            ->with('success', __('messages.retention_policy_deleted'));
    }

    public function execute(Request $request, RetentionPolicy $policy)
    {
        $this->authorize('execute', $policy);
        
        $dryRun = $request->boolean('dry_run', false);
        $result = $this->retentionService->executeCleanup($policy->data_type, $dryRun);

        if ($result['status'] === 'blocked') {
            return redirect()->route('admin.retention.index')
                ->withErrors(['execution' => $result['reason']]);
        }

        AuditLog::createLog([
            'user_id' => auth()->user()->id,
            'action' => $dryRun ? 'retention_cleanup_dry_run' : 'retention_cleanup_executed',
            'table_name' => 'retention_policies',
            'record_id' => $policy->id,
            'organization_id' => auth()->user()->organization_id,
            'new_values' => [
                'data_type' => $policy->data_type,
                'records_deleted' => $result['records_deleted'],
                'records_backed_up' => $result['records_backed_up'],
                'backup_path' => $result['backup_file_path'],
                'status' => $result['status'],
                'dry_run' => $dryRun,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $message = $dryRun 
            ? __('messages.dry_run_completed', ['count' => $result['records_deleted']])
            : __('messages.cleanup_completed', ['count' => $result['records_deleted']]);

        return redirect()->route('admin.retention.index')
            ->with('success', $message);
    }

    public function logs()
    {
        $this->authorize('viewLogs', RetentionPolicy::class);
        
        $logs = RetentionLog::latest('executed_at')->paginate(20);

        return view('admin.retention.logs', [
            'logs' => $logs,
        ]);
    }
}
