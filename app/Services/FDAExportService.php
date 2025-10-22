<?php

namespace App\Services;

use App\Models\CTEEvent;
use App\Models\ExportLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class FDAExportService
{
    /**
     * Generate unique export ID
     */
    private function generateExportId(): string
    {
        $code = strtoupper(Str::random(8));
        return "EX-{$code}";
    }

    /**
     * Calculate SHA-256 hash of content
     */
    private function calculateContentHash(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Log export to database with hash
     */
    private function logExport(
        string $content,
        string $fileType,
        string $exportScope,
        ?string $scopeValue,
        int $recordCount,
        ?string $startRecordId = null,
        ?string $endRecordId = null
    ): ExportLog {
        $exportId = $this->generateExportId();
        $contentHash = $this->calculateContentHash($content);

        return ExportLog::create([
            'user_id' => Auth::id(),
            'export_id' => $exportId,
            'file_type' => $fileType,
            'export_scope' => $exportScope,
            'scope_value' => $scopeValue,
            'content_hash' => $contentHash,
            'file_size' => strlen($content),
            'record_count' => $recordCount,
            'start_record_id' => $startRecordId,
            'end_record_id' => $endRecordId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Add metadata footer to exported content
     */
    private function addMetadataFooter(string $content, ExportLog $exportLog, string $fileType): string
    {
        $metadata = [
            'export_id' => $exportLog->export_id,
            'content_hash' => $exportLog->content_hash,
            'exported_by' => Auth::user()?->name ?? 'System',
            'exported_at' => now()->toIso8601String(),
            'record_count' => $exportLog->record_count,
            'verified' => 'Not modified',
            'verification_url' => url('/api/verify-export'),
        ];

        $footer = "\n═══════════════════════════════════════════════════════════\n";
        $footer .= "FSMA Export - trace.veximglobal.com\n";
        $footer .= "Export ID: {$metadata['export_id']}\n";
        $footer .= "Hash: {$metadata['content_hash']}\n";
        $footer .= "Verified: {$metadata['verified']}\n";
        $footer .= "Exported By: {$metadata['exported_by']}\n";
        $footer .= "Timestamp: {$metadata['exported_at']}\n";
        $footer .= "Records: {$metadata['record_count']}\n";
        $footer .= "═══════════════════════════════════════════════════════════\n";

        if ($fileType === 'json') {
            // For JSON, append metadata as a separate object
            $decoded = json_decode($content, true);
            $decoded['_metadata'] = $metadata;
            return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } elseif ($fileType === 'xml') {
            // For XML, append metadata as a comment
            return $content . "\n<!-- " . str_replace('--', '- -', implode("\n", array_map(
                fn($k, $v) => "$k: $v",
                array_keys($metadata),
                array_values($metadata)
            ))) . " -->";
        } else {
            // For CSV, append as comment lines
            return $content . $footer;
        }
    }

    /**
     * Export all CTE events with hash protection
     */
    public function exportAllEvents(?Carbon $startDate = null, ?Carbon $endDate = null, string $format = 'json'): string
    {
        $query = CTEEvent::query();

        if ($startDate && $endDate) {
            $query->whereBetween('event_date', [$startDate, $endDate]);
        }

        $events = $query->get();
        $recordCount = $events->count();
        $startRecordId = $events->first()?->id;
        $endRecordId = $events->last()?->id;

        $content = $this->formatExport($events, $format);

        // Log export with hash
        $exportLog = $this->logExport(
            $content,
            $format,
            'all',
            null,
            $recordCount,
            (string)$startRecordId,
            (string)$endRecordId
        );

        // Add metadata footer
        return $this->addMetadataFooter($content, $exportLog, $format);
    }

    /**
     * Export events by product with hash protection
     */
    public function exportByProduct(int $productId, ?Carbon $startDate = null, ?Carbon $endDate = null, string $format = 'json'): string
    {
        $query = CTEEvent::where('product_id', $productId);

        if ($startDate && $endDate) {
            $query->whereBetween('event_date', [$startDate, $endDate]);
        }

        $events = $query->get();
        $recordCount = $events->count();
        $startRecordId = $events->first()?->id;
        $endRecordId = $events->last()?->id;

        $content = $this->formatExport($events, $format);

        // Log export with hash
        $exportLog = $this->logExport(
            $content,
            $format,
            'product',
            (string)$productId,
            $recordCount,
            (string)$startRecordId,
            (string)$endRecordId
        );

        // Add metadata footer
        return $this->addMetadataFooter($content, $exportLog, $format);
    }

    /**
     * Export events by TLC with hash protection
     */
    public function exportByTLC(string $tlc, string $format = 'json'): string
    {
        $events = CTEEvent::where('traceability_lot_code', $tlc)
            ->orWhereJsonContains('input_tlcs', $tlc)
            ->orWhereJsonContains('output_tlcs', $tlc)
            ->get();

        $recordCount = $events->count();
        $startRecordId = $events->first()?->id;
        $endRecordId = $events->last()?->id;

        $content = $this->formatExport($events, $format);

        // Log export with hash
        $exportLog = $this->logExport(
            $content,
            $format,
            'tlc',
            $tlc,
            $recordCount,
            (string)$startRecordId,
            (string)$endRecordId
        );

        // Add metadata footer
        return $this->addMetadataFooter($content, $exportLog, $format);
    }

    /**
     * Format events for export
     */
    private function formatExport(array $events, string $format): string
    {
        $data = [];

        foreach ($events as $event) {
            $data[] = $this->formatEvent($event, $format);
        }

        if ($format === 'json') {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } elseif ($format === 'xml') {
            return $this->formatXml($data);
        } else {
            return $this->formatCsv($data);
        }
    }

    /**
     * Format single event for export
     */
    private function formatEvent(CTEEvent $event, string $format): array
    {
        $eventData = [
            'event_type' => $event->event_type,
            'event_date' => $event->event_date?->toIso8601String(),
            'traceability_lot_code' => $event->traceability_lot_code ?? $event->traceRecord?->tlc,
            'product_description' => $event->product_description,
            'quantity_received' => $event->quantity_received,
            'quantity_unit' => $event->quantity_unit,
            'receiving_location_gln' => $event->receiving_location_gln ?? $event->location?->gln,
            'receiving_location_name' => $event->receiving_location_name ?? $event->location?->location_name,
            'shipping_location_gln' => $event->shipping_location_gln,
            'shipping_location_name' => $event->shipping_location_name,
            'business_name' => $event->business_name,
            'business_gln' => $event->business_gln,
            'business_address' => $event->business_address,
            'input_tlcs' => $event->input_tlcs,
            'output_tlcs' => $event->output_tlcs,
            'transformation_description' => $event->transformation_description,
            'reference_doc' => $event->reference_doc,
            'shipping_reference_doc' => $event->shipping_reference_doc,
            'shipping_date' => $event->shipping_date?->toIso8601String(),
            'receiving_date_expected' => $event->receiving_date_expected,
            'fda_compliant' => $event->fda_compliant,
        ];

        return $eventData;
    }

    /**
     * Format XML content
     */
    private function formatXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');
        $xml->addAttribute('version', '1.0');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'fda-export.xsd');

        foreach ($data as $item) {
            $event = $xml->addChild('event');
            $event->addAttribute('event_type', $item['event_type']);
            $event->addAttribute('event_date', $item['event_date']);
            $event->addAttribute('traceability_lot_code', $item['traceability_lot_code']);
            $event->addAttribute('product_description', $item['product_description']);
            $event->addAttribute('quantity_received', $item['quantity_received']);
            $event->addAttribute('quantity_unit', $item['quantity_unit']);
            $event->addAttribute('receiving_location_gln', $item['receiving_location_gln']);
            $event->addAttribute('receiving_location_name', $item['receiving_location_name']);
            $event->addAttribute('shipping_location_gln', $item['shipping_location_gln']);
            $event->addAttribute('shipping_location_name', $item['shipping_location_name']);
            $event->addAttribute('business_name', $item['business_name']);
            $event->addAttribute('business_gln', $item['business_gln']);
            $event->addAttribute('business_address', $item['business_address']);
            $event->addAttribute('input_tlcs', json_encode($item['input_tlcs']));
            $event->addAttribute('output_tlcs', json_encode($item['output_tlcs']));
            $event->addAttribute('transformation_description', $item['transformation_description']);
            $event->addAttribute('reference_doc', $item['reference_doc']);
            $event->addAttribute('shipping_reference_doc', $item['shipping_reference_doc']);
            $event->addAttribute('shipping_date', $item['shipping_date']);
            $event->addAttribute('receiving_date_expected', $item['receiving_date_expected']);
            $event->addAttribute('fda_compliant', $item['fda_compliant']);
        }

        return $xml->asXML();
    }

    /**
     * Format CSV content
     */
    private function formatCsv(array $data): string
    {
        $headers = [
            'event_type',
            'event_date',
            'traceability_lot_code',
            'product_description',
            'quantity_received',
            'quantity_unit',
            'receiving_location_gln',
            'receiving_location_name',
            'shipping_location_gln',
            'shipping_location_name',
            'business_name',
            'business_gln',
            'business_address',
            'input_tlcs',
            'output_tlcs',
            'transformation_description',
            'reference_doc',
            'shipping_reference_doc',
            'shipping_date',
            'receiving_date_expected',
            'fda_compliant',
        ];

        $csv = fopen('php://memory', 'w');
        fputcsv($csv, $headers);

        foreach ($data as $item) {
            fputcsv($csv, [
                $item['event_type'],
                $item['event_date'],
                $item['traceability_lot_code'],
                $item['product_description'],
                $item['quantity_received'],
                $item['quantity_unit'],
                $item['receiving_location_gln'],
                $item['receiving_location_name'],
                $item['shipping_location_gln'],
                $item['shipping_location_name'],
                $item['business_name'],
                $item['business_gln'],
                $item['business_address'],
                json_encode($item['input_tlcs']),
                json_encode($item['output_tlcs']),
                $item['transformation_description'],
                $item['reference_doc'],
                $item['shipping_reference_doc'],
                $item['shipping_date'],
                $item['receiving_date_expected'],
                $item['fda_compliant'],
            ]);
        }

        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);

        return $csvContent;
    }
}
