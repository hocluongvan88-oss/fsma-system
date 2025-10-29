<?php

namespace App\Services;

use App\Models\CTEEvent;
use App\Models\TraceRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Quantity Tracking Service
 * Enforces quantity conservation rules for transformation events
 * Ensures output quantity ≤ input quantity (accounting principle)
 */
class QuantityTrackingService
{
    /**
     * Track quantity for a CTE event
     * Validates that transformation output doesn't exceed input
     */
    public function trackQuantity(CTEEvent $event): array
    {
        $errors = [];

        if ($event->isTransformation()) {
            $errors = array_merge($errors, $this->validateTransformationQuantity($event));
        } elseif ($event->isShipping()) {
            $errors = array_merge($errors, $this->validateShippingQuantity($event));
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate transformation quantity conservation
     * Output quantity must not exceed input quantity
     */
    private function validateTransformationQuantity(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->input_tlcs) || !is_array($event->input_tlcs)) {
            $errors[] = 'No input TLCs specified for transformation';
            return $errors;
        }

        $totalInputQuantity = 0;
        $inputDetails = [];

        foreach ($event->input_tlcs as $tlc) {
            $traceRecord = TraceRecord::where('tlc', $tlc)->first();
            
            if (!$traceRecord) {
                $errors[] = "Input TLC '{$tlc}' not found in system";
                continue;
            }

            $availableQuantity = $this->getAvailableQuantity($traceRecord);
            
            if ($availableQuantity <= 0) {
                $errors[] = "Input TLC '{$tlc}' has no available quantity (fully consumed)";
                continue;
            }

            $totalInputQuantity += $availableQuantity;
            $inputDetails[$tlc] = [
                'available' => $availableQuantity,
                'unit' => $traceRecord->unit,
            ];
        }

        if ($event->output_quantity && $totalInputQuantity > 0) {
            if ($event->output_quantity > $totalInputQuantity) {
                $errors[] = sprintf(
                    'Output quantity (%.2f) exceeds total input quantity (%.2f). Transformation yield cannot exceed 100%%.',
                    $event->output_quantity,
                    $totalInputQuantity
                );
            }

            $yieldPercentage = ($event->output_quantity / $totalInputQuantity) * 100;
            if ($yieldPercentage > 100) {
                $errors[] = sprintf(
                    'Transformation yield is %.2f%% - exceeds 100%% maximum',
                    $yieldPercentage
                );
            }

            if ($yieldPercentage < 50) {
                Log::warning('Low transformation yield detected', [
                    'event_id' => $event->id,
                    'yield_percentage' => $yieldPercentage,
                    'input_quantity' => $totalInputQuantity,
                    'output_quantity' => $event->output_quantity,
                ]);
            }
        }

        if ($event->output_quantity && $event->output_quantity < 0) {
            $errors[] = 'Output quantity cannot be negative';
        }

        Log::info('Transformation Quantity Validation', [
            'event_id' => $event->id,
            'input_tlcs' => $event->input_tlcs,
            'total_input_quantity' => $totalInputQuantity,
            'output_quantity' => $event->output_quantity,
            'yield_percentage' => $totalInputQuantity > 0 ? (($event->output_quantity / $totalInputQuantity) * 100) : 0,
            'errors_count' => count($errors),
        ]);

        return $errors;
    }

    /**
     * Validate shipping quantity
     * Ensures shipped quantity doesn't exceed available quantity
     */
    private function validateShippingQuantity(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->traceability_lot_code) && empty($event->traceRecord?->tlc)) {
            $errors[] = 'Traceability Lot Code is required for shipping events';
            return $errors;
        }

        $tlc = $event->traceability_lot_code ?? $event->traceRecord?->tlc;
        $traceRecord = TraceRecord::where('tlc', $tlc)->first();

        if (!$traceRecord) {
            $errors[] = "TLC '{$tlc}' not found in system";
            return $errors;
        }

        $availableQuantity = $this->getAvailableQuantity($traceRecord);

        if ($event->quantity_received && $availableQuantity > 0) {
            if ($event->quantity_received > $availableQuantity) {
                $errors[] = sprintf(
                    'Shipping quantity (%.2f %s) exceeds available quantity (%.2f %s)',
                    $event->quantity_received,
                    $event->quantity_unit ?? 'units',
                    $availableQuantity,
                    $traceRecord->unit_of_measure
                );
            }
        }

        return $errors;
    }

    /**
     * Get available quantity for a trace record
     * Calculates: received - shipped - consumed
     */
    public function getAvailableQuantity(TraceRecord $traceRecord): float
    {
        if ($traceRecord->available_quantity !== null) {
            return max(0, (float) $traceRecord->available_quantity);
        }
        
        $receivedQuantity = $this->getTotalReceivedQuantity($traceRecord);
        $shippedQuantity = $this->getTotalShippedQuantity($traceRecord);
        $consumedQuantity = $this->getTotalConsumedQuantity($traceRecord);

        $available = $receivedQuantity - $shippedQuantity - $consumedQuantity;
        
        return max(0, $available);
    }

    /**
     * Get total received quantity for a TLC
     */
    private function getTotalReceivedQuantity(TraceRecord $traceRecord): float
    {
        return (float) CTEEvent::where('traceability_lot_code', $traceRecord->tlc)
            ->where('event_type', 'receiving')
            ->sum('quantity_received');
    }

    /**
     * Get total shipped quantity for a TLC
     */
    private function getTotalShippedQuantity(TraceRecord $traceRecord): float
    {
        return (float) CTEEvent::where('traceability_lot_code', $traceRecord->tlc)
            ->where('event_type', 'shipping')
            ->where('status', 'active') // Only count active (non-voided) events
            ->sum('quantity_received');
    }

    /**
     * Get total consumed quantity for a TLC (used in transformations)
     */
    private function getTotalConsumedQuantity(TraceRecord $traceRecord): float
    {
        // Use whereJsonContains to find transformation events with this TLC as input
        $consumed = (float) CTEEvent::where('event_type', 'transformation')
            ->where('status', 'active') // Only count active (non-voided) events
            ->whereJsonContains('input_tlcs', $traceRecord->tlc)
            ->sum('output_quantity');

        return $consumed;
    }

    /**
     * Get quantity tracking report for a TLC
     */
    public function getQuantityReport(string $tlc): array
    {
        $traceRecord = TraceRecord::where('tlc', $tlc)->first();

        if (!$traceRecord) {
            return [
                'tlc' => $tlc,
                'found' => false,
                'error' => "TLC '{$tlc}' not found",
            ];
        }

        $received = $this->getTotalReceivedQuantity($traceRecord);
        $shipped = $this->getTotalShippedQuantity($traceRecord);
        $consumed = $this->getTotalConsumedQuantity($traceRecord);
        $available = $this->getAvailableQuantity($traceRecord);

        return [
            'tlc' => $tlc,
            'found' => true,
            'unit' => $traceRecord->unit, // Changed from unit_of_measure to unit
            'received_quantity' => $received,
            'shipped_quantity' => $shipped,
            'consumed_quantity' => $consumed,
            'available_quantity' => $available,
            'consumption_percentage' => $received > 0 ? (($consumed / $received) * 100) : 0,
            'status' => $available > 0 ? 'AVAILABLE' : ($consumed > 0 ? 'FULLY_CONSUMED' : 'NO_ACTIVITY'),
        ];
    }

    /**
     * Validate quantity conservation across all events for a product
     */
    public function validateProductQuantityConservation(string $productCode): array
    {
        $traceRecords = TraceRecord::where('product_code', $productCode)->get();
        $violations = [];

        foreach ($traceRecords as $record) {
            $report = $this->getQuantityReport($record->tlc);
            
            if ($report['received_quantity'] > 0) {
                $totalOutflow = $report['shipped_quantity'] + $report['consumed_quantity'];
                
                if ($totalOutflow > $report['received_quantity']) {
                    $violations[] = [
                        'tlc' => $record->tlc,
                        'received' => $report['received_quantity'],
                        'outflow' => $totalOutflow,
                        'violation' => 'Outflow exceeds inflow',
                    ];
                }
            }
        }

        return [
            'product_code' => $productCode,
            'total_records' => count($traceRecords),
            'violations_found' => count($violations),
            'violations' => $violations,
            'conserved' => empty($violations),
        ];
    }
}
