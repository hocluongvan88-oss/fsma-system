<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\ErrorLog;
use App\Models\AuditLog;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard cho regular admin (không phải system admin)
     * Hiển thị thông tin quản lý tổ chức và người dùng
     */
    public function index()
    {
        $user = auth()->user();
        
        // Regular admin chỉ xem dữ liệu của tổ chức của họ
        $organizationId = $user->organization_id;
        
        $data = [
            'total_users' => User::where('organization_id', $organizationId)->count(),
            'active_users' => User::where('organization_id', $organizationId)
                ->where('last_login', '>=', now()->subDays(30))
                ->count(),
            'recent_errors' => ErrorLog::where('organization_id', $organizationId)
                ->latest()
                ->limit(10)
                ->get(),
            'recent_audit_logs' => AuditLog::where('organization_id', $organizationId)
                ->latest()
                ->limit(10)
                ->get(),
            'organization' => Organization::find($organizationId),
        ];

        return view('admin.dashboard', $data);
    }
}
