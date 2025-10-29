<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class SKUGenerationService
{
    /**
     * Generate unique SKU (Stock Keeping Unit)
     * Format: SKU-YYYYMMDD-XXXXX (e.g., SKU-20250101-A1B2C)
     * Ensures uniqueness across organization
     */
    public static function generateUniqueSKU(int $organizationId): string
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $sku = self::generateSKU();

            // Check if SKU already exists in this organization
            $exists = Product::where('sku', $sku)
                ->where('organization_id', $organizationId)
                ->exists();

            if (!$exists) {
                return $sku;
            }

            $attempt++;
        }

        throw new \Exception("Failed to generate unique SKU after {$maxAttempts} attempts");
    }

    /**
     * Generate SKU format: SKU-YYYYMMDD-XXXXX
     */
    private static function generateSKU(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5)); // 5 random alphanumeric characters
        
        return "SKU-{$date}-{$random}";
    }

    /**
     * Validate SKU format
     */
    public static function validateSKUFormat(string $sku): bool
    {
        return preg_match('/^SKU-\d{8}-[A-Z0-9]{5}$/', $sku) === 1;
    }
}
