<?php

namespace App\Services;

use App\Models\TraceRecord;
use App\Models\CTEEvent;
use Illuminate\Support\Facades\DB;

class TraceabilityService
{
    /**
     * Trace forward from a record
     */
    public function traceForward(TraceRecord $record)
    {
        $chain = [];
        $visited = [];
        
        $this->buildForwardChain($record, $chain, $visited);
        
        return [
            'origin' => $this->formatRecord($record),
            'chain' => $chain,
            'total_steps' => count($chain),
        ];
    }
    
    /**
     * Trace backward from a record
     */
    public function traceBackward(TraceRecord $record)
    {
        $chain = [];
        $visited = [];
        
        $this->buildBackwardChain($record, $chain, $visited);
        
        return [
            'destination' => $this->formatRecord($record),
            'chain' => $chain,
            'total_steps' => count($chain),
        ];
    }
    
    /**
     * Build forward chain recursively
     */
    protected function buildForwardChain(TraceRecord $record, &$chain, &$visited)
    {
        if (in_array($record->id, $visited)) {
            return;
        }
        
        $visited[] = $record->id;
        
        // Find shipping events where this record was shipped
        $shippingEvents = CTEEvent::where('event_type', 'shipping')
            ->where('trace_record_id', $record->id)
            ->where('status', 'active')
            ->with(['partner', 'location'])
            ->get();
        
        foreach ($shippingEvents as $event) {
            $step = [
                'event' => [
                    'id' => $event->id,
                    'type' => 'shipping',
                    'date' => $event->event_date,
                    'quantity' => $event->quantity,
                    'unit' => $event->unit,
                ],
                'from' => $this->formatLocation($event->location),
                'to' => $this->formatPartner($event->partner),
                'record' => $this->formatRecord($record),
            ];
            
            $chain[] = $step;
            
            // Find receiving events at destination
            $receivingEvents = CTEEvent::where('event_type', 'receiving')
                ->where('reference_document_number', $event->reference_document_number)
                ->where('status', 'active')
                ->with('traceRecord')
                ->get();
            
            foreach ($receivingEvents as $receivingEvent) {
                if ($receivingEvent->traceRecord) {
                    $this->buildForwardChain($receivingEvent->traceRecord, $chain, $visited);
                }
            }
        }
    }
    
    /**
     * Build backward chain recursively
     */
    protected function buildBackwardChain(TraceRecord $record, &$chain, &$visited)
    {
        if (in_array($record->id, $visited)) {
            return;
        }
        
        $visited[] = $record->id;
        
        // Find receiving event for this record
        $receivingEvent = CTEEvent::where('event_type', 'receiving')
            ->where('trace_record_id', $record->id)
            ->where('status', 'active')
            ->with(['partner', 'location'])
            ->first();
        
        if ($receivingEvent) {
            $step = [
                'event' => [
                    'id' => $receivingEvent->id,
                    'type' => 'receiving',
                    'date' => $receivingEvent->event_date,
                    'quantity' => $receivingEvent->quantity,
                    'unit' => $receivingEvent->unit,
                ],
                'from' => $this->formatPartner($receivingEvent->partner),
                'to' => $this->formatLocation($receivingEvent->location),
                'record' => $this->formatRecord($record),
            ];
            
            $chain[] = $step;
            
            // Find corresponding shipping event
            if ($receivingEvent->reference_document_number) {
                $shippingEvent = CTEEvent::where('event_type', 'shipping')
                    ->where('reference_document_number', $receivingEvent->reference_document_number)
                    ->where('status', 'active')
                    ->with('traceRecord')
                    ->first();
                
                if ($shippingEvent && $shippingEvent->traceRecord) {
                    $this->buildBackwardChain($shippingEvent->traceRecord, $chain, $visited);
                }
            }
        }
    }
    
    /**
     * Format record for output
     */
    protected function formatRecord(TraceRecord $record)
    {
        return [
            'id' => $record->id,
            'tlc' => $record->tlc,
            'product' => $record->product ? [
                'id' => $record->product->id,
                'name' => $record->product->product_name,
                'code' => $record->product->product_code,
            ] : null,
            'quantity' => $record->quantity,
            'unit' => $record->unit,
            'lot_code' => $record->lot_code,
            'harvest_date' => $record->harvest_date,
        ];
    }
    
    /**
     * Format location for output
     */
    protected function formatLocation($location)
    {
        if (!$location) return null;
        
        return [
            'id' => $location->id,
            'name' => $location->location_name,
            'type' => $location->location_type,
            'address' => $location->address,
        ];
    }
    
    /**
     * Format partner for output
     */
    protected function formatPartner($partner)
    {
        if (!$partner) return null;
        
        return [
            'id' => $partner->id,
            'name' => $partner->partner_name,
            'type' => $partner->partner_type,
        ];
    }
}
