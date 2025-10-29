<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Package;
use App\Models\TraceRecord;

class SystemDashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_organizations' => Organization::count(),
            'total_users' => User::count(),
            'active_users' => User::where('last_login', '>=', now()->subDays(30))->count(),
            'total_cte_records' => TraceRecord::count(),
            'database_size' => 'N/A',
            'storage_used' => 'N/A',
            'error_rate' => 0,
            'package_distribution' => Package::withCount('organizations')->orderByDesc('organizations_count')->get(),
            'recent_organizations' => Organization::with('package')->withCount('users')->latest('created_at')->limit(10)->get(),
            'top_organizations' => Organization::withCount(['traceRecords as cte_count', 'documents as document_count'])->orderByDesc('cte_count')->limit(10)->get(),
        ];

        return view('admin.system-dashboard', $data);
    }
}
