<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    // Cache TTL constants (in seconds)
    const TTL_STATIC_DATA = 600; // 10 minutes for products, locations, organizations
    const TTL_DYNAMIC_DATA = 300; // 5 minutes for frequently changing data
    const TTL_USER_SESSION = 3600; // 1 hour for user-specific data

    /**
     * Get cached data or execute callback and cache result
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning("Cache error for key {$key}: " . $e->getMessage());
            // Fallback to direct execution if cache fails
            return $callback();
        }
    }

    /**
     * Invalidate cache by key or pattern
     */
    public static function forget(string $key): bool
    {
        try {
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::warning("Cache forget error for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate cache by tag (requires Redis or Memcached)
     */
    public static function forgetByTag(string $tag): bool
    {
        try {
            Cache::tags($tag)->flush();
            return true;
        } catch (\Exception $e) {
            Log::warning("Cache tag flush error for tag {$tag}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate cache key for organization-scoped data
     */
    public static function orgKey(string $prefix, ?int $orgId = null): string
    {
        $orgId = $orgId ?? auth()->user()?->organization_id ?? 'global';
        return "{$prefix}:org:{$orgId}";
    }

    /**
     * Generate cache key for paginated data
     */
    public static function paginationKey(string $prefix, int $page, int $perPage, ?int $orgId = null): string
    {
        $orgId = $orgId ?? auth()->user()?->organization_id ?? 'global';
        return "{$prefix}:org:{$orgId}:page:{$page}:per:{$perPage}";
    }
}
