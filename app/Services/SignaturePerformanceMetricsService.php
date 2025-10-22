<?php

namespace App\Services;

use App\Models\ESignature;
use Illuminate\Support\Facades\DB;

class SignaturePerformanceMetricsService
{
    /**
     * Record performance metrics for signature creation
     */
    public function recordSignatureCreationMetrics(
        ESignature $signature,
        array $timings,
        array $resourceMetrics = []
    ): void {
        $totalTime = array_sum($timings);
        
        // Find bottleneck component
        $bottleneck = array_key_first($timings);
        $bottleneckTime = max($timings);
        $bottleneckPercentage = ($bottleneckTime / $totalTime) * 100;
        
        DB::table('signature_performance_metrics')->insert([
            'e_signature_id' => $signature->id,
            'signature_creation_time_ms' => $timings['creation'] ?? null,
            'timestamp_request_time_ms' => $timings['timestamp'] ?? null,
            'certificate_verification_time_ms' => $timings['certificate'] ?? null,
            'hash_computation_time_ms' => $timings['hash'] ?? null,
            'encryption_time_ms' => $timings['encryption'] ?? null,
            'total_signature_time_ms' => $totalTime,
            'tsa_provider' => $signature->timestamp_provider,
            'memory_used_mb' => $resourceMetrics['memory'] ?? null,
            'cpu_time_ms' => $resourceMetrics['cpu'] ?? null,
            'bottleneck_component' => $bottleneck,
            'bottleneck_time_ms' => $bottleneckTime,
            'bottleneck_percentage' => $bottleneckPercentage,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Record verification metrics
     */
    public function recordVerificationMetrics(
        ESignature $signature,
        int $verificationTimeMs,
        int $revocationCheckTimeMs = 0,
        int $ltvValidationTimeMs = 0
    ): void {
        DB::table('signature_performance_metrics')->updateOrInsert(
            ['e_signature_id' => $signature->id],
            [
                'verification_time_ms' => $verificationTimeMs,
                'revocation_check_time_ms' => $revocationCheckTimeMs,
                'ltv_validation_time_ms' => $ltvValidationTimeMs,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Get metrics summary for the last N days
     */
    public function getMetricsSummary(int $days = 30): array
    {
        $metrics = DB::table('signature_performance_metrics')
            ->whereBetween('created_at', [now()->subDays($days), now()])
            ->get();
        
        if ($metrics->isEmpty()) {
            return [
                'total_signatures' => 0,
                'average_creation_time_ms' => 0,
                'average_verification_time_ms' => 0,
                'success_rate' => 0,
                'total_errors' => 0,
                'period_days' => $days,
            ];
        }
        
        return [
            'total_signatures' => $metrics->count(),
            'average_creation_time_ms' => (int)$metrics->avg('total_signature_time_ms'),
            'average_verification_time_ms' => (int)$metrics->avg('verification_time_ms'),
            'min_creation_time_ms' => (int)$metrics->min('total_signature_time_ms'),
            'max_creation_time_ms' => (int)$metrics->max('total_signature_time_ms'),
            'total_errors' => (int)$metrics->sum('error_count'),
            'success_rate' => $this->calculateSuccessRate($metrics),
            'period_days' => $days,
        ];
    }

    /**
     * Identify bottlenecks in signature operations
     */
    public function identifyBottlenecks(): array
    {
        $metrics = DB::table('signature_performance_metrics')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->get();
        
        return $this->analyzeBottlenecks($metrics);
    }

    /**
     * Get TSA performance metrics
     */
    public function getTSAPerformance(): array
    {
        $metrics = DB::table('signature_performance_metrics')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->get();
        
        return $this->analyzeTSAPerformance($metrics);
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStatistics(
        ?string $period = 'day',
        ?string $tsaProvider = null
    ): array {
        $query = DB::table('signature_performance_metrics');
        
        // Filter by period
        if ($period === 'day') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->subWeek(), now()]);
        } elseif ($period === 'month') {
            $query->whereBetween('created_at', [now()->subMonth(), now()]);
        }
        
        // Filter by TSA provider
        if ($tsaProvider) {
            $query->where('tsa_provider', $tsaProvider);
        }
        
        $metrics = $query->get();
        
        return [
            'total_signatures' => $metrics->count(),
            'average_creation_time_ms' => (int)$metrics->avg('total_signature_time_ms'),
            'average_verification_time_ms' => (int)$metrics->avg('verification_time_ms'),
            'average_tsa_response_time_ms' => (int)$metrics->avg('tsa_response_time_ms'),
            'min_creation_time_ms' => (int)$metrics->min('total_signature_time_ms'),
            'max_creation_time_ms' => (int)$metrics->max('total_signature_time_ms'),
            'total_errors' => (int)$metrics->sum('error_count'),
            'success_rate' => $this->calculateSuccessRate($metrics),
            'bottleneck_analysis' => $this->analyzeBottlenecks($metrics),
            'tsa_performance' => $this->analyzeTSAPerformance($metrics),
            'throughput' => $this->calculateThroughput($metrics),
        ];
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate($metrics): float
    {
        if ($metrics->isEmpty()) {
            return 0;
        }
        
        $successful = $metrics->filter(fn($m) => $m->error_count === 0)->count();
        return round(($successful / $metrics->count()) * 100, 2);
    }

    /**
     * Analyze bottlenecks
     */
    private function analyzeBottlenecks($metrics): array
    {
        if ($metrics->isEmpty()) {
            return [];
        }
        
        $bottlenecks = $metrics->groupBy('bottleneck_component')
            ->map(fn($group) => [
                'component' => $group->first()->bottleneck_component,
                'count' => $group->count(),
                'average_time_ms' => (int)$group->avg('bottleneck_time_ms'),
                'average_percentage' => round($group->avg('bottleneck_percentage'), 2),
            ])
            ->sortByDesc('count')
            ->values()
            ->toArray();
        
        return $bottlenecks;
    }

    /**
     * Analyze TSA performance
     */
    private function analyzeTSAPerformance($metrics): array
    {
        if ($metrics->isEmpty()) {
            return [];
        }
        
        return $metrics->groupBy('tsa_provider')
            ->map(fn($group) => [
                'provider' => $group->first()->tsa_provider,
                'total_requests' => $group->count(),
                'average_response_time_ms' => (int)$group->avg('tsa_response_time_ms'),
                'retry_count' => (int)$group->sum('tsa_retry_count'),
                'success_count' => $group->where('tsa_status', 'success')->count(),
                'failed_count' => $group->where('tsa_status', 'failed')->count(),
                'timeout_count' => $group->where('tsa_status', 'timeout')->count(),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Calculate throughput
     */
    private function calculateThroughput($metrics): array
    {
        if ($metrics->isEmpty()) {
            return [];
        }
        
        $timeRange = $metrics->max('created_at')->diffInMinutes($metrics->min('created_at')) ?: 1;
        
        return [
            'signatures_per_minute' => round($metrics->count() / $timeRange, 2),
            'verifications_per_minute' => round($metrics->whereNotNull('verification_time_ms')->count() / $timeRange, 2),
            'total_time_range_minutes' => $timeRange,
        ];
    }

    /**
     * Get performance alerts
     */
    public function getPerformanceAlerts(): array
    {
        $alerts = [];
        $recentMetrics = DB::table('signature_performance_metrics')
            ->whereDate('created_at', today())
            ->get();
        
        if ($recentMetrics->isEmpty()) {
            return $alerts;
        }
        
        $avgCreationTime = (int)$recentMetrics->avg('total_signature_time_ms');
        $avgVerificationTime = (int)$recentMetrics->avg('verification_time_ms');
        
        // Alert if creation time exceeds 5 seconds
        if ($avgCreationTime > 5000) {
            $alerts[] = [
                'level' => 'warning',
                'message' => "Signature creation time is high: {$avgCreationTime}ms (threshold: 5000ms)",
                'metric' => 'creation_time',
            ];
        }
        
        // Alert if verification time exceeds 2 seconds
        if ($avgVerificationTime > 2000) {
            $alerts[] = [
                'level' => 'warning',
                'message' => "Signature verification time is high: {$avgVerificationTime}ms (threshold: 2000ms)",
                'metric' => 'verification_time',
            ];
        }
        
        // Alert if error rate exceeds 5%
        $errorRate = (($recentMetrics->sum('error_count') / $recentMetrics->count()) * 100);
        if ($errorRate > 5) {
            $alerts[] = [
                'level' => 'error',
                'message' => "Error rate is high: {$errorRate}% (threshold: 5%)",
                'metric' => 'error_rate',
            ];
        }
        
        return $alerts;
    }
}
