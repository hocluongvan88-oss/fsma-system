<?php

namespace App\Services;

use App\Models\TraceRecord;
use Illuminate\Support\Str;

class TLCGenerationService
{
    /**
     * Generate unique TLC (Traceability Lot Code)
     * Format: ORG-YYYYMMDD-XXXXX (e.g., ORG-20250101-A1B2C)
     * Ensures uniqueness across organization
     */
    public static function generateUniqueTLC(int $organizationId): string
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $tlc = self::generateTLC();

            // Check if TLC already exists in this organization
            $exists = TraceRecord::where('tlc', $tlc)
                ->where('organization_id', $organizationId)
                ->exists();

            if (!$exists) {
                return $tlc;
            }

            $attempt++;
        }

        throw new \Exception("Failed to generate unique TLC after {$maxAttempts} attempts");
    }

    /**
     * Generate TLC format: ORG-YYYYMMDD-XXXXX
     */
    private static function generateTLC(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5)); // 5 random alphanumeric characters
        
        return "ORG-{$date}-{$random}";
    }

    /**
     * Validate TLC format
     */
    public static function validateTLCFormat(string $tlc): bool
    {
        return preg_match('/^ORG-\d{8}-[A-Z0-9]{5}$/', $tlc) === 1;
    }
}
