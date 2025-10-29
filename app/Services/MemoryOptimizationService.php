<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MemoryOptimizationService
{
    /**
     * Get current memory usage
     */
    public static function getMemoryUsage(): array
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
            'percentage' => (memory_get_usage(true) / self::getMemoryLimitInBytes()) * 100,
        ];
    }

    /**
     * Convert memory limit to bytes
     */
    private static function getMemoryLimitInBytes(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $value = (int) $limit;
        $unit = strtoupper(substr($limit, -1));

        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Log memory usage if threshold exceeded
     */
    public static function checkMemoryThreshold(float $threshold = 80): void
    {
        $usage = self::getMemoryUsage();
        
        if ($usage['percentage'] > $threshold) {
            Log::warning('Memory usage high', [
                'current' => $usage['current'],
                'peak' => $usage['peak'],
                'limit' => $usage['limit'],
                'percentage' => $usage['percentage'],
            ]);
        }
    }

    /**
     * Clear unused memory
     */
    public static function clearMemory(): void
    {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}
