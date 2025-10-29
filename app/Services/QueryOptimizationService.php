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
                ->where('organization_id', $organizationId);
            
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
                ->where('organization_id', $organizationId)
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
                ->where('organization_id', $organizationId);
            
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
     * Modified to return objects with product relationship accessible
     * Now returns records with product data as nested object for view compatibility
     */
    public static function getActiveTraceRecords($organizationId)
    {
        $cacheKey = "trace_records_active_org_{$organizationId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($organizationId) {
            $records = DB::table('trace_records as tr')
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
            
            return $records->map(function($record) {
                $record->product = (object)[
                    'id' => $record->product_id,
                    'product_name' => $record->product_name
                ];
                return $record;
            });
        });
    }

    /**
     * Get recent CTE events with pagination
     * Optimized: Only select needed columns, use pagination
     * CRITICAL FIX: Added organization_id filter on leftJoin partners to prevent NULL-based data leakage
     */
    public static function getRecentCTEEvents(string $eventType, $organizationId, int $limit = 10)
    {
        return DB::table('cte_events as ce')
            ->join('trace_records as tr', 'ce.trace_record_id', '=', 'tr.id')
            ->join('products as p', 'tr.product_id', '=', 'p.id')
            ->join('locations as l', 'ce.location_id', '=', 'l.id')
            ->leftJoin('partners as pa', function($join) use ($organizationId) {
                $join->on('ce.partner_id', '=', 'pa.id')
                     ->where('pa.organization_id', '=', $organizationId);
            })
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
            ->where('ce.organization_id', $organizationId)
            ->where('p.organization_id', $organizationId)
            ->where('l.organization_id', $organizationId)
            ->orderBy('ce.event_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clear cache for organization
     * Call this after creating/updating/deleting records
     * Fixed cache invalidation to use exact keys instead of wildcard patterns
     */
    public static function clearOrganizationCache($organizationId)
    {
        // Clear all product cache variations for this organization
        \Illuminate\Support\Facades\Cache::forget("products_org_{$organizationId}_ftl_0");
        \Illuminate\Support\Facades\Cache::forget("products_org_{$organizationId}_ftl_1");
        
        // Clear location cache
        \Illuminate\Support\Facades\Cache::forget("locations_org_{$organizationId}");
        
        // Clear all partner type variations
        \Illuminate\Support\Facades\Cache::forget("partners_org_{$organizationId}_type_supplier");
        \Illuminate\Support\Facades\Cache::forget("partners_org_{$organizationId}_type_customer");
        \Illuminate\Support\Facades\Cache::forget("partners_org_{$organizationId}_type_both");
        \Illuminate\Support\Facades\Cache::forget("partners_org_{$organizationId}_type_processing");
        \Illuminate\Support\Facades\Cache::forget("partners_org_{$organizationId}_type_distribution");
        \Illuminate\Support\Facades\Cache::forget("partners_org_{$organizationId}_type_farm");
        
        // Clear trace records cache
        \Illuminate\Support\Facades\Cache::forget("trace_records_active_org_{$organizationId}");
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
