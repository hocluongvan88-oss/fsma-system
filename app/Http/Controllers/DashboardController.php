<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;
use App\Models\TraceRecord;
use App\Models\CTEEvent;
use App\Models\ESignature;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $packageNames = [
            'free' => 'Free Tier',
            'basic' => 'Basic',
            'premium' => 'Premium',
            'enterprise' => 'Enterprise',
        ];
        
        $stats = [
            'total_products' => Product::count(),
            'ftl_products' => Product::ftl()->count(),
            'total_locations' => Location::count(),
            'total_partners' => Partner::count(),
            'active_inventory' => TraceRecord::active()->count(),
            'total_inventory_qty' => (float) TraceRecord::active()->sum('quantity'),
            'recent_events' => CTEEvent::with(['traceRecord.product', 'location', 'creator'])
                ->latest('event_date')
                ->take(10)
                ->get(),
            'recent_signatures' => ESignature::where('created_at', '>', now()->subDays(30))
                ->count(),
        ];

        $cteUsage = (int) $user->getCteUsageThisMonth();
        $cteLimit = (int) ($user->max_cte_records_monthly ?: 999999);
        $documentCount = (int) $user->getDocumentCount();
        $documentLimit = (int) ($user->max_documents ?: 999999);
        $userCount = (int) $user->getActiveUserCount();
        $userLimit = (int) ($user->max_users ?: 999999);
        
        // Calculate percentage safely
        $ctePercentage = $cteLimit > 0 ? ($cteUsage / $cteLimit) * 100 : 0;

        $packageStats = [
            'package_name' => $packageNames[$user->package_id] ?? strtoupper($user->package_id),
            'package_slug' => $user->package_id,
            'cte_usage' => $cteUsage,
            'cte_limit' => $cteLimit,
            'cte_percentage' => $ctePercentage,
            'document_count' => $documentCount,
            'document_limit' => $documentLimit,
            'user_count' => $userCount,
            'user_limit' => $userLimit,
            'show_warning' => $ctePercentage >= 80,
            'can_upgrade' => !in_array($user->package_id, ['enterprise']),
        ];

        // Events by type for chart
        $eventsByType = CTEEvent::select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        // Recent activity timeline
        $recentActivity = CTEEvent::with(['traceRecord.product', 'location', 'creator'])
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'eventsByType', 'recentActivity', 'packageStats'));
    }
}
