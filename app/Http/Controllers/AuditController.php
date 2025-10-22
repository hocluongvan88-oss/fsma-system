<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->middleware('throttle:60,1')->only(['index', 'show']);
        $this->middleware('throttle:5,60')->only(['export']);
        $this->auditLogService = $auditLogService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);
        
        $currentUser = auth()->user();
        $query = AuditLog::with('user');

        if (!$currentUser->isAdmin()) {
            $query->whereHas('user', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            });
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by table
        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Search by action
        if ($request->filled('search')) {
            $query->where('action', 'like', '%' . $request->search . '%');
        }

        $logs = $query->latest('created_at')->paginate(50);
        
        $usersQuery = User::orderBy('full_name');
        if (!$currentUser->isAdmin()) {
            $usersQuery->where('organization_id', $currentUser->organization_id)
                       ->where('email', '!=', 'admin@fsma204.com');
        }
        $users = $usersQuery->get();
        
        $tables = AuditLog::distinct()->pluck('table_name')->filter()->sort();

        return view('reports.audit-log', compact('logs', 'users', 'tables'));
    }

    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        
        $this->authorize('view', $log);
        
        return response()->json([
            'log' => $log,
            'changes' => $log->getChanges(),
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $currentUser = auth()->user();
        $query = AuditLog::with('user');

        // Enforce organization isolation
        if (!$currentUser->isAdmin()) {
            $query->whereHas('user', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            });
        }

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Limit export to 10,000 records max
        $logs = $query->latest('created_at')->limit(10000)->get();

        // Log the export operation
        $this->auditLogService->log(
            'EXPORT_AUDIT_TRAIL',
            'audit_logs',
            0,
            null,
            [
                'filters' => $request->only(['user_id', 'table_name', 'start_date', 'end_date']),
                'record_count' => $logs->count(),
            ]
        );

        // Generate CSV
        $filename = 'audit-trail-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Action',
                'Table',
                'Record ID',
                'IP Address',
                'Integrity Hash',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->full_name ?? 'System',
                    $log->action,
                    $log->table_name,
                    $log->record_id,
                    $log->ip_address,
                    $log->integrity_hash ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
