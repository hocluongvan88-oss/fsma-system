<?php

namespace App\Http\Controllers;

use App\Models\ArchivalLog;
use App\Services\ArchivalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchivalController extends Controller
{
    protected ArchivalService $archivalService;

    public function __construct(ArchivalService $archivalService)
    {
        $this->archivalService = $archivalService;
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show archival management dashboard
     */
    public function index()
    {
        $stats = $this->archivalService->getStatistics();
        $recentLogs = ArchivalLog::latest('executed_at')->limit(10)->get();
        
        // Get data type statistics
        $dataTypeStats = $this->getDataTypeStats();

        return view('admin.archival.index', [
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'dataTypeStats' => $dataTypeStats,
            'config' => [
                'strategy' => config('archival.strategy'),
                'hot_data_months' => config('archival.hot_data_months'),
                'batch_size' => config('archival.batch_size'),
            ],
        ]);
    }

    /**
     * Execute archival process
     */
    public function execute(Request $request)
    {
        $dryRun = $request->boolean('dry_run', false);
        
        try {
            $results = $this->archivalService->executeArchival($dryRun);
            
            $totalArchived = array_sum(array_column(array_filter($results, fn($r) => $r['status'] === 'success'), 'records_archived'));
            
            $message = $dryRun 
                ? __('messages.archival_dry_run_completed', ['count' => $totalArchived])
                : __('messages.archival_completed', ['count' => $totalArchived]);

            return redirect()->route('admin.archival.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.archival.index')
                ->with('error', __('messages.archival_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * View archival logs with pagination
     */
    public function logs()
    {
        $logs = ArchivalLog::with('executedByUser')
            ->latest('executed_at')
            ->paginate(20);

        return view('admin.archival.logs', [
            'logs' => $logs,
        ]);
    }

    /**
     * View archived data for a specific data type
     */
    public function viewArchived(Request $request, string $dataType)
    {
        $strategy = config('archival.strategy');
        
        if ($strategy !== 'database') {
            return redirect()->route('admin.archival.index')
                ->with('error', __('messages.archival_view_not_available'));
        }

        $archivalTable = config("archival.archival_data_types.{$dataType}.archival_table");
        
        if (!$archivalTable) {
            return redirect()->route('admin.archival.index')
                ->with('error', __('messages.invalid_data_type'));
        }

        $archivedData = DB::table($archivalTable)
            ->orderBy('archived_at', 'desc')
            ->paginate(50);

        return view('admin.archival.view', [
            'dataType' => $dataType,
            'archivedData' => $archivedData,
        ]);
    }

    /**
     * Get statistics for each data type
     */
    protected function getDataTypeStats(): array
    {
        $stats = [];
        $dataTypes = config('archival.archival_data_types', []);
        $hotMonths = config('archival.hot_data_months', 36);
        $cutoffDate = now()->subMonths($hotMonths);

        foreach ($dataTypes as $dataType => $config) {
            if (!$config['enabled']) {
                continue;
            }

            $model = $config['model'];
            $hotRecords = $model::where('created_at', '>=', $cutoffDate)->count();
            $coldRecords = $model::where('created_at', '<', $cutoffDate)->count();
            
            $lastArchival = ArchivalLog::where('data_type', $dataType)
                ->where('status', 'success')
                ->latest('executed_at')
                ->first();

            $stats[$dataType] = [
                'name' => ucfirst(str_replace('_', ' ', $dataType)),
                'hot_records' => $hotRecords,
                'cold_records' => $coldRecords,
                'last_archival' => $lastArchival?->executed_at,
                'total_archived' => ArchivalLog::where('data_type', $dataType)
                    ->sum('records_archived'),
            ];
        }

        return $stats;
    }
}
