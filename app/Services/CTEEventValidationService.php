<?php

namespace App\Services;

use App\Models\CTEEvent;
use Illuminate\Support\Facades\Log;

/**
 * CTE Event Validation Service
 * Comprehensive validation for CTE events including:
 * - Date sequence validation
 * - GLN format validation
 * - Lot code uniqueness
 * - Event type-specific rules
 */
class CTEEventValidationService
{
    protected FSMAKDEValidationService $kdeValidationService;
    protected QuantityTrackingService $quantityTrackingService;

    public function __construct(
        FSMAKDEValidationService $kdeValidationService,
        QuantityTrackingService $quantityTrackingService
    ) {
        $this->kdeValidationService = $kdeValidationService;
        $this->quantityTrackingService = $quantityTrackingService;
    }

    /**
     * Validate CTE event comprehensively
     */
    public function validate(CTEEvent $event): array
    {
        $errors = [];
        $warnings = [];

        // Validate KDEs
        $kdeValidation = $this->kdeValidationService->validateAllKDEs($event);
        $errors = array_merge($errors, $kdeValidation['errors']);
        $warnings = array_merge($warnings, $kdeValidation['warnings']);

        // Validate date sequences
        $errors = array_merge($errors, $this->validateDateSequence($event));

        // Validate GLN formats
        $errors = array_merge($errors, $this->validateGLNFormats($event));

        // Validate lot code uniqueness
        $errors = array_merge($errors, $this->validateLotCodeUniqueness($event));

        // Validate quantity conservation
        $quantityValidation = $this->quantityTrackingService->trackQuantity($event);
        $errors = array_merge($errors, $quantityValidation['errors']);

        // Validate event-specific rules
        $errors = array_merge($errors, $this->validateEventTypeRules($event));

        Log::info('CTE Event Validation Complete', [
            'event_id' => $event->id,
            'event_type' => $event->event_type,
            'errors_count' => count($errors),
            'warnings_count' => count($warnings),
            'valid' => empty($errors),
        ]);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate date sequence rules
     */
    private function validateDateSequence(CTEEvent $event): array
    {
        $errors = [];

        // Harvest date must be before pack date
        if ($event->harvest_date && $event->pack_date) {
            if ($event->harvest_date > $event->pack_date) {
                $errors[] = 'Harvest date must be before or equal to pack date';
            }
        }

        // Pack date must be before event date
        if ($event->pack_date && $event->event_date) {
            if ($event->pack_date > $event->event_date) {
                $errors[] = 'Pack date must be before or equal to event date';
            }
        }

        // Event date must be before shipping date
        if ($event->event_date && $event->shipping_date) {
            if ($event->event_date > $event->shipping_date) {
                $errors[] = 'Event date must be before shipping date';
            }
        }

        // Check against previous events for this TLC
        $previousEvent = $this->getPreviousEventForTLC($event);
        if ($previousEvent && $event->event_date < $previousEvent->event_date) {
            $errors[] = sprintf(
                'Event date (%s) cannot be before previous event date (%s) for this TLC',
                $event->event_date->format('Y-m-d'),
                $previousEvent->event_date->format('Y-m-d')
            );
        }

        return $errors;
    }

    /**
     * Validate GLN format (13 digits)
     */
    private function validateGLNFormats(CTEEvent $event): array
    {
        $errors = [];

        $glnFields = [
            'receiving_location_gln' => 'Receiving location GLN',
            'shipping_location_gln' => 'Shipping location GLN',
            'business_gln' => 'Business GLN',
        ];

        foreach ($glnFields as $field => $label) {
            $value = $event->$field;
            if (!empty($value) && !$this->isValidGLN($value)) {
                $errors[] = "{$label} must be exactly 13 digits (provided: {$value})";
            }
        }

        return $errors;
    }

    /**
     * Validate lot code uniqueness per organization
     */
    private function validateLotCodeUniqueness(CTEEvent $event): array
    {
        $errors = [];

        $tlc = $event->traceability_lot_code ?? $event->traceRecord?->tlc;
        if (empty($tlc)) {
            return $errors;
        }

        // Check if this TLC already exists for the same organization
        $query = CTEEvent::where('traceability_lot_code', $tlc)
            ->where('id', '!=', $event->id);

        // Filter by organization_id if user is authenticated
        if (auth()->check() && auth()->user()->organization_id) {
            $query->whereHas('creator', function ($q) {
                $q->where('organization_id', auth()->user()->organization_id);
            });
        }

        $existingEvent = $query->first();

        if ($existingEvent && $event->event_type === 'receiving') {
            $errors[] = "Traceability Lot Code '{$tlc}' already exists for this organization";
        }

        return $errors;
    }

    /**
     * Validate event type-specific rules
     */
    private function validateEventTypeRules(CTEEvent $event): array
    {
        $errors = [];

        if ($event->isReceiving()) {
            $errors = array_merge($errors, $this->validateReceivingEvent($event));
        } elseif ($event->isShipping()) {
            $errors = array_merge($errors, $this->validateShippingEvent($event));
        } elseif ($event->isTransformation()) {
            $errors = array_merge($errors, $this->validateTransformationEvent($event));
        }

        return $errors;
    }

    /**
     * Validate receiving event specific rules
     */
    private function validateReceivingEvent(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->quantity_received)) {
            $errors[] = 'Quantity received is required for receiving events';
        }

        if (empty($event->quantity_unit)) {
            $errors[] = 'Unit of measure is required for receiving events';
        }

        if (empty($event->reference_doc)) {
            $errors[] = 'Reference document (PO/Invoice/BOL) is required for receiving events';
        }

        return $errors;
    }

    /**
     * Validate shipping event specific rules
     */
    private function validateShippingEvent(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->shipping_date)) {
            $errors[] = 'Shipping date is required for shipping events';
        }

        if (empty($event->shipping_reference_doc)) {
            $errors[] = 'Shipping reference document (BOL/Invoice) is required for shipping events';
        }

        // Verify TLC exists and has available quantity
        $tlc = $event->traceability_lot_code ?? $event->traceRecord?->tlc;
        if (!empty($tlc)) {
            $traceRecord = \App\Models\TraceRecord::where('tlc', $tlc)->first();
            if (!$traceRecord) {
                $errors[] = "TLC '{$tlc}' not found in system";
            }
        }

        return $errors;
    }

    /**
     * Validate transformation event specific rules
     */
    private function validateTransformationEvent(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->input_tlcs) || !is_array($event->input_tlcs)) {
            $errors[] = 'Input TLCs are required for transformation events';
        } else {
            // Validate each input TLC exists
            foreach ($event->input_tlcs as $tlc) {
                $traceRecord = \App\Models\TraceRecord::where('tlc', $tlc)->first();
                if (!$traceRecord) {
                    $errors[] = "Input TLC '{$tlc}' not found in system";
                }
            }
        }

        if (empty($event->output_tlcs) || !is_array($event->output_tlcs)) {
            $errors[] = 'Output TLCs are required for transformation events';
        } else {
            // Validate output TLCs are unique
            $uniqueTLCs = array_unique($event->output_tlcs);
            if (count($uniqueTLCs) !== count($event->output_tlcs)) {
                $errors[] = 'Output TLCs must be unique';
            }
        }

        if (empty($event->transformation_description)) {
            $errors[] = 'Transformation description is required';
        }

        return $errors;
    }

    /**
     * Get previous event for a TLC
     */
    private function getPreviousEventForTLC(CTEEvent $event): ?CTEEvent
    {
        $tlc = $event->traceability_lot_code ?? $event->traceRecord?->tlc;
        
        if (empty($tlc)) {
            return null;
        }

        return CTEEvent::where('traceability_lot_code', $tlc)
            ->where('id', '!=', $event->id)
            ->where('event_date', '<', $event->event_date)
            ->orderBy('event_date', 'desc')
            ->first();
    }

    /**
     * Validate GLN format (13 digits)
     */
    private function isValidGLN(string $gln): bool
    {
        return preg_match('/^\d{13}$/', $gln) === 1;
    }

    /**
     * Get comprehensive validation report
     */
    public function getValidationReport(CTEEvent $event): array
    {
        $validation = $this->validate($event);

        return [
            'event_id' => $event->id,
            'event_type' => $event->event_type,
            'validation_status' => $validation['valid'] ? 'VALID' : 'INVALID',
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'],
            'error_count' => count($validation['errors']),
            'warning_count' => count($validation['warnings']),
            'validated_at' => now()->toIso8601String(),
        ];
    }
}
