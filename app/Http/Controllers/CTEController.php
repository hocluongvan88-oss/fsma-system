<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;
use App\Models\TraceRecord;
use App\Models\CTEEvent;
use App\Models\ESignature;
use App\Models\TransformationItem;
use App\Services\QueryOptimizationService;
use App\Services\ConcurrentVoidService;
use App\Services\CTELoggingService;
use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CTEController extends Controller
{
    use Paginatable;

    protected $concurrentVoidService;
    protected $loggingService;

    public function __construct(
        ConcurrentVoidService $concurrentVoidService,
        CTELoggingService $loggingService
    ) {
        $this->concurrentVoidService = $concurrentVoidService;
        $this->loggingService = $loggingService;
    }

    // Receiving
    public function receiving()
    {
        $currentUser = auth()->user();
        
        $products = QueryOptimizationService::getActiveProducts($currentUser->organization_id, false);
        $locations = QueryOptimizationService::getActiveLocations($currentUser->organization_id);
        $suppliers = QueryOptimizationService::getPartnersByType($currentUser->organization_id, 'supplier');
            
        $recentEvents = CTEEvent::with(['traceRecord.product', 'partner', 'location'])
            ->whereHas('traceRecord', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            })
            ->where('event_type', 'receiving')
            ->orderBy('event_date', 'desc')
            ->limit(10)
            ->get();
        
        return view('cte.receiving', compact('products', 'locations', 'suppliers', 'recentEvents'));
    }

    public function storeReceiving(Request $request)
    {
        if (!auth()->user()->canCreateCteRecord()) {
            return back()->withInput()
                ->with('error', __('messages.cte_record_limit_reached'));
        }

        $rules = [
            'tlc' => 'required|string|max:100|unique:trace_records',
            'product_id' => 'required|exists:products,id',
            'product_description' => 'nullable|string|max:500',
            'product_lot_code' => 'nullable|string|max:100', // Added KDE #12 validation
            'quantity_received' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'receiving_location_gln' => 'nullable|string|regex:/^\d{13}$/',
            'receiving_location_name' => 'nullable|string|max:255',
            'harvest_location_gln' => 'nullable|string|regex:/^\d{13}$/', // Added KDE #8 validation
            'harvest_location_name' => 'nullable|string|max:255', // Added KDE #8 validation
            'partner_id' => 'required|exists:partners,id',
            'business_name' => 'nullable|string|max:255',
            'business_gln' => 'nullable|string|regex:/^\d{13}$/',
            'business_address' => 'nullable|string|max:500',
            'harvest_date' => 'nullable|date',
            'pack_date' => 'nullable|date|after_or_equal:harvest_date',
            'cooling_date' => 'nullable|date|after_or_equal:pack_date', // Added KDE #14 validation
            'event_date' => 'required|date|after_or_equal:cooling_date',
            'reference_doc' => 'required|string|max:100',
            'reference_doc_type' => 'nullable|in:PO,Invoice,BOL,AWB,Other', // Added KDE #17 validation
            'fda_compliance_notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ];

        if (auth()->user()->hasFeature('e_signatures')) {
            $rules['signature_password'] = 'nullable|string';
            $rules['signature_reason'] = 'nullable|string|max:500';
        }

        $validated = $request->validate($rules);

        if ($validated['harvest_date'] && $validated['pack_date']) {
            if ($validated['harvest_date'] > $validated['pack_date']) {
                return back()->withInput()->withErrors([
                    'pack_date' => __('messages.pack_date_after_harvest')
                ]);
            }
        }

        if ($validated['pack_date'] && isset($validated['cooling_date'])) {
            if ($validated['pack_date'] > $validated['cooling_date']) {
                return back()->withInput()->withErrors([
                    'cooling_date' => 'Cooling date must be after or equal to pack date'
                ]);
            }
        }

        if (isset($validated['cooling_date']) && $validated['event_date']) {
            if ($validated['cooling_date'] > $validated['event_date']) {
                return back()->withInput()->withErrors([
                    'event_date' => 'Event date must be after or equal to cooling date'
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $traceRecord = TraceRecord::create([
                'tlc' => $validated['tlc'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity_received'],
                'available_quantity' => $validated['quantity_received'],
                'consumed_quantity' => 0,
                'unit' => $validated['unit'],
                'location_id' => $validated['location_id'],
                'harvest_date' => $validated['harvest_date'] ?? null,
                'lot_code' => $validated['tlc'],
                'materialized_path' => '/' . $validated['tlc'] . '/',
                'status' => 'active',
                'organization_id' => auth()->user()->organization_id,
            ]);

            $cteEvent = CTEEvent::create([
                'event_type' => 'receiving',
                'trace_record_id' => $traceRecord->id,
                'event_date' => $validated['event_date'],
                'location_id' => $validated['location_id'],
                'partner_id' => $validated['partner_id'],
                'product_description' => $validated['product_description'] ?? null,
                'product_lot_code' => $validated['product_lot_code'] ?? null, // KDE #12
                'quantity_received' => $validated['quantity_received'],
                'quantity_unit' => $validated['unit'],
                'receiving_location_gln' => $validated['receiving_location_gln'] ?? null,
                'receiving_location_name' => $validated['receiving_location_name'] ?? null,
                'harvest_location_gln' => $validated['harvest_location_gln'] ?? null, // KDE #8
                'harvest_location_name' => $validated['harvest_location_name'] ?? null, // KDE #8
                'business_name' => $validated['business_name'] ?? null,
                'business_gln' => $validated['business_gln'] ?? null,
                'business_address' => $validated['business_address'] ?? null,
                'harvest_date' => $validated['harvest_date'] ?? null,
                'pack_date' => $validated['pack_date'] ?? null,
                'cooling_date' => $validated['cooling_date'] ?? null, // KDE #14
                'reference_doc' => $validated['reference_doc'],
                'reference_doc_type' => $validated['reference_doc_type'] ?? null, // KDE #17
                'fda_compliance_notes' => $validated['fda_compliance_notes'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'traceability_lot_code' => $validated['tlc'],
            ]);

            if (auth()->user()->hasFeature('e_signatures')) {
                try {
                    $signature = ESignature::createSignature(
                        auth()->user(),
                        'cte_events',
                        $cteEvent->id,
                        'create_receiving',
                        $validated['signature_password'] ?? '',
                        $validated['signature_reason'] ?? 'Created receiving event'
                    );

                    $cteEvent->update(['signature_id' => $signature->id]);

                    $this->loggingService->logESignatureCreated(
                        $signature->id,
                        'create_receiving',
                        $cteEvent->id
                    );
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('error', __('messages.signature_failed') . ': ' . $e->getMessage());
                }
            }

            QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

            DB::commit();
            $this->loggingService->logCTEEventCreated('receiving', $cteEvent->id, [
                'tlc' => $validated['tlc'],
                'quantity' => $validated['quantity_received'],
                'unit' => $validated['unit'],
            ]);
            return redirect()->route('cte.receiving')
                ->with('success', __('messages.receiving_event_recorded'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->logTransactionFailure('receiving', $e);
            return back()->withInput()
                ->with('error', __('messages.failed_to_record_receiving') . ': ' . $e->getMessage());
        }
    }

    // Transformation
    public function transformation()
    {
        $currentUser = auth()->user();
        
        $products = QueryOptimizationService::getActiveProducts($currentUser->organization_id, false);
        $locations = QueryOptimizationService::getActiveLocations($currentUser->organization_id);
        $activeTLCs = QueryOptimizationService::getActiveTraceRecords($currentUser->organization_id);
        $recentEvents = QueryOptimizationService::getRecentCTEEvents('transformation', $currentUser->organization_id, 10);
        
        $this->loggingService->logBatch('Active TLCs loaded', $activeTLCs, 10);
        
        if ($this->loggingService->isDebugEnabled()) {
            foreach ($activeTLCs as $tlc) {
                if (is_null($tlc->available_quantity)) {
                    $this->loggingService->debug("TLC {$tlc->tlc} has NULL available_quantity, fixing");
                    $tlc->available_quantity = $tlc->quantity;
                    $tlc->consumed_quantity = 0;
                    $tlc->save();
                }
            }
        }
        
        return view('cte.transformation', compact('products', 'locations', 'activeTLCs', 'recentEvents'));
    }

    public function storeTransformation(Request $request)
    {
        if (!auth()->user()->canCreateCteRecord()) {
            return back()->withInput()
                ->with('error', __('messages.cte_record_limit_reached'));
        }

        $validated = $request->validate([
            'output_tlc' => 'required|string|max:100|unique:trace_records,tlc',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'quantity_unit' => 'required|string|max:20',
            'location_id' => 'required|exists:locations,id',
            'input_trace_record_ids' => 'required|array|min:1',
            'input_trace_record_ids.*' => 'exists:trace_records,id',
            'transformation_description' => 'required|string|max:500',
            'output_tlcs' => 'nullable|string|max:500',
            'event_date' => 'required|date',
            'reference_doc' => 'nullable|string|max:100',
            'fda_compliance_notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $inputRecords = TraceRecord::whereIn('id', $validated['input_trace_record_ids'])
                ->where('organization_id', auth()->user()->organization_id)
                ->lockForUpdate() // Lock records to prevent race conditions
                ->get();
            
            foreach ($inputRecords as $inputRecord) {
                if ($inputRecord->available_quantity <= 0) {
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        'input_trace_record_ids' => sprintf(
                            'TLC %s has no available quantity (%.2f %s available)',
                            $inputRecord->tlc,
                            $inputRecord->available_quantity,
                            $inputRecord->unit
                        )
                    ]);
                }
            }
            
            $totalAvailable = $inputRecords->sum('available_quantity');
            
            if ($validated['quantity'] > $totalAvailable) {
                DB::rollBack();
                return back()->withInput()->withErrors([
                    'quantity' => sprintf(
                        'Output quantity (%.2f) exceeds total available input (%.2f). Yield cannot exceed 100%%.',
                        $validated['quantity'],
                        $totalAvailable
                    )
                ]);
            }

            $yieldPercentage = ($validated['quantity'] / $totalAvailable) * 100;
            if ($yieldPercentage > 100) {
                DB::rollBack();
                return back()->withInput()->withErrors([
                    'quantity' => sprintf('Transformation yield is %.2f%% - exceeds 100%% maximum', $yieldPercentage)
                ]);
            }

            $paths = $inputRecords->pluck('materialized_path')->filter()->toArray();
            $combinedPath = implode('', $paths) . $validated['output_tlc'] . '/';

            $outputRecord = TraceRecord::create([
                'tlc' => $validated['output_tlc'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'available_quantity' => $validated['quantity'], // Per documentation: output starts with full quantity
                'consumed_quantity' => 0,
                'unit' => $validated['quantity_unit'],
                'location_id' => $validated['location_id'],
                'lot_code' => $validated['output_tlc'],
                'materialized_path' => $combinedPath,
                'status' => 'active',
                'organization_id' => auth()->user()->organization_id,
            ]);

            $outputTlcsArray = [];
            if (!empty($validated['output_tlcs'])) {
                $decoded = json_decode($validated['output_tlcs'], true);
                $outputTlcsArray = is_array($decoded) ? $decoded : [$validated['output_tlc']];
            } else {
                $outputTlcsArray = [$validated['output_tlc']];
            }

            $cteEvent = CTEEvent::create([
                'event_type' => 'transformation',
                'trace_record_id' => $outputRecord->id,
                'event_date' => $validated['event_date'],
                'location_id' => $validated['location_id'],
                'input_tlcs' => $inputRecords->pluck('tlc')->toArray(),
                'output_tlcs' => $outputTlcsArray,
                'transformation_description' => $validated['transformation_description'],
                'output_quantity' => $validated['quantity'],
                'reference_doc' => $validated['reference_doc'] ?? null,
                'fda_compliance_notes' => $validated['fda_compliance_notes'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'traceability_lot_code' => $validated['output_tlc'],
            ]);

            $remainingNeeded = $validated['quantity'];
            
            foreach ($inputRecords as $inputRecord) {
                if ($remainingNeeded <= 0) {
                    break;
                }
                
                $amountToConsume = min($inputRecord->available_quantity, $remainingNeeded);
                
                TransformationItem::create([
                    'transformation_event_id' => $cteEvent->id,
                    'input_trace_record_id' => $inputRecord->id,
                    'quantity_used' => $amountToConsume,
                    'unit' => $validated['quantity_unit'],
                ]);
                
                $inputRecord->consume($amountToConsume);
                $remainingNeeded -= $amountToConsume;
                
                DB::table('trace_relationships')->insert([
                    'parent_id' => $inputRecord->id,
                    'child_id' => $outputRecord->id,
                    'relationship_type' => 'INPUT', // Standardized relationship type
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            DB::table('trace_relationships')->insert([
                'parent_id' => $outputRecord->id,
                'child_id' => $outputRecord->id,
                'relationship_type' => 'OUTPUT', // Standardized relationship type
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

            DB::commit();
            $this->loggingService->logCTEEventCreated('transformation', $cteEvent->id, [
                'output_tlc' => $validated['output_tlc'],
                'input_count' => $inputRecords->count(),
                'output_quantity' => $validated['quantity'],
                'yield_percentage' => $yieldPercentage,
            ]);
            return redirect()->route('cte.transformation')
                ->with('success', __('messages.transformation_event_recorded'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->logTransactionFailure('transformation', $e);
            return back()->withInput()
                ->with('error', __('messages.failed_to_record_transformation') . ': ' . $e->getMessage());
        }
    }

    // Shipping
    public function shipping()
    {
        $currentUser = auth()->user();
        
        $activeTLCs = QueryOptimizationService::getActiveTraceRecords($currentUser->organization_id);
        $locations = QueryOptimizationService::getActiveLocations($currentUser->organization_id);
        $customers = QueryOptimizationService::getPartnersByType($currentUser->organization_id, 'customer');
        $recentEvents = QueryOptimizationService::getRecentCTEEvents('shipping', $currentUser->organization_id, 10);
        
        return view('cte.shipping', compact('activeTLCs', 'locations', 'customers', 'recentEvents'));
    }

    public function storeShipping(Request $request)
    {
        if (!auth()->user()->canCreateCteRecord()) {
            return back()->withInput()
                ->with('error', __('messages.cte_record_limit_reached'));
        }

        $validated = $request->validate([
            'trace_record_ids' => 'required|array|min:1',
            'trace_record_ids.*' => 'exists:trace_records,id',
            'quantities_shipped' => 'required|array|min:1',
            'quantities_shipped.*' => 'required|numeric|min:0.01',
            'location_id' => 'required|exists:locations,id',
            'partner_id' => 'required|exists:partners,id',
            'shipping_location_gln' => 'nullable|string|regex:/^\d{13}$/',
            'shipping_location_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'receiving_date_expected' => 'nullable|date|after_or_equal:event_date',
            'shipping_reference_doc' => 'required|string|max:100',
            'fda_compliance_notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['trace_record_ids'] as $index => $recordId) {
                $traceRecord = TraceRecord::where('id', $recordId)
                    ->where('organization_id', auth()->user()->organization_id)
                    ->lockForUpdate() // Lock to prevent race conditions
                    ->firstOrFail();
                
                $quantityToShip = $validated['quantities_shipped'][$index] ?? $traceRecord->available_quantity;
                
                if (!$traceRecord->canConsume($quantityToShip)) {
                    DB::rollBack();
                    return back()->withInput()->withErrors([
                        'quantities_shipped' => sprintf(
                            'TLC %s: Cannot ship %.2f %s. Only %.2f %s available.',
                            $traceRecord->tlc,
                            $quantityToShip,
                            $traceRecord->unit,
                            $traceRecord->available_quantity,
                            $traceRecord->unit
                        )
                    ]);
                }
                
                $cteEvent = CTEEvent::create([
                    'event_type' => 'shipping',
                    'trace_record_id' => $traceRecord->id,
                    'event_date' => $validated['event_date'],
                    'location_id' => $validated['location_id'],
                    'partner_id' => $validated['partner_id'],
                    'shipping_location_gln' => $validated['shipping_location_gln'] ?? null,
                    'shipping_location_name' => $validated['shipping_location_name'],
                    'shipping_date' => $validated['event_date'],
                    'quantity_shipped' => $quantityToShip, // Added per documentation
                    'receiving_date_expected' => $validated['receiving_date_expected'] ?? null,
                    'shipping_reference_doc' => $validated['shipping_reference_doc'],
                    'fda_compliance_notes' => $validated['fda_compliance_notes'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'traceability_lot_code' => $traceRecord->tlc,
                ]);
                
                $traceRecord->consume($quantityToShip);
                
                DB::table('trace_relationships')->insert([
                    'parent_id' => $traceRecord->id,
                    'child_id' => null,
                    'relationship_type' => 'OUTPUT', // Standardized: shipping is OUTPUT (end of chain)
                    'cte_event_id' => $cteEvent->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

            DB::commit();
            $this->loggingService->logCTEEventCreated('shipping', 0, [
                'tlc_count' => count($validated['trace_record_ids']),
                'total_quantity' => array_sum($validated['quantities_shipped']),
            ]);
            return redirect()->route('cte.shipping')
                ->with('success', __('messages.shipping_event_recorded'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->logTransactionFailure('shipping', $e);
            return back()->withInput()
                ->with('error', __('messages.failed_to_record_shipping') . ': ' . $e->getMessage());
        }
    }

    public function voidManagement(Request $request)
    {
        $currentUser = auth()->user();
        
        $query = CTEEvent::with(['traceRecord.product', 'voidedBy'])
            ->whereHas('traceRecord', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            })
            ->where('status', 'voided');
        
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('voided_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('voided_at', '<=', $request->to_date);
        }
        
        if ($request->filled('search')) {
            $query->whereHas('traceRecord', function($q) use ($request) {
                $q->where('tlc', 'like', '%' . $request->search . '%');
            });
        }
        
        $voidedEvents = $query->orderBy('voided_at', 'desc')->paginate(15);
        
        $totalVoidedCount = CTEEvent::where('status', 'voided')
            ->whereHas('traceRecord', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            })
            ->count();
        
        $receivingVoidedCount = CTEEvent::where('status', 'voided')
            ->where('event_type', 'receiving')
            ->whereHas('traceRecord', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            })
            ->count();
        
        $transformationVoidedCount = CTEEvent::where('status', 'voided')
            ->where('event_type', 'transformation')
            ->whereHas('traceRecord', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            })
            ->count();
        
        $shippingVoidedCount = CTEEvent::where('status', 'voided')
            ->where('event_type', 'shipping')
            ->whereHas('traceRecord', function($q) use ($currentUser) {
                $q->where('organization_id', $currentUser->organization_id);
            })
            ->count();
        
        return view('cte.void-management', compact(
            'voidedEvents',
            'totalVoidedCount',
            'receivingVoidedCount',
            'transformationVoidedCount',
            'shippingVoidedCount'
        ));
    }

    public function voidAndReentry(Request $request, $eventId)
    {
        $event = CTEEvent::where('id', $eventId)
            ->where('status', 'active')
            ->lockForUpdate() // CRITICAL: Lock row to prevent concurrent void attempts
            ->firstOrFail();
        
        if ($event->void_count >= 1) {
            return back()->with('error', __('messages.event_already_voided_once'));
        }
        
        $user = auth()->user();
        $canVoid = $user->isAdmin() || 
                   $event->created_at->diffInHours(now()) <= 2;
        
        if (!$canVoid) {
            return back()->with('error', __('messages.void_not_allowed'));
        }

        $validated = $request->validate([
            'void_reason' => 'required|string',
            'void_notes' => 'required|string',
            'signature_password' => 'required|string',
        ]);
        
        DB::beginTransaction();
        try {
            try {
                $voidSignature = ESignature::createSignature(
                    auth()->user(),
                    'cte_events',
                    $event->id,
                    'void',
                    $validated['signature_password'],
                    $validated['void_reason'] . ': ' . $validated['void_notes']
                );

                $this->loggingService->logESignatureCreated(
                    $voidSignature->id,
                    'void',
                    $event->id
                );
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', __('messages.signature_failed') . ': ' . $e->getMessage());
            }

            $result = $this->concurrentVoidService->voidEvent(
                $eventId,
                $validated['void_reason'],
                $validated['void_notes']
            );
            
            DB::commit();
            $this->loggingService->logVoidEvent($eventId, $validated['void_reason'], [
                'execution_time_ms' => $result['execution_time_ms'],
                'signature_id' => $voidSignature->id,
            ]);
            
            return redirect()->route('cte.' . $event->event_type . '.reentry', ['event' => $event->id])
                ->with('success', __('messages.event_voided_reentry_ready'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            $this->loggingService->logTransactionFailure('void', $e);
            return back()->with('error', __('messages.void_failed') . ': ' . $e->getMessage());
        }
    }
    
    public function showReentryForm($eventId)
    {
        $voidedEvent = CTEEvent::where('id', $eventId)
            ->where('status', 'voided')
            ->firstOrFail();
        
        $currentUser = auth()->user();
        
        switch ($voidedEvent->event_type) {
            case 'receiving':
                $products = QueryOptimizationService::getActiveProducts($currentUser->organization_id, false);
                $locations = QueryOptimizationService::getActiveLocations($currentUser->organization_id);
                $suppliers = QueryOptimizationService::getPartnersByType($currentUser->organization_id, 'supplier');
                
                // This ensures $recentEvents contains CTEEvent models with traceRecord relationship
                $recentEvents = CTEEvent::with(['traceRecord.product', 'partner', 'location'])
                    ->whereHas('traceRecord', function($q) use ($currentUser) {
                        $q->where('organization_id', $currentUser->organization_id);
                    })
                    ->where('event_type', 'receiving')
                    ->orderBy('event_date', 'desc')
                    ->limit(10)
                    ->get();
                
                return view('cte.receiving', compact('products', 'locations', 'suppliers', 'recentEvents'))
                    ->with('reentryData', $voidedEvent);
                    
            case 'shipping':
                $activeTLCs = QueryOptimizationService::getActiveTraceRecords($currentUser->organization_id);
                $locations = QueryOptimizationService::getActiveLocations($currentUser->organization_id);
                $customers = QueryOptimizationService::getPartnersByType($currentUser->organization_id, 'customer');
                
                $recentEvents = CTEEvent::with(['traceRecord.product', 'partner', 'location'])
                    ->whereHas('traceRecord', function($q) use ($currentUser) {
                        $q->where('organization_id', $currentUser->organization_id);
                    })
                    ->where('event_type', 'shipping')
                    ->orderBy('event_date', 'desc')
                    ->limit(10)
                    ->get();
                
                return view('cte.shipping', compact('activeTLCs', 'locations', 'customers', 'recentEvents'))
                    ->with('reentryData', $voidedEvent);
                    
            case 'transformation':
                $products = QueryOptimizationService::getActiveProducts($currentUser->organization_id, false);
                $locations = QueryOptimizationService::getActiveLocations($currentUser->organization_id);
                $activeTLCs = QueryOptimizationService::getActiveTraceRecords($currentUser->organization_id);
                
                $recentEvents = CTEEvent::with(['traceRecord.product', 'partner', 'location'])
                    ->whereHas('traceRecord', function($q) use ($currentUser) {
                        $q->where('organization_id', $currentUser->organization_id);
                    })
                    ->where('event_type', 'transformation')
                    ->orderBy('event_date', 'desc')
                    ->limit(10)
                    ->get();
                
                return view('cte.transformation', compact('products', 'locations', 'activeTLCs', 'recentEvents'))
                    ->with('reentryData', $voidedEvent);
        }
    }
}
