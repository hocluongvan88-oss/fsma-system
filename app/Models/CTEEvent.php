<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CTEEvent extends Model
{
    use HasFactory;

    protected $table = 'cte_events';

    protected $fillable = [
        'event_type',
        'status', // Added for VOID mechanism
        'trace_record_id',
        'event_date',
        'location_id',
        'partner_id',
        'input_tlcs',
        'reference_doc',
        'reference_doc_type', // Added KDE #17
        'notes',
        'created_by',
        'voided_by', // Added for audit trail
        'voided_at', // Added for audit trail
        'voids_event_id', // Added to link VOID events to original
        'void_count', // Added to track number of times event has been voided
        'product_description',
        'product_lot_code', // Added KDE #12
        'quantity_received',
        'quantity_unit',
        'receiving_location_gln',
        'receiving_location_name',
        'harvest_location_gln', // Added KDE #8
        'harvest_location_name', // Added KDE #8
        'shipping_location_gln',
        'shipping_location_name',
        'business_name',
        'business_gln',
        'business_address',
        'traceability_lot_code',
        'output_tlcs',
        'transformation_description',
        'shipping_reference_doc',
        'shipping_date',
        'receiving_date_expected',
        'fda_compliant',
        'fda_compliance_notes',
        'output_quantity',
        'harvest_date',
        'pack_date',
        'cooling_date', // Added KDE #14
        'signature_id', // Added signature_id to fillable
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'voided_at' => 'datetime', // Added
            'input_tlcs' => 'array',
            'output_tlcs' => 'array',
            'quantity_received' => 'decimal:2',
            'output_quantity' => 'decimal:2',
            'shipping_date' => 'datetime',
            'harvest_date' => 'datetime',
            'pack_date' => 'datetime',
            'cooling_date' => 'datetime', // Added cast for cooling_date
            'fda_compliant' => 'boolean',
        ];
    }

    // Relationships
    public function traceRecord()
    {
        return $this->belongsTo(TraceRecord::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function voidsEvent()
    {
        return $this->belongsTo(CTEEvent::class, 'voids_event_id');
    }

    public function voidedByEvents()
    {
        return $this->hasMany(CTEEvent::class, 'voids_event_id');
    }

    public function transformationItems()
    {
        return $this->hasMany(TransformationItem::class, 'transformation_event_id');
    }

    public function signature()
    {
        return $this->belongsTo(ESignature::class, 'signature_id');
    }

    // Scopes
    public function scopeReceiving($query)
    {
        return $query->where('event_type', 'receiving');
    }

    public function scopeTransformation($query)
    {
        return $query->where('event_type', 'transformation');
    }

    public function scopeShipping($query)
    {
        return $query->where('event_type', 'shipping');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    public function scopeVoidEvents($query)
    {
        return $query->where('event_type', 'VOID');
    }

    // Helper methods
    public function isReceiving(): bool
    {
        return $this->event_type === 'receiving';
    }

    public function isTransformation(): bool
    {
        return $this->event_type === 'transformation';
    }

    public function isShipping(): bool
    {
        return $this->event_type === 'shipping';
    }

    public function hasInputs(): bool
    {
        return !empty($this->input_tlcs);
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function isVoidEvent(): bool
    {
        return $this->event_type === 'VOID';
    }

    public function canBeVoided(): bool
    {
        // Can only void active events
        if ($this->status !== 'active') {
            return false;
        }
        
        // Check if user is admin or within 2 hours
        $user = auth()->user();
        return $user->hasRole('admin') || 
               $this->created_at->diffInHours(now()) <= 2;
    }

    /**
     * Get all KDEs (Key Data Elements) for FDA export
     * Returns array of all required FSMA 204 KDEs
     */
    public function getKDEs(): array
    {
        return [
            // Event Information KDEs
            'event_type' => $this->event_type,
            'event_date' => $this->event_date?->toIso8601String(),
            
            // Product KDEs
            'traceability_lot_code' => $this->traceability_lot_code ?? $this->traceRecord?->tlc,
            'product_description' => $this->product_description,
            'product_lot_code' => $this->product_lot_code, // Now using dedicated column (KDE #12)
            
            // Quantity KDEs
            'quantity_received' => $this->quantity_received,
            'output_quantity' => $this->output_quantity,  // Added KDE #15 (yield)
            'quantity_unit' => $this->quantity_unit,
            
            // Location KDEs
            'receiving_location_gln' => $this->receiving_location_gln ?? $this->location?->gln,
            'receiving_location_name' => $this->receiving_location_name ?? $this->location?->location_name,
            'harvest_location_gln' => $this->harvest_location_gln, // Added KDE #8
            'harvest_location_name' => $this->harvest_location_name, // Added KDE #8
            'shipping_location_gln' => $this->shipping_location_gln,
            'shipping_location_name' => $this->shipping_location_name,
            
            // Business KDEs
            'business_name' => $this->business_name,
            'business_gln' => $this->business_gln,
            'business_address' => $this->business_address,
            
            // Traceability KDEs
            'input_tlcs' => $this->input_tlcs,
            'output_tlcs' => $this->output_tlcs,
            
            // Transformation KDEs
            'transformation_description' => $this->transformation_description,
            'transformation_yield_percentage' => $this->output_quantity && $this->input_tlcs ? 
                (($this->output_quantity / collect($this->input_tlcs)->count()) * 100) : null,  // Added KDE #15
            
            // Date KDEs
            'harvest_date' => $this->harvest_date?->toIso8601String(),
            'pack_date' => $this->pack_date?->toIso8601String(),
            'cooling_date' => $this->cooling_date?->toIso8601String(), // Added KDE #14
            'shipping_date' => $this->shipping_date?->toIso8601String(),
            'receiving_date_expected' => $this->receiving_date_expected,
            
            // Reference Document KDEs
            'reference_doc' => $this->reference_doc,
            'reference_doc_type' => $this->reference_doc_type, // Added KDE #17
            'shipping_reference_doc' => $this->shipping_reference_doc,
            
            // Compliance KDEs
            'fda_compliant' => $this->fda_compliant,
            'fda_compliance_notes' => $this->fda_compliance_notes,  // Added KDE #27
            
            // Audit KDEs
            'created_by_user' => $this->creator?->name,  // Added KDE #18 (authorization)
            'created_at' => $this->created_at?->toIso8601String(),  // Added KDE #22 (data integrity)
            'voided_by_user' => $this->voidedBy?->name,  // Added KDE for audit trail
            'voided_at' => $this->voided_at?->toIso8601String(),  // Added KDE for audit trail
            'void_count' => $this->void_count, // Added KDE for tracking void count
            'signature_user' => $this->signature?->user_name, // Added KDE for signature
            'signature_date' => $this->signature?->signed_at?->toIso8601String(), // Added KDE for signature
        ];
    }

    /**
     * Validate KDEs for FDA compliance using the validation service
     */
    public function validateFDACompliance(): array
    {
        $validationService = app(\App\Services\FSMAKDEValidationService::class);
        return $validationService->validateAllKDEs($this);
    }

    /**
     * Get comprehensive compliance report
     */
    public function getComplianceReport(): array
    {
        $validationService = app(\App\Services\FSMAKDEValidationService::class);
        return $validationService->getComplianceReport($this);
    }

    /**
     * Get is_voided attribute
     * Added accessor to support both model instances and stdClass from queries
     */
    public function getIsVoidedAttribute(): bool
    {
        return $this->status === 'voided';
    }
}
