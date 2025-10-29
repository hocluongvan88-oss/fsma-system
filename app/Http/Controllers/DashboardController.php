<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use App\Models\TraceRecord;
use App\Models\CTEEvent;
use App\Models\Document;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isSystemAdmin()) {
            return redirect()->route('admin.system-dashboard');
        }

        $organization = $user->organization;
        
        if (!$organization) {
            return view('dashboard', [
                'stats' => [
                    'total_products' => 0,
                    'ftl_products' => 0,
                    'active_inventory' => 0,
                    'total_inventory_qty' => 0,
                    'total_locations' => 0,
                    'total_partners' => 0,
                    'recent_events' => []
                ],
                'packageStats' => [
                    'package_name' => 'N/A',
                    'cte_usage' => 0,
                    'cte_limit' => 0,
                    'cte_percentage' => 0,
                    'show_warning' => false,
                    'document_count' => 0,
                    'document_limit' => 0,
                    'user_count' => 0,
                    'user_limit' => 0
                ]
            ]);
        }

        $userPackage = $user->userPackage;
        $package = $userPackage?->package;
        
        $cteCount = CTEEvent::where('organization_id', $organization->id)->count();
        $documentCount = Document::where('organization_id', $organization->id)->count();
        $userCount = User::where('organization_id', $organization->id)->count();
        
        $cteLimit = $package?->cte_limit ?? 0;
        $documentLimit = $package?->document_limit ?? 0;
        $userLimit = $package?->user_limit ?? 0;
        
        $ctePercentage = $cteLimit > 0 ? ($cteCount / $cteLimit) * 100 : 0;
        
        $packageStats = [
            'package_name' => $package?->package_name ?? 'Free',
            'cte_usage' => $cteCount,
            'cte_limit' => $cteLimit,
            'cte_percentage' => $ctePercentage,
            'show_warning' => $ctePercentage >= 80,
            'document_count' => $documentCount,
            'document_limit' => $documentLimit,
            'user_count' => $userCount,
            'user_limit' => $userLimit
        ];

        $stats = [
            'total_products' => $organization->products()->count(),
            'ftl_products' => $organization->products()->where('is_ftl', true)->count(),
            'active_inventory' => $organization->traceRecords()->where('status', 'active')->count(),
            'total_inventory_qty' => $organization->traceRecords()->where('status', 'active')->sum('quantity') ?? 0,
            'total_locations' => $organization->locations()->count(),
            'total_partners' => $organization->partners()->count(),
            'recent_events' => CTEEvent::where('organization_id', $organization->id)
                ->with(['traceRecord', 'traceRecord.product', 'location', 'creator'])
                ->orderBy('event_date', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('dashboard', [
            'stats' => $stats,
            'packageStats' => $packageStats,
            'is_system_admin' => false
        ]);
    }
}
