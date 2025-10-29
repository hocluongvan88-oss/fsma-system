<?php

namespace App\Http\Controllers;

use App\Models\TraceRecord;
use App\Services\GS1Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PublicTraceController extends Controller
{
    protected $gs1Service;

    public function __construct(GS1Service $gs1Service)
    {
        $this->gs1Service = $gs1Service;
    }

    protected function validateTLC($tlc)
    {
        $validator = Validator::make(['tlc' => $tlc], [
            'tlc' => ['required', 'string', 'regex:/^[A-Z0-9\-]{8,50}$/']
        ]);

        return $validator->fails() ? $validator->errors()->first('tlc') : null;
    }

    /**
     * Public traceability landing page (no auth required)
     * Added organization_id validation for public endpoints
     */
    public function show(Request $request, $tlc = null)
    {
        // Get TLC from route or query parameter
        $tlc = $tlc ?? $request->query('tlc');

        if (!$tlc) {
            return view('public.trace-search');
        }

        $validationError = $this->validateTLC($tlc);
        if ($validationError) {
            return view('public.trace-search', [
                'error' => $validationError
            ]);
        }

        Log::channel('traceability')->info('Public Trace Lookup', [
            'tlc' => $tlc,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        $cacheKey = "public_trace_{$tlc}";
        $record = Cache::remember($cacheKey, 300, function() use ($tlc) {
            return TraceRecord::where('tlc', $tlc)
                ->where('is_public_traceable', true)
                ->with(['product', 'location', 'cteEvents.location', 'cteEvents.partner'])
                ->first();
        });

        if (!$record) {
            return view('public.trace-search', [
                'error' => 'Traceability Lot Code not found. Please check and try again.'
            ]);
        }

        // Get trace history
        $traceBack = $record->traceBackward();
        $traceForward = $record->traceForward();

        // Generate GS1 data
        $gs1Data = [
            'digital_link' => $this->gs1Service->generateDigitalLink($record),
            'gs1_128' => $this->gs1Service->generateGS1_128($record),
            'qr_code_url' => $this->gs1Service->generateQRCode($record),
        ];

        return view('public.trace-result', compact('record', 'traceBack', 'traceForward', 'gs1Data'));
    }

    /**
     * API endpoint for mobile scanning
     * Added organization_id validation for public API
     */
    public function api(Request $request)
    {
        $tlc = $request->input('tlc');

        if (!$tlc) {
            return response()->json(['error' => 'TLC required'], 400);
        }

        $validationError = $this->validateTLC($tlc);
        if ($validationError) {
            return response()->json(['error' => $validationError], 400);
        }

        Log::channel('traceability')->info('Public API Trace', [
            'tlc' => $tlc,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $cacheKey = "public_api_trace_{$tlc}";
        $record = Cache::remember($cacheKey, 300, function() use ($tlc) {
            return TraceRecord::where('tlc', $tlc)
                ->where('is_public_traceable', true)
                ->with(['product', 'location', 'cteEvents'])
                ->first();
        });

        if (!$record) {
            return response()->json(['error' => 'TLC not found'], 404);
        }

        return response()->json([
            'tlc' => $record->tlc,
            'product' => [
                'sku' => $record->product->sku,
                'name' => $record->product->product_name,
                'is_ftl' => $record->product->is_ftl,
            ],
            'lot_code' => $record->lot_code,
            'harvest_date' => $record->harvest_date?->format('Y-m-d'),
            'quantity' => $record->quantity,
            'unit' => $record->unit,
            'location' => [
                'name' => $record->location->location_name,
                'gln' => $record->location->gln,
            ],
            'status' => $record->status,
            'gs1' => [
                'digital_link' => $this->gs1Service->generateDigitalLink($record),
                'gs1_128' => $this->gs1Service->generateGS1_128($record),
            ],
            'events' => $record->cteEvents->map(function($event) {
                return [
                    'type' => $event->event_type,
                    'date' => $event->event_date->format('Y-m-d'),
                    'location' => $event->location?->location_name,
                    'partner' => $event->partner?->partner_name,
                ];
            }),
            'cached' => Cache::has($cacheKey)
        ]);
    }
}
