<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FDAExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FDAExportController extends Controller
{
    protected FDAExportService $fdaExportService;

    public function __construct(FDAExportService $fdaExportService)
    {
        $this->fdaExportService = $fdaExportService;
    }

    /**
     * Export all CTE events in FDA-compliant format
     * GET /api/fda-export/all?format=json&start_date=2024-01-01&end_date=2024-12-31
     */
    public function exportAll(Request $request)
    {
        $validated = $request->validate([
            'format' => 'in:json,xml,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $format = $validated['format'] ?? 'json';
        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        $content = $this->fdaExportService->exportAllEvents($startDate, $endDate, $format);

        return $this->downloadResponse($content, $format, 'fda_export_all');
    }

    /**
     * Export events for a specific product
     * GET /api/fda-export/product/{productId}?format=json
     */
    public function exportByProduct(Request $request, int $productId)
    {
        $validated = $request->validate([
            'format' => 'in:json,xml,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $format = $validated['format'] ?? 'json';
        $startDate = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        $content = $this->fdaExportService->exportByProduct($productId, $startDate, $endDate, $format);

        return $this->downloadResponse($content, $format, "fda_export_product_{$productId}");
    }

    /**
     * Export events for a specific TLC
     * GET /api/fda-export/tlc/{tlc}?format=json
     */
    public function exportByTLC(Request $request, string $tlc)
    {
        $validated = $request->validate([
            'format' => 'in:json,xml,csv',
        ]);

        $format = $validated['format'] ?? 'json';

        $content = $this->fdaExportService->exportByTLC($tlc, $format);

        return $this->downloadResponse($content, $format, "fda_export_tlc_{$tlc}");
    }

    /**
     * Validate all events for FDA compliance
     * POST /api/fda-export/validate
     */
    public function validateCompliance()
    {
        $results = $this->fdaExportService->validateAllEventsCompliance();

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => "Validation complete: {$results['compliant_events']} compliant, {$results['non_compliant_events']} non-compliant",
        ]);
    }

    /**
     * Get compliance status summary
     * GET /api/fda-export/compliance-status
     */
    public function complianceStatus()
    {
        $totalEvents = \App\Models\CTEEvent::count();
        $compliantEvents = \App\Models\CTEEvent::where('fda_compliant', true)->count();
        $nonCompliantEvents = $totalEvents - $compliantEvents;

        return response()->json([
            'success' => true,
            'data' => [
                'total_events' => $totalEvents,
                'compliant_events' => $compliantEvents,
                'non_compliant_events' => $nonCompliantEvents,
                'compliance_percentage' => $totalEvents > 0 ? round(($compliantEvents / $totalEvents) * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * Generate download response with appropriate headers
     */
    private function downloadResponse(string $content, string $format, string $filename): Response
    {
        $mimeType = match ($format) {
            'xml' => 'application/xml',
            'csv' => 'text/csv',
            default => 'application/json',
        };

        $extension = $format;
        $timestamp = now()->format('Y-m-d_H-i-s');

        return response($content)
            ->header('Content-Type', $mimeType . '; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}_{$timestamp}.{$extension}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
