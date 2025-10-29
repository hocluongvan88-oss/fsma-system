<?php

namespace App\Constants;

class PackageLimits
{
    /**
     * Constant for unlimited resources
     * Using -1 instead of 0 or 999999 for clarity
     */
    const UNLIMITED = -1;

    /**
     * Check if a value represents unlimited
     */
    public static function isUnlimited(?int $value): bool
    {
        // Consider null, -1, 0, or very large numbers as unlimited
        // This maintains backward compatibility with existing data
        return $value === null 
            || $value === self::UNLIMITED 
            || $value === 0 
            || $value >= 999999;
    }

    /**
     * Get display value for limits
     */
    public static function getDisplayValue(?int $value): string
    {
        return self::isUnlimited($value) ? 'Unlimited' : (string) $value;
    }

    /**
     * Normalize limit value for storage
     * Converts various "unlimited" representations to standard -1
     */
    public static function normalize(?int $value): ?int
    {
        if (self::isUnlimited($value)) {
            return self::UNLIMITED;
        }
        return $value;
    }
}
