<?php

namespace App\Services;

use App\Models\TraceRecord;
use App\Models\Product;

class GS1Service
{
    public function generateGS1Data(TraceRecord $record): array
    {
        return [
            'digital_link' => $this->generateDigitalLink($record),
            'gs1_128' => $this->generateGS1_128($record),
            'qr_code_url' => $this->generateQRCode($record),
            'gtin' => $this->padGTIN($record->product->sku),
            'lot' => $record->lot_code,
            'serial' => $record->tlc,
        ];
    }

    /**
     * Generate GS1 Digital Link URI for a trace record
     * Format: https://id.gs1.org/01/{GTIN}/10/{LOT}/21/{SERIAL}
     */
    public function generateDigitalLink(TraceRecord $record): string
    {
        $product = $record->product;
        
        // Base URL for GS1 Digital Link
        $baseUrl = config('app.url');
        
        // Build GS1 Digital Link with AI (Application Identifiers)
        $gtin = $this->padGTIN($product->sku); // AI 01
        $lot = $record->lot_code; // AI 10
        $serial = $record->tlc; // AI 21
        
        // GS1 Digital Link format
        $digitalLink = "{$baseUrl}/01/{$gtin}/10/{$lot}/21/{$serial}";
        
        return $digitalLink;
    }

    /**
     * Generate GS1-128 barcode data
     * Format: (01)GTIN(10)LOT(21)SERIAL(13)PACKDATE
     */
    public function generateGS1_128(TraceRecord $record): string
    {
        $product = $record->product;
        
        $gtin = $this->padGTIN($product->sku); // AI 01 - GTIN
        $lot = $record->lot_code; // AI 10 - Batch/Lot
        $serial = $record->tlc; // AI 21 - Serial Number
        $packDate = $record->harvest_date ? 
            $record->harvest_date->format('ymd') : ''; // AI 13 - Pack Date
        
        // Build GS1-128 string with FNC1 character (represented as |)
        $gs1_128 = "(01){$gtin}(10){$lot}(21){$serial}";
        
        if ($packDate) {
            $gs1_128 .= "(13){$packDate}";
        }
        
        return $gs1_128;
    }

    /**
     * Parse GS1-128 barcode data
     */
    public function parseGS1_128(string $barcode): array
    {
        $data = [];
        
        // Remove FNC1 characters
        $barcode = str_replace(['|', chr(29)], '', $barcode);
        
        // Parse Application Identifiers
        preg_match_all('/$$(\d{2,4})$$([^\(]+)/', $barcode, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $ai = $match[1];
            $value = trim($match[2]);
            
            switch ($ai) {
                case '01':
                    $data['gtin'] = $value;
                    break;
                case '10':
                    $data['lot'] = $value;
                    break;
                case '21':
                    $data['serial'] = $value;
                    break;
                case '13':
                    $data['pack_date'] = $value;
                    break;
                case '15':
                    $data['best_before'] = $value;
                    break;
                case '17':
                    $data['expiry_date'] = $value;
                    break;
            }
        }
        
        return $data;
    }

    /**
     * Generate QR code data URL for a trace record
     */
    public function generateQRCode(TraceRecord $record, int $size = 300): string
    {
        $digitalLink = $this->generateDigitalLink($record);
        
        // Use Google Charts API for QR code generation
        // In production, consider using a PHP library like endroid/qr-code
        $qrUrl = "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl=" . urlencode($digitalLink);
        
        return $qrUrl;
    }

    /**
     * Generate QR code as SVG using a simple implementation
     */
    public function generateQRCodeSVG(TraceRecord $record): string
    {
        $digitalLink = $this->generateDigitalLink($record);
        
        // For production, use a proper QR code library
        // This is a placeholder that generates a simple data matrix
        $size = 200;
        $data = base64_encode($digitalLink);
        
        return <<<SVG
<svg width="{$size}" height="{$size}" xmlns="http://www.w3.org/2000/svg">
    <rect width="{$size}" height="{$size}" fill="white"/>
    <text x="50%" y="50%" text-anchor="middle" font-size="12" fill="black">
        QR Code: {$record->tlc}
    </text>
    <text x="50%" y="60%" text-anchor="middle" font-size="8" fill="gray">
        Scan to trace
    </text>
</svg>
SVG;
    }

    /**
     * Pad SKU to 14-digit GTIN format
     */
    private function padGTIN(string $sku): string
    {
        // Remove non-numeric characters
        $numeric = preg_replace('/[^0-9]/', '', $sku);
        
        // Pad to 14 digits (GTIN-14)
        return str_pad($numeric, 14, '0', STR_PAD_LEFT);
    }

    /**
     * Validate GTIN check digit
     */
    public function validateGTIN(string $gtin): bool
    {
        if (strlen($gtin) !== 14) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $multiplier = ($i % 2 === 0) ? 3 : 1;
            $sum += (int)$gtin[$i] * $multiplier;
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return (int)$gtin[13] === $checkDigit;
    }

    /**
     * Generate traceability landing page URL
     */
    public function generateTraceabilityURL(TraceRecord $record): string
    {
        return route('public.trace', ['tlc' => $record->tlc]);
    }
}
