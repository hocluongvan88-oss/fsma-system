<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationAuditLog;
use Illuminate\Http\Request;

class NotificationAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = NotificationAuditLog::with(['user', 'notification', 'organization'])
            ->orderBy('created_at', 'desc');

        // Filter by organization
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
        }

        $logs = $query->paginate(50);

        return view('admin.notifications.audit-logs', compact('logs'));
    }

    public function show(NotificationAuditLog $log)
    {
        return view('admin.notifications.audit-log-detail', compact('log'));
    }

    public function export(Request $request)
    {
        $query = NotificationAuditLog::with(['user', 'notification', 'organization'])
            ->orderBy('created_at', 'desc');

        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->get();

        $csv = "ID,Organization,User,Notification ID,Action,Status,Details,IP Address,Created At\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log->id,
                $log->organization->name ?? 'N/A',
                $log->user->name ?? 'N/A',
                $log->notification_id ?? 'N/A',
                $log->action,
                $log->status,
                json_encode($log->details),
                $log->ip_address,
                $log->created_at
            );
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="notification-audit-logs.csv"');
    }
}
