<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TraceRecord;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TraceabilityController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    protected function validateTLC($tlc)
    {
        $validator = Validator::make(['tlc' => $tlc], [
            'tlc' => ['required', 'string', 'regex:/^[A-Z0-9\-]{8,50}$/']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid TLC format. Must be 8-50 alphanumeric characters.',
                'errors' => $validator->errors()
            ], 400);
        }

        return null;
    }

    public function lookup($tlc)
    {
        // Validate TLC format
        $validationError = $this->validateTLC($tlc);
        if ($validationError) return $validationError;

        Log::channel('traceability')->info('API Trace Lookup', [
            'tlc' => $tlc,
            'ip_hash' => hash('sha256', request()->ip()),
            'user_hash' => hash('sha256', (string)auth()->id()),
            'timestamp' => now()
        ]);

        $user = auth()->user();
        $cacheKey = "trace_lookup_{$tlc}_org_{$user->organization_id}";
        
        $record = Cache::remember($cacheKey, 300, function() use ($tlc, $user) {
            return TraceRecord::where('tlc', $tlc)
                ->where('organization_id', $user->organization_id)
                ->with(['product', 'location', 'cteEvents.location', 'cteEvents.partner'])
                ->first();
        });

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'TLC not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $record->id,
                'tlc' => $record->tlc,
                'product' => $record->product ? [
                    'id' => $record->product->id,
                    'name' => $record->product->product_name,
                    'code' => $record->product->product_code,
                    'is_ftl' => $record->product->is_ftl,
                ] : null,
                'quantity' => $record->quantity,
                'unit' => $record->unit,
                'lot_code' => $record->lot_code,
                'harvest_date' => $record->harvest_date,
                'location' => $record->location ? [
                    'id' => $record->location->id,
                    'name' => $record->location->location_name,
                    'type' => $record->location->location_type,
                ] : null,
                'status' => $record->status,
                'events_count' => $record->cteEvents->count(),
            ],
            'cached' => Cache::has($cacheKey)
        ]);
    }

    public function traceForward($tlc)
    {
        // Validate TLC format
        $validationError = $this->validateTLC($tlc);
        if ($validationError) return $validationError;

        Log::channel('traceability')->info('API Trace Forward', [
            'tlc' => $tlc,
            'ip_hash' => hash('sha256', request()->ip()),
            'user_hash' => hash('sha256', (string)auth()->id()),
        ]);

        $user = auth()->user();
        $record = TraceRecord::where('tlc', $tlc)
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'TLC not found',
            ], 404);
        }

        $cacheKey = "trace_forward_{$tlc}_org_{$user->organization_id}";
        
        $result = Cache::remember($cacheKey, 600, function() use ($record) {
            return $this->traceabilityService->traceForward($record);
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'cached' => Cache::has($cacheKey)
        ]);
    }

    public function traceBackward($tlc)
    {
        // Validate TLC format
        $validationError = $this->validateTLC($tlc);
        if ($validationError) return $validationError;

        Log::channel('traceability')->info('API Trace Backward', [
            'tlc' => $tlc,
            'ip_hash' => hash('sha256', request()->ip()),
            'user_hash' => hash('sha256', (string)auth()->id()),
        ]);

        $user = auth()->user();
        $record = TraceRecord::where('tlc', $tlc)
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'TLC not found',
            ], 404);
        }

        $cacheKey = "trace_backward_{$tlc}_org_{$user->organization_id}";
        
        $result = Cache::remember($cacheKey, 600, function() use ($record) {
            return $this->traceabilityService->traceBackward($record);
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'cached' => Cache::has($cacheKey)
        ]);
    }
}
