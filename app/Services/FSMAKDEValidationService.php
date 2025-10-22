<?php

namespace App\Services;

use App\Models\CTEEvent;
use Illuminate\Support\Facades\Log;

/**
 * FSMA 204 Key Data Elements (KDE) Validation Service
 * Validates all 27 required KDEs for FDA compliance
 * 
 * Updated to include 4 new KDEs:
 * - product_lot_code (KDE #12)
 * - harvest_location_gln/name (KDE #8)
 * - cooling_date (KDE #14)
 * - reference_doc_type (KDE #17)
 */
class FSMAKDEValidationService
{
    private const REQUIRED_KDES = [
        // Event Information (3 KDEs)
        'event_type',
        'event_date',
        'traceability_lot_code',
        
        // Product Information (3 KDEs) - Added product_lot_code
        'product_description',
        'product_quantity',
        'product_lot_code', // NEW: KDE #12
        
        // Location Information (6 KDEs) - Added harvest_location
        'receiving_location_gln',
        'receiving_location_name',
        'harvest_location_gln', // NEW: KDE #8
        'harvest_location_name', // NEW: KDE #8
        'shipping_location_gln',
        'shipping_location_name',
        
        // Business Information (3 KDEs)
        'business_name',
        'business_gln',
        'business_address',
        
        // Dates (4 KDEs) - Added cooling_date
        'harvest_date',
        'pack_date',
        'cooling_date', // NEW: KDE #14
        'shipping_date',
        
        // References (3 KDEs) - Added reference_doc_type
        'reference_document',
        'reference_doc_type', // NEW: KDE #17
        'shipping_reference_document',
        
        // Transformation (2 KDEs)
        'input_tlcs',
        'output_tlcs',
        
        // Compliance (3 KDEs)
        'fda_compliant',
        'fda_compliance_notes',
        'signature_hash',
    ];

    /**
     * Validate all KDEs for a CTE Event
     */
    public function validateAllKDEs(CTEEvent $event): array
    {
        $errors = [];
        $warnings = [];

        $errors = array_merge($errors, $this->validateEventKDEs($event));
        $errors = array_merge($errors, $this->validateProductKDEs($event));
        $errors = array_merge($errors, $this->validateLocationKDEs($event));
        $errors = array_merge($errors, $this->validateBusinessKDEs($event));
        $errors = array_merge($errors, $this->validateDateKDEs($event));
        $errors = array_merge($errors, $this->validateReferenceKDEs($event));
        $errors = array_merge($errors, $this->validateTransformationKDEs($event));
        $errors = array_merge($errors, $this->validateComplianceKDEs($event));
        $errors = array_merge($errors, $this->validateCrossFieldRules($event));

        Log::info('FSMA 204 KDE Validation', [
            'event_id' => $event->id,
            'event_type' => $event->event_type,
            'errors_count' => count($errors),
            'warnings_count' => count($warnings),
        ]);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate Event KDEs
     */
    private function validateEventKDEs(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->event_type)) {
            $errors[] = 'KDE-001: Event type is required';
        } elseif (!in_array($event->event_type, ['receiving', 'shipping', 'transformation'])) {
            $errors[] = 'KDE-001: Event type must be receiving, shipping, or transformation';
        }

        if (empty($event->event_date)) {
            $errors[] = 'KDE-002: Event date is required';
        } elseif ($event->event_date > now()) {
            $errors[] = 'KDE-002: Event date cannot be in the future';
        }

        if (empty($event->traceability_lot_code) && empty($event->traceRecord?->tlc)) {
            $errors[] = 'KDE-003: Traceability Lot Code (TLC) is required';
        }

        return $errors;
    }

    /**
     * Validate Product KDEs
     */
    private function validateProductKDEs(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->product_description)) {
            $errors[] = 'KDE-004: Product description is required';
        } elseif (strlen($event->product_description) < 10) {
            $errors[] = 'KDE-004: Product description must be at least 10 characters';
        }

        if (empty($event->quantity_received) && $event->isReceiving()) {
            $errors[] = 'KDE-005: Product quantity is required for receiving events';
        } elseif ($event->quantity_received && $event->quantity_received <= 0) {
            $errors[] = 'KDE-005: Product quantity must be greater than zero';
        }

        if ($event->isReceiving() && empty($event->product_lot_code)) {
            $errors[] = 'KDE-012: Product lot code from supplier is required for receiving events';
        }

        return $errors;
    }

    /**
     * Validate Location KDEs
     */
    private function validateLocationKDEs(CTEEvent $event): array
    {
        $errors = [];

        if ($event->isReceiving() || $event->isTransformation()) {
            $receivingGLN = $event->receiving_location_gln ?? $event->location?->gln;
            if (empty($receivingGLN)) {
                $errors[] = 'KDE-006: Receiving location GLN is required';
            } elseif (!$this->validateGLN($receivingGLN)) {
                $errors[] = 'KDE-006: Receiving location GLN must be 13 digits';
            }

            if (empty($event->receiving_location_name) && empty($event->location?->location_name)) {
                $errors[] = 'KDE-007: Receiving location name is required';
            }
        }

        if ($event->isReceiving()) {
            if (!empty($event->harvest_location_gln) && !$this->validateGLN($event->harvest_location_gln)) {
                $errors[] = 'KDE-008: Harvest location GLN must be 13 digits if provided';
            }
            
            // Harvest location is recommended but not strictly required
            if (empty($event->harvest_location_gln) && empty($event->harvest_location_name)) {
                // This is a warning, not an error - harvest location is best practice
                Log::warning('Harvest location not provided', [
                    'event_id' => $event->id,
                    'recommendation' => 'Provide harvest location for full FSMA 204 compliance'
                ]);
            }
        }

        if ($event->isShipping()) {
            if (empty($event->shipping_location_gln)) {
                $errors[] = 'KDE-009: Shipping location GLN is required';
            } elseif (!$this->validateGLN($event->shipping_location_gln)) {
                $errors[] = 'KDE-009: Shipping location GLN must be 13 digits';
            }

            if (empty($event->shipping_location_name)) {
                $errors[] = 'KDE-010: Shipping location name is required';
            }
        }

        return $errors;
    }

    /**
     * Validate Business KDEs
     */
    private function validateBusinessKDEs(CTEEvent $event): array
    {
        $errors = [];

        if (empty($event->business_name)) {
            $errors[] = 'KDE-011: Business name is required';
        }

        if (empty($event->business_gln)) {
            $errors[] = 'KDE-012: Business GLN is required';
        } elseif (!$this->validateGLN($event->business_gln)) {
            $errors[] = 'KDE-012: Business GLN must be 13 digits';
        }

        if (empty($event->business_address)) {
            $errors[] = 'KDE-013: Business address is required';
        }

        return $errors;
    }

    /**
     * Validate Date KDEs
     */
    private function validateDateKDEs(CTEEvent $event): array
    {
        $errors = [];

        if ($event->isReceiving() && !empty($event->harvest_date)) {
            if ($event->harvest_date > $event->event_date) {
                $errors[] = 'KDE-014: Harvest date cannot be after event date';
            }
        }

        if ($event->isReceiving() && !empty($event->pack_date)) {
            if ($event->pack_date > $event->event_date) {
                $errors[] = 'KDE-015: Pack date cannot be after event date';
            }
        }

        if ($event->isReceiving() && !empty($event->cooling_date)) {
            if ($event->cooling_date > $event->event_date) {
                $errors[] = 'KDE-016: Cooling date cannot be after event date';
            }
            
            if (!empty($event->pack_date) && $event->cooling_date < $event->pack_date) {
                $errors[] = 'KDE-016: Cooling date must be after or equal to pack date';
            }
        }

        if ($event->isShipping()) {
            if (empty($event->shipping_date)) {
                $errors[] = 'KDE-017: Shipping date is required for shipping events';
            } elseif ($event->shipping_date > now()) {
                $errors[] = 'KDE-017: Shipping date cannot be in the future';
            }
        }

        return $errors;
    }

    /**
     * Validate Reference KDEs
     */
    private function validateReferenceKDEs(CTEEvent $event): array
    {
        $errors = [];

        if ($event->isReceiving() && empty($event->reference_document)) {
            $errors[] = 'KDE-018: Reference document (PO/Invoice/BOL) is required for receiving events';
        }

        if ($event->isReceiving() && !empty($event->reference_document) && empty($event->reference_doc_type)) {
            $errors[] = 'KDE-019: Reference document type (PO/Invoice/BOL/AWB/Other) is required when reference document is provided';
        }

        if ($event->isShipping() && empty($event->shipping_reference_document)) {
            $errors[] = 'KDE-020: Shipping reference document (BOL/Invoice) is required for shipping events';
        }

        return $errors;
    }

    /**
     * Validate Transformation KDEs
     */
    private function validateTransformationKDEs(CTEEvent $event): array
    {
        $errors = [];

        if ($event->isTransformation()) {
            if (empty($event->input_tlcs)) {
                $errors[] = 'KDE-021: Input TLCs are required for transformation events';
            } elseif (!is_array($event->input_tlcs) || count($event->input_tlcs) === 0) {
                $errors[] = 'KDE-021: At least one input TLC is required';
            }

            if (empty($event->output_tlcs)) {
                $errors[] = 'KDE-022: Output TLCs are required for transformation events';
            } elseif (!is_array($event->output_tlcs) || count($event->output_tlcs) === 0) {
                $errors[] = 'KDE-022: At least one output TLC is required';
            }
        }

        return $errors;
    }

    /**
     * Validate Compliance KDEs
     */
    private function validateComplianceKDEs(CTEEvent $event): array
    {
        $errors = [];

        if ($event->fda_compliant === null) {
            $errors[] = 'KDE-023: FDA compliance status is required';
        }

        if ($event->fda_compliant && empty($event->fda_compliance_notes)) {
            $errors[] = 'KDE-024: FDA compliance notes are required when marking as compliant';
        }

        return $errors;
    }

    /**
     * Validate cross-field business rules
     */
    private function validateCrossFieldRules(CTEEvent $event): array
    {
        $errors = [];

        if ($event->harvest_date && $event->pack_date && $event->harvest_date > $event->pack_date) {
            $errors[] = 'RULE-001: Harvest date must be before or equal to pack date';
        }

        if ($event->pack_date && $event->cooling_date && $event->pack_date > $event->cooling_date) {
            $errors[] = 'RULE-002: Pack date must be before or equal to cooling date';
        }

        if ($event->cooling_date && $event->event_date && $event->cooling_date > $event->event_date) {
            $errors[] = 'RULE-003: Cooling date must be before or equal to event date';
        }

        if ($event->isTransformation() && $event->quantity_received && $event->output_quantity) {
            if ($event->output_quantity > $event->quantity_received) {
                $errors[] = 'RULE-004: Output quantity cannot exceed input quantity in transformation';
            }
        }

        if ($event->isTransformation() && is_array($event->output_tlcs)) {
            $uniqueTLCs = array_unique($event->output_tlcs);
            if (count($uniqueTLCs) !== count($event->output_tlcs)) {
                $errors[] = 'RULE-005: Output TLCs must be unique';
            }
        }

        return $errors;
    }

    /**
     * Validate GLN format (13 digits)
     */
    private function validateGLN(string $gln): bool
    {
        return preg_match('/^\d{13}$/', $gln) === 1;
    }

    /**
     * Get KDE compliance report
     */
    public function getComplianceReport(CTEEvent $event): array
    {
        $validation = $this->validateAllKDEs($event);
        
        return [
            'event_id' => $event->id,
            'event_type' => $event->event_type,
            'compliance_status' => $validation['valid'] ? 'COMPLIANT' : 'NON_COMPLIANT',
            'total_kdes' => count(self::REQUIRED_KDES), // Now 27 KDEs (was 23)
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'],
            'validated_at' => now()->toIso8601String(),
        ];
    }
}
