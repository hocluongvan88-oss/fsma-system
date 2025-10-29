<?php

namespace App\Http\Controllers;

use App\Models\RetentionPolicy;
use App\Models\RetentionLog;
use App\Models\AuditLog;
use App\Models\CTEEvent;
use App\Models\TraceRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ComplianceReportController extends Controller
{
    /**
     * Show FSMA 204 compliance dashboard
     * FSMA 204 Compliance: Comprehensive retention and archival status
     */
    public function dashboard()
    {
        $this->authorize('viewAny', RetentionPolicy::class);

        $organizationId = auth()->user()->organization_id;

        $metrics = Cache::remember("compliance_metrics_org_{$organizationId}", 3600, function () use ($organizationId) {
            return [
                'total_policies' => RetentionPolicy::where('organization_id', $organizationId)->count(),
                'active_policies' => RetentionPolicy::where('organization_id', $organizationId)->where('is_active', true)->count(),
                'protected_records' => $this->countProtectedRecords($organizationId),
                'archived_records' => $this->countArchivedRecords($organizationId),
                'retention_logs_30d' => RetentionLog::where('organization_id', $organizationId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
                'audit_logs_30d' => AuditLog::where('organization_id', $organizationId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
            ];
        });

        $policyStatus = RetentionPolicy::where('organization_id', $organizationId)
            ->select('data_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(CASE WHEN is_active THEN 1 ELSE 0 END) as active'))
            ->groupBy('data_type')
            ->get();

        $executionTimeline = RetentionLog::where('organization_id', $organizationId)
            ->select(
                DB::raw('DATE(executed_at) as date'),
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(records_deleted) as total_deleted')
            )
            ->where('executed_at', '>=', now()->subDays(90))
            ->groupBy('date', 'status')
            ->orderBy('date', 'desc')
            ->get();

        $complianceScore = $this->calculateComplianceScore($organizationId, $metrics);

        return view('compliance.dashboard', compact('metrics', 'policyStatus', 'executionTimeline', 'complianceScore'));
    }

    /**
     * Generate FSMA 204 compliance audit report
     * FSMA 204 Compliance: Complete audit trail and immutability verification
     */
    public function generateAuditReport()
    {
        $this->authorize('viewLogs', RetentionPolicy::class);

        $organizationId = auth()->user()->organization_id;

        $reportData = [
            'generated_at' => now(),
            'organization_id' => $organizationId,
            'retention_policies' => RetentionPolicy::where('organization_id', $organizationId)->get(),
            'retention_logs' => RetentionLog::where('organization_id', $organizationId)
                ->orderBy('executed_at', 'desc')
                ->limit(100)
                ->get(),
            'audit_logs' => AuditLog::where('organization_id', $organizationId)
                ->where('auditable_type', 'App\\Models\\RetentionPolicy')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get(),
            'protected_data_count' => $this->countProtectedRecords($organizationId),
            'compliance_score' => $this->calculateComplianceScore($organizationId),
        ];

        $pdf = Pdf::loadView('compliance.audit-report', $reportData);
        
        return $pdf->download('FSMA_204_Compliance_Audit_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Get retention policy recommendations
     * FSMA 204 Compliance: Automated policy optimization suggestions
     */
    public function getRecommendations()
    {
        $this->authorize('viewAny', RetentionPolicy::class);

        $organizationId = auth()->user()->organization_id;

        $recommendations = Cache::remember("retention_recommendations_org_{$organizationId}", 86400, function () use ($organizationId) {
            $recommendations = [];

            // Check for missing policies
            $protectedTypes = ['trace_records', 'cte_events', 'audit_logs', 'e_signatures', 'documents', 'document_versions', 'trace_relationships'];
            $existingPolicies = RetentionPolicy::where('organization_id', $organizationId)
                ->pluck('data_type')
                ->toArray();

            foreach ($protectedTypes as $type) {
                if (!in_array($type, $existingPolicies)) {
                    $recommendations[] = [
                        'type' => 'missing_policy',
                        'severity' => 'high',
                        'message' => "Missing retention policy for {$type}. FSMA 204 requires protection of all critical data types.",
                        'action' => "Create retention policy for {$type}",
                    ];
                }
            }

            // Check for inactive policies
            $inactivePolicies = RetentionPolicy::where('organization_id', $organizationId)
                ->where('is_active', false)
                ->count();

            if ($inactivePolicies > 0) {
                $recommendations[] = [
                    'type' => 'inactive_policies',
                    'severity' => 'medium',
                    'message' => "{$inactivePolicies} retention policies are inactive. Ensure all critical data types have active protection.",
                    'action' => 'Review and activate inactive policies',
                ];
            }

            // Check for archival opportunities
            $oldRecords = $this->countOldRecords($organizationId, 36);
            if ($oldRecords > 1000) {
                $recommendations[] = [
                    'type' => 'archival_opportunity',
                    'severity' => 'low',
                    'message' => "Found {$oldRecords} records older than 36 months. Consider archiving to cold storage for optimization.",
                    'action' => 'Execute archival for records older than 36 months',
                ];
            }

            // Check for backup verification
            $recentLogs = RetentionLog::where('organization_id', $organizationId)
                ->where('created_at', '>=', now()->subDays(7))
                ->where('backup_verified', false)
                ->count();

            if ($recentLogs > 0) {
                $recommendations[] = [
                    'type' => 'backup_verification',
                    'severity' => 'high',
                    'message' => "{$recentLogs} recent retention operations have unverified backups. Verify backup integrity immediately.",
                    'action' => 'Verify backup files for recent retention operations',
                ];
            }

            return $recommendations;
        });

        return response()->json(['recommendations' => $recommendations]);
    }

    /**
     * Export compliance data for audit
     * FSMA 204 Compliance: Exportable audit trail for regulatory review
     */
    public function exportComplianceData()
    {
        $this->authorize('viewLogs', RetentionPolicy::class);

        $organizationId = auth()->user()->organization_id;

        $data = [
            'policies' => RetentionPolicy::where('organization_id', $organizationId)->get(),
            'logs' => RetentionLog::where('organization_id', $organizationId)->get(),
            'audit_logs' => AuditLog::where('organization_id', $organizationId)
                ->where('auditable_type', 'App\\Models\\RetentionPolicy')
                ->get(),
        ];

        $filename = 'FSMA_204_Compliance_Export_' . now()->format('Y-m-d_His') . '.json';
        
        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Calculate compliance score based on metrics
     */
    private function calculateComplianceScore($organizationId, $metrics = null): int
    {
        if (!$metrics) {
            $metrics = [
                'total_policies' => RetentionPolicy::where('organization_id', $organizationId)->count(),
                'active_policies' => RetentionPolicy::where('organization_id', $organizationId)->where('is_active', true)->count(),
                'protected_records' => $this->countProtectedRecords($organizationId),
                'audit_logs_30d' => AuditLog::where('organization_id', $organizationId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
            ];
        }

        $score = 100;

        // Deduct for missing policies
        if ($metrics['total_policies'] < 7) {
            $score -= (7 - $metrics['total_policies']) * 10;
        }

        // Deduct for inactive policies
        if ($metrics['active_policies'] < $metrics['total_policies']) {
            $score -= ($metrics['total_policies'] - $metrics['active_policies']) * 5;
        }

        // Deduct for missing audit logs
        if ($metrics['audit_logs_30d'] < 10) {
            $score -= 10;
        }

        // Bonus for good practices
        if ($metrics['protected_records'] > 10000) {
            $score += 5;
        }

        return max(0, min(100, $score));
    }

    /**
     * Count protected records across all protected data types
     */
    private function countProtectedRecords($organizationId): int
    {
        return TraceRecord::where('organization_id', $organizationId)->count() +
               CTEEvent::where('organization_id', $organizationId)->count() +
               AuditLog::where('organization_id', $organizationId)->count();
    }

    /**
     * Count archived records
     */
    private function countArchivedRecords($organizationId): int
    {
        return RetentionLog::where('organization_id', $organizationId)
            ->where('status', 'archived')
            ->sum('records_deleted');
    }

    /**
     * Count records older than specified months
     */
    private function countOldRecords($organizationId, int $months): int
    {
        $cutoffDate = now()->subMonths($months);

        return TraceRecord::where('organization_id', $organizationId)
            ->where('created_at', '<', $cutoffDate)
            ->count() +
            CTEEvent::where('organization_id', $organizationId)
                ->where('created_at', '<', $cutoffDate)
                ->count();
    }
}
