<?php

namespace App\Http\Controllers;

use App\Models\TraceRecord;
use App\Models\CTEEvent;
use App\Models\Product;
use App\Models\AuditLog;
use App\Models\TraceabilityAnalytics;
use App\Services\TraceabilityService;
use App\Services\GS1Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $traceabilityService;
    protected $gs1Service;

    public function __construct(TraceabilityService $traceabilityService, GS1Service $gs1Service)
    {
        $this->traceabilityService = $traceabilityService;
        $this->gs1Service = $gs1Service;
    }

    public function traceability(Request $request)
    {
        $query = TraceRecord::with(['product', 'location']);

        // Apply filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tlc', 'like', "%{$search}%")
                  ->orWhere('lot_code', 'like', "%{$search}%");
            });
        }

        $records = $query->latest()->paginate(20);

        // Get filter options
        $products = Product::ftl()->orderBy('product_name')->get();
        $locations = \App\Models\Location::orderBy('location_name')->get();

        return view('reports.traceability', compact('records', 'products', 'locations'));
    }

    public function queryTraceability(Request $request)
    {
        $validated = $request->validate([
            'tlc' => 'required|string',
            'direction' => 'required|in:backward,forward,both',
        ]);

        $tlc = $validated['tlc'];
        $direction = $validated['direction'];

        // Find the trace record
        $traceRecord = TraceRecord::where('tlc', $tlc)->first();

        if (!$traceRecord) {
            return back()->with('error', 'TLC not found: ' . $tlc);
        }

        TraceabilityAnalytics::create([
            'trace_record_id' => $traceRecord->id,
            'query_type' => 'admin_report',
            'direction' => $direction,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $results = [
            'query_tlc' => $tlc,
            'direction' => $direction,
            'record' => $traceRecord->load(['product', 'location']),
            'backward' => [],
            'forward' => [],
        ];

        if (in_array($direction, ['backward', 'both'])) {
            $backwardData = $this->traceabilityService->traceBackward($traceRecord);
            $results['backward'] = $backwardData['chain'];
        }

        if (in_array($direction, ['forward', 'both'])) {
            $forwardData = $this->traceabilityService->traceForward($traceRecord);
            $results['forward'] = $forwardData['chain'];
        }

        // Get all CTE events for this TLC
        $results['events'] = CTEEvent::where('trace_record_id', $traceRecord->id)
            ->with(['location', 'partner', 'creator'])
            ->orderBy('event_date')
            ->get();

        $results['gs1_data'] = $this->gs1Service->generateGS1Data($traceRecord);

        return view('reports.traceability-result', compact('results'));
    }

    public function exportTraceability(Request $request)
    {
        $validated = $request->validate([
            'tlc' => 'required|string',
            'direction' => 'required|in:backward,forward,both',
        ]);

        $tlc = $validated['tlc'];
        $traceRecord = TraceRecord::where('tlc', $tlc)->first();

        if (!$traceRecord) {
            return back()->with('error', 'TLC not found');
        }

        $records = collect([$traceRecord]);

        if (in_array($validated['direction'], ['backward', 'both'])) {
            $records = $records->merge($traceRecord->traceBackward()->get());
        }

        if (in_array($validated['direction'], ['forward', 'both'])) {
            $records = $records->merge($traceRecord->traceForward()->get());
        }

        // Generate CSV
        $filename = 'traceability_' . $tlc . '_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'TLC',
                'Product SKU',
                'Product Name',
                'Quantity',
                'Unit',
                'Location',
                'Status',
                'Harvest Date',
                'Pack Date',
                'Created At',
            ]);

            // CSV Data
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->tlc,
                    $record->product->sku,
                    $record->product->product_name,
                    $record->quantity,
                    $record->unit,
                    $record->location->location_name,
                    $record->status,
                    $record->harvest_date?->format('Y-m-d'),
                    $record->pack_date?->format('Y-m-d'),
                    $record->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportTraceabilityPdf(Request $request)
    {
        $validated = $request->validate([
            'tlc' => 'required|string',
            'direction' => 'required|in:backward,forward,both',
        ]);

        $tlc = $validated['tlc'];
        $traceRecord = TraceRecord::where('tlc', $tlc)->with(['product', 'location'])->first();

        if (!$traceRecord) {
            return back()->with('error', 'TLC not found');
        }

        $results = [
            'query_tlc' => $tlc,
            'direction' => $validated['direction'],
            'record' => $traceRecord,
            'backward' => [],
            'forward' => [],
        ];

        if (in_array($validated['direction'], ['backward', 'both'])) {
            $backwardData = $this->traceabilityService->traceBackward($traceRecord);
            $results['backward'] = $backwardData['chain'];
        }

        if (in_array($validated['direction'], ['forward', 'both'])) {
            $forwardData = $this->traceabilityService->traceForward($traceRecord);
            $results['forward'] = $forwardData['chain'];
        }

        $results['events'] = CTEEvent::where('trace_record_id', $traceRecord->id)
            ->with(['location', 'partner', 'creator'])
            ->orderBy('event_date')
            ->get();

        $results['gs1_data'] = $this->gs1Service->generateGS1Data($traceRecord);

        $pdf = Pdf::loadView('reports.traceability-pdf', compact('results'));
        
        return $pdf->download('traceability_' . $tlc . '_' . date('Y-m-d_His') . '.pdf');
    }

    public function analytics(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Query statistics
        $queryStats = TraceabilityAnalytics::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('COUNT(*) as total_queries'),
                DB::raw('COUNT(DISTINCT trace_record_id) as unique_records'),
                DB::raw('COUNT(DISTINCT ip_address) as unique_ips'),
                DB::raw('SUM(CASE WHEN query_type = "public" THEN 1 ELSE 0 END) as public_queries'),
                DB::raw('SUM(CASE WHEN query_type = "admin_report" THEN 1 ELSE 0 END) as admin_queries'),
                DB::raw('SUM(CASE WHEN query_type = "api" THEN 1 ELSE 0 END) as api_queries')
            )
            ->first();

        // Queries by day
        $queriesByDay = TraceabilityAnalytics::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most queried products
        $topProducts = TraceabilityAnalytics::whereBetween('traceability_analytics.created_at', [$dateFrom, $dateTo])
            ->join('trace_records', 'traceability_analytics.trace_record_id', '=', 'trace_records.id')
            ->join('products', 'trace_records.product_id', '=', 'products.id')
            ->select(
                'products.product_name',
                DB::raw('COUNT(*) as query_count')
            )
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('query_count')
            ->limit(10)
            ->get();

        // Query types distribution
        $queryTypes = TraceabilityAnalytics::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('query_type', DB::raw('COUNT(*) as count'))
            ->groupBy('query_type')
            ->get();

        // Direction distribution
        $directions = TraceabilityAnalytics::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('direction', DB::raw('COUNT(*) as count'))
            ->groupBy('direction')
            ->get();

        return view('reports.traceability-analytics', compact(
            'queryStats',
            'queriesByDay',
            'topProducts',
            'queryTypes',
            'directions',
            'dateFrom',
            'dateTo'
        ));
    }

    public function compliance()
    {
        // Compliance statistics
        $stats = [
            'total_products' => Product::ftl()->count(),
            'total_trace_records' => TraceRecord::count(),
            'active_records' => TraceRecord::active()->count(),
            'total_cte_events' => CTEEvent::count(),
            'receiving_events' => CTEEvent::receiving()->count(),
            'transformation_events' => CTEEvent::transformation()->count(),
            'shipping_events' => CTEEvent::shipping()->count(),
            'audit_logs_count' => AuditLog::count(),
            'recent_audit_logs' => AuditLog::recent(30)->count(),
        ];

        // Events by month (last 12 months)
        $eventsByMonth = CTEEvent::select(
                DB::raw('DATE_FORMAT(event_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('event_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Products without recent activity
        $inactiveProducts = Product::ftl()
            ->whereDoesntHave('traceRecords', function($query) {
                $query->where('created_at', '>=', now()->subDays(90));
            })
            ->get();

        // Compliance score calculation
        $complianceScore = $this->calculateComplianceScore($stats);

        return view('reports.compliance', compact('stats', 'eventsByMonth', 'inactiveProducts', 'complianceScore'));
    }

    private function calculateComplianceScore($stats)
    {
        $score = 100;
        
        // Deduct points for missing data
        if ($stats['total_products'] == 0) $score -= 30;
        if ($stats['total_cte_events'] == 0) $score -= 30;
        if ($stats['audit_logs_count'] == 0) $score -= 20;
        
        // Bonus for good practices
        if ($stats['recent_audit_logs'] > 100) $score += 5;
        if ($stats['total_cte_events'] > 1000) $score += 5;
        
        return max(0, min(100, $score));
    }
}
