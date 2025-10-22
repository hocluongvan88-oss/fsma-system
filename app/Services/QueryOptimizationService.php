<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryOptimizationService
{
    /**
     * Cache duration for frequently accessed data (in seconds)
     */
    const CACHE_DURATION = 300; // 5 minutes

    /**
     * Get active products with caching
     * Added 'sku' column to match master-data structure and prevent stdClass errors
     */
    public static function getActiveProducts($organizationId, bool $ftlOnly = true)
    {
        $cacheKey = "products_org_{$organizationId}_ftl_" . ($ftlOnly ? '1' : '0');
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($organizationId, $ftlOnly) {
            $query = DB::table('products')
                ->select('id', 'sku', 'product_name', 'is_ftl', 'category', 'unit_of_measure', 'organization_id')
                ->where(function($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId)
                      ->orWhereNull('organization_id');
                });
            
            if ($ftlOnly) {
                $query->where('is_ftl', true);
            }
            
            return $query->orderBy('product_name')->get();
        });
    }

    /**
     * Get active locations with caching
     * Added 'location_type' column to match master-data structure
     */
    public static function getActiveLocations($organizationId)
    {
        $cacheKey = "locations_org_{$organizationId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($organizationId) {
            return DB::table('locations')
                ->select('id', 'location_name', 'gln', 'location_type', 'organization_id')
                ->where(function($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId)
                      ->orWhereNull('organization_id');
                })
                ->orderBy('location_name')
                ->get();
        });
    }

    /**
     * Get partners by type with caching
     * Added 'partner_type' to select and ensured consistency with master-data
     */
    public static function getPartnersByType($organizationId, string $partnerType)
    {
        $cacheKey = "partners_org_{$organizationId}_type_{$partnerType}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($organizationId, $partnerType) {
            $query = DB::table('partners')
                ->select('id', 'partner_name', 'partner_type', 'gln', 'organization_id')
                ->where(function($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId)
                      ->orWhereNull('organization_id');
                });
            
            if (in_array($partnerType, ['supplier', 'customer'])) {
                $query->where(function($q) use ($partnerType) {
                    $q->where('partner_type', $partnerType)
                      ->orWhere('partner_type', 'both');
                });
            } else {
                $query->where('partner_type', $partnerType);
            }
            
            return $query->orderBy('partner_name')->get();
        });
    }

    /**
     * Get active trace records with caching
     * Optimized: Only select needed columns, eager load product
     */
    public static function getActiveTraceRecords($organizationId)
    {
        $cacheKey = "trace_records_active_org_{$organizationId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($organizationId) {
            return DB::table('trace_records as tr')
                ->join('products as p', 'tr.product_id', '=', 'p.id')
                ->select(
                    'tr.id',
                    'tr.tlc',
                    'tr.quantity',
                    'tr.available_quantity',
                    'tr.consumed_quantity',
                    'tr.unit',
                    'tr.status',
                    'p.id as product_id',
                    'p.product_name'
                )
                ->where('tr.status', 'active')
                ->where('tr.organization_id', $organizationId)
                ->where('tr.available_quantity', '>', 0)
                ->orderBy('tr.created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get recent CTE events with pagination
     * Optimized: Only select needed columns, use pagination
     */
    public static function getRecentCTEEvents(string $eventType, $organizationId, int $limit = 10)
    {
        return DB::table('cte_events as ce')
            ->join('trace_records as tr', 'ce.trace_record_id', '=', 'tr.id')
            ->join('products as p', 'tr.product_id', '=', 'p.id')
            ->join('locations as l', 'ce.location_id', '=', 'l.id')
            ->leftJoin('partners as pa', 'ce.partner_id', '=', 'pa.id')
            ->select(
                'ce.id',
                'ce.event_type',
                'ce.event_date',
                'ce.status',
                'ce.created_at',
                'tr.tlc',
                'p.product_name',
                'l.location_name',
                'pa.partner_name'
            )
            ->where('ce.event_type', $eventType)
            ->where('tr.organization_id', $organizationId)
            ->orderBy('ce.event_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clear cache for organization
     * Call this after creating/updating/deleting records
     */
    public static function clearOrganizationCache($organizationId)
    {
        $patterns = [
            "products_org_{$organizationId}_*",
            "locations_org_{$organizationId}",
            "partners_org_{$organizationId}_*",
            "trace_records_active_org_{$organizationId}",
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Get database performance metrics
     */
    public static function getPerformanceMetrics(): array
    {
        $metrics = [];
        
        // Check slow queries
        $slowQueries = DB::select("
            SELECT * FROM information_schema.processlist 
            WHERE command != 'Sleep' 
            AND time > 1 
            ORDER BY time DESC 
            LIMIT 10
        ");
        
        $metrics['slow_queries'] = count($slowQueries);
        
        // Check table sizes
        $tableSizes = DB::select("
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name IN ('audit_logs', 'cte_events', 'trace_records')
            ORDER BY (data_length + index_length) DESC
        ");
        
        $metrics['table_sizes'] = $tableSizes;
        
        // Check index usage
        $indexStats = DB::select("
            SELECT 
                table_name,
                index_name,
                cardinality
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name IN ('audit_logs', 'cte_events', 'trace_records')
            ORDER BY table_name, index_name
        ");
        
        $metrics['index_stats'] = $indexStats;
        
        return $metrics;
    }
}
