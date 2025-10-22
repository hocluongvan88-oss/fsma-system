@extends('layouts.app')

@section('title', __('messages.receiving'))

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.record_receiving_event') }}</h2>
        
        <form method="POST" action="{{ route('cte.receiving') }}" id="receivingForm">
            @csrf
            
            <!-- Basic Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.basic_information') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.tlc_traceability_lot_code') }} *</label>
                    <input type="text" name="tlc" class="form-input" value="{{ old('tlc') }}" required placeholder="{{ __('messages.eg_rcv_2024_001') }}">
                    @error('tlc')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_lot_code') }} *
                        <span style="color: var(--danger); font-size: 0.75rem;">{{ __('messages.required_for_fda_compliance') }}</span>
                    </label>
                    <input type="text" name="product_lot_code" class="form-input" value="{{ old('product_lot_code') }}" required placeholder="{{ __('messages.eg_lot_2024_abc123') }}">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.original_product_lot_code_from_supplier') }}</small>
                    @error('product_lot_code')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.product') }} *</label>
                    <select name="product_id" id="productSelect" class="form-select" required>
                        <option value="">{{ __('messages.select_product') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->product_name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                    <!-- Added debug info to show product count -->
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                        {{ count($products) }} {{ __('messages.products_available') }}
                        @if(count($products) == 0)
                            <span style="color: var(--danger);">
                                - {{ __('messages.no_products_found') }}. {{ __('messages.please_add_products_in_master_data') }}
                            </span>
                        @endif
                    </small>
                    @error('product_id')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_description') }}</label>
                    <textarea name="product_description" class="form-textarea" rows="2" placeholder="{{ __('messages.detailed_product_description_fda') }}">{{ old('product_description') }}</textarea>
                </div>
            </div>

            <!-- Quantity Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.quantity_information') }}</h3>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.quantity_received') }} *</label>
                        <input type="number" name="quantity_received" class="form-input" value="{{ old('quantity_received') }}" step="0.01" min="0.01" required>
                        @error('quantity_received')
                            <small style="color: var(--danger);">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <!-- Fixed field name from quantity_unit to unit to match backend -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.unit') }} *</label>
                        <select name="unit" class="form-select" required>
                            <option value="">{{ __('messages.select_unit') }}</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="lb" {{ old('unit') == 'lb' ? 'selected' : '' }}>lb</option>
                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>{{ __('messages.box') }}</option>
                            <option value="case" {{ old('unit') == 'case' ? 'selected' : '' }}>{{ __('messages.case') }}</option>
                            <option value="pallet" {{ old('unit') == 'pallet' ? 'selected' : '' }}>{{ __('messages.pallet') }}</option>
                        </select>
                        @error('unit')
                            <small style="color: var(--danger);">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.receiving_location') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.receiving_location') }} *</label>
                    <!-- Added ID for JavaScript auto-population -->
                    <select name="location_id" id="locationSelect" class="form-select" required>
                        <option value="">{{ __('messages.select_location') }}</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" 
                                    data-gln="{{ $location->gln }}" 
                                    data-name="{{ $location->location_name }}"
                                    {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->location_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <!-- Added ID for auto-population and validation feedback -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.receiving_location_gln') }} 
                        <span style="color: var(--danger); font-size: 0.75rem;">{{ __('messages.required_for_fda_compliance') }}</span>
                    </label>
                    <input type="text" name="receiving_location_gln" id="locationGLN" class="form-input gln-input" value="{{ old('receiving_location_gln') }}" placeholder="{{ __('messages.global_location_number') }}" pattern="^\d{13}$" title="{{ __('messages.gln_must_be_13_digits') }}" maxlength="13">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.gln_13_digits') }}</small>
                    <small id="glnValidation" style="display: none; margin-top: 0.25rem;"></small>
                    @error('receiving_location_gln')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <!-- Added ID for auto-population -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.receiving_location_name') }}</label>
                    <input type="text" name="receiving_location_name" id="locationName" class="form-input" value="{{ old('receiving_location_name') }}" placeholder="{{ __('messages.full_location_name_fda') }}">
                </div>
                
                <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--info); margin-top: 1rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--info);">{{ __('messages.harvest_location_information') }}</h4>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.harvest_location_gln') }}
                            <span style="color: var(--text-secondary); font-size: 0.75rem;">({{ __('messages.optional_for_fresh_produce') }})</span>
                        </label>
                        <input type="text" name="harvest_location_gln" class="form-input gln-input" value="{{ old('harvest_location_gln') }}" placeholder="{{ __('messages.farm_or_harvest_location_gln') }}" pattern="^\d{13}$|^$" title="{{ __('messages.gln_must_be_13_digits') }}" maxlength="13">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.gln_13_digits_or_leave_blank') }}</small>
                        @error('harvest_location_gln')
                            <small style="color: var(--danger);">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.harvest_location_name') }}
                            <span style="color: var(--text-secondary); font-size: 0.75rem;">({{ __('messages.optional') }})</span>
                        </label>
                        <input type="text" name="harvest_location_name" class="form-input" value="{{ old('harvest_location_name') }}" placeholder="{{ __('messages.farm_name_or_harvest_location') }}">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.farm_or_harvest_location_name') }}</small>
                    </div>
                </div>
            </div>

            <!-- Business Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.supplier_information') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.supplier') }} *</label>
                    <!-- Added ID for JavaScript auto-population -->
                    <select name="partner_id" id="partnerSelect" class="form-select" required>
                        <option value="">{{ __('messages.select_supplier') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" 
                                    data-gln="{{ $supplier->gln }}" 
                                    data-name="{{ $supplier->partner_name }}"
                                    {{ old('partner_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->partner_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('partner_id')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <!-- Added ID for auto-population -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.business_name') }}</label>
                    <input type="text" name="business_name" id="businessName" class="form-input" value="{{ old('business_name') }}" placeholder="{{ __('messages.supplier_business_name') }}">
                </div>
                
                <!-- Added ID for auto-population and validation -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.business_gln') }}</label>
                    <input type="text" name="business_gln" id="businessGLN" class="form-input gln-input" value="{{ old('business_gln') }}" placeholder="{{ __('messages.global_location_number') }}" pattern="^\d{13}$" title="{{ __('messages.gln_must_be_13_digits') }}" maxlength="13">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.gln_13_digits') }}</small>
                    <small id="businessGlnValidation" style="display: none; margin-top: 0.25rem;"></small>
                    @error('business_gln')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.business_address') }}</label>
                    <textarea name="business_address" class="form-textarea" rows="2" placeholder="{{ __('messages.full_business_address') }}">{{ old('business_address') }}</textarea>
                </div>
            </div>

            <!-- Dates section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.dates') }}</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <!-- Added IDs for date validation -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.harvest_date') }}</label>
                        <input type="date" name="harvest_date" id="harvestDate" class="form-input" value="{{ old('harvest_date') }}">
                        @error('harvest_date')
                            <small style="color: var(--danger);">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.pack_date') }}</label>
                        <input type="date" name="pack_date" id="packDate" class="form-input" value="{{ old('pack_date') }}">
                        @error('pack_date')
                            <small style="color: var(--danger);">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.cooling_date') }}
                        <span style="color: var(--text-secondary); font-size: 0.75rem;">({{ __('messages.optional_for_fresh_produce') }})</span>
                    </label>
                    <input type="datetime-local" name="cooling_date" class="form-input" value="{{ old('cooling_date') }}" placeholder="{{ __('messages.date_time_product_was_cooled') }}">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.cooling_date_for_fresh_produce_compliance') }}</small>
                    @error('cooling_date')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.event_date') }} *</label>
                    <input type="datetime-local" name="event_date" id="eventDate" class="form-input" value="{{ old('event_date', now()->format('Y-m-d\TH:i')) }}" required>
                    @error('event_date')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <!-- Added date validation feedback -->
                <div id="dateValidation" style="display: none; padding: 0.75rem; border-radius: 0.375rem; margin-top: 0.5rem;"></div>
            </div>

            <!-- Reference & Compliance section -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.reference_compliance') }}</h3>
                
                <!-- Made reference_doc required with indicator -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.reference_document_po_invoice_bol') }} *
                        <span style="color: var(--danger); font-size: 0.75rem;">{{ __('messages.required_for_fda_compliance') }}</span>
                    </label>
                    <input type="text" name="reference_doc" class="form-input" value="{{ old('reference_doc') }}" placeholder="{{ __('messages.eg_po_12345') }}" required>
                    @error('reference_doc')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.reference_document_type') }} *
                        <span style="color: var(--danger); font-size: 0.75rem;">{{ __('messages.required_for_fda_compliance') }}</span>
                    </label>
                    <select name="reference_doc_type" class="form-select" required>
                        <option value="">{{ __('messages.select_document_type') }}</option>
                        <option value="PO" {{ old('reference_doc_type') == 'PO' ? 'selected' : '' }}>{{ __('messages.purchase_order') }} (PO)</option>
                        <option value="Invoice" {{ old('reference_doc_type') == 'Invoice' ? 'selected' : '' }}>{{ __('messages.invoice') }}</option>
                        <option value="BOL" {{ old('reference_doc_type') == 'BOL' ? 'selected' : '' }}>{{ __('messages.bill_of_lading') }} (BOL)</option>
                        <option value="AWB" {{ old('reference_doc_type') == 'AWB' ? 'selected' : '' }}>{{ __('messages.air_waybill') }} (AWB)</option>
                        <option value="Other" {{ old('reference_doc_type') == 'Other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                    </select>
                    @error('reference_doc_type')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.fda_compliance_notes') }}</label>
                    <textarea name="fda_compliance_notes" class="form-textarea" rows="2" placeholder="{{ __('messages.any_compliance_notes') }}">{{ old('fda_compliance_notes') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.notes') }}</label>
                    <textarea name="notes" class="form-textarea" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            
            <!-- Enhanced validation info box -->
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--info);">{{ __('messages.date_sequence_validation') }}</h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0;">
                    {{ __('messages.harvest_date') }} ≤ {{ __('messages.pack_date') }} ≤ {{ __('messages.event_date') }}
                </p>
            </div>
            
            <!-- Conditional E-Signature section - only show for Enterprise users -->
            @if(auth()->user()->hasFeature('e_signatures'))
                <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--warning-bg); border-radius: 0.5rem; border-left: 4px solid var(--warning);">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--warning); margin-bottom: 1rem; text-transform: uppercase;">
                        {{ __('messages.electronic_signature_optional') }}
                    </h3>
                    
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        {{ __('messages.add_signature_for_compliance') }}
                    </p>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.password_for_signature') }}</label>
                        <input type="password" name="signature_password" class="form-input" placeholder="{{ __('messages.enter_your_password') }}">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                            {{ __('messages.password_verification_required') }}
                        </small>
                        @error('signature_password')
                            <small style="color: var(--danger);">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.signature_reason') }}</label>
                        <input type="text" name="signature_reason" class="form-input" placeholder="{{ __('messages.eg_initial_receiving_record') }}">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                            {{ __('messages.optional_but_recommended') }}
                        </small>
                    </div>
                </div>
            @else
                <!-- Upgrade prompt for non-Enterprise users -->
                <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--info-bg); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--info); margin-bottom: 0.5rem; text-transform: uppercase;">
                        {{ __('messages.e_signatures_available_in_enterprise') }}
                    </h3>
                    
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        {{ __('messages.electronic_signature_is_premium_feature') }}
                    </p>
                    
                    <a href="{{ route('pricing') }}" class="btn btn-sm btn-primary" style="display: inline-block;">
                        {{ __('messages.upgrade_to_enterprise') }}
                    </a>
                </div>
            @endif
            
            <!-- Dynamic button text based on package -->
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;">
                @if(auth()->user()->hasFeature('e_signatures'))
                    {{ __('messages.record_receiving_with_signature') }}
                @else
                    {{ __('messages.record_receiving') }}
                @endif
            </button>
        </form>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.recent_receiving_events') }}</h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($recentEvents as $event)
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; {{ (isset($event->status) && $event->status === 'voided') || (isset($event->is_voided) && $event->is_voided) ? 'opacity: 0.6; border: 2px dashed var(--danger);' : '' }}">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong>{{ $event->traceRecord->tlc }}</strong>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge badge-success">{{ __('messages.receiving') }}</span>
                        {{-- Check status property instead of is_voided to avoid undefined property error --}}
                        @if(isset($event->status) && $event->status === 'voided')
                            <span class="badge badge-danger">{{ __('messages.voided') }}</span>
                        @endif
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    {{ $event->traceRecord->product->product_name }}<br>
                    {{ $event->traceRecord->quantity }} {{ $event->traceRecord->unit }}<br>
                    {{ __('messages.from') }}: {{ $event->partner->partner_name }}<br>
                    {{ $event->event_date->format('Y-m-d H:i') }}
                    
                    @if(isset($event->status) && $event->status === 'voided')
                        <br>
                        <span style="color: var(--danger); font-weight: 600;">
                            {{ __('messages.voided_by') }}: {{ $event->voidedBy->full_name ?? 'System' }}<br>
                            {{ __('messages.voided_at') }}: {{ $event->voided_at ? $event->voided_at->format('Y-m-d H:i') : 'N/A' }}
                        </span>
                    @endif
                </div>
                
                @if(!$event->is_voided && ($event->void_count ?? 0) < 1)
                    @php
                        $canVoid = false;
                        $voidReason = '';
                        
                        // Admin can always void
                        if(auth()->user()->role === 'Admin') {
                            $canVoid = true;
                            $voidReason = __('messages.admin_only');
                        }
                        // Within 2 hours for non-admin
                        elseif($event->created_at->diffInHours(now()) < 2) {
                            $canVoid = true;
                            $voidReason = __('messages.within_2_hours');
                        }
                    @endphp
                    
                    @if($canVoid)
                        <form method="POST" action="{{ route('cte.receiving.void', ['event' => $event->id]) }}" style="display: inline;">
                            @csrf
                            <button type="button" 
                                    class="btn btn-sm btn-danger void-event-btn" 
                                    data-event-id="{{ $event->id }}"
                                    data-event-type="receiving"
                                    data-tlc="{{ $event->traceRecord->tlc }}"
                                    style="margin-top: 0.5rem; font-size: 0.75rem;">
                                {{ __('messages.void_event') }}
                            </button>
                        </form>
                    @else
                        <small style="display: block; margin-top: 0.5rem; color: var(--text-muted); font-style: italic;">
                            {{ __('messages.void_time_restriction') }}
                        </small>
                    @endif
                @elseif(($event->void_count ?? 0) >= 1)
                    {{-- Show message when event has already been voided once --}}
                    <small style="display: block; margin-top: 0.5rem; color: var(--danger); font-weight: 600;">
                        {{ __('messages.event_already_voided_once') }}
                    </small>
                @endif
            </div>
            @empty
            <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                {{ __('messages.no_recent_receiving_events') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Added JavaScript for auto-population and validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Receiving form JavaScript initialized');
    
    const locationSelect = document.getElementById('locationSelect');
    const locationGLN = document.getElementById('locationGLN');
    const locationName = document.getElementById('locationName');
    const glnValidation = document.getElementById('glnValidation');
    
    const partnerSelect = document.getElementById('partnerSelect');
    const businessGLN = document.getElementById('businessGLN');
    const businessName = document.getElementById('businessName');
    const businessGlnValidation = document.getElementById('businessGlnValidation');
    
    const productSelect = document.getElementById('productSelect');
    
    console.log('[v0] Dropdown elements found:', {
        locationSelect: !!locationSelect,
        partnerSelect: !!partnerSelect,
        productSelect: !!productSelect,
        locationOptions: locationSelect ? locationSelect.options.length : 0,
        partnerOptions: partnerSelect ? partnerSelect.options.length : 0,
        productOptions: productSelect ? productSelect.options.length : 0
    });
    
    if (locationSelect) {
        console.log('[v0] Location select disabled:', locationSelect.disabled);
        console.log('[v0] Location select options:', Array.from(locationSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text
        })));
    }
    
    if (partnerSelect) {
        console.log('[v0] Partner select disabled:', partnerSelect.disabled);
        console.log('[v0] Partner select options:', Array.from(partnerSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text
        })));
    }
    
    if (productSelect) {
        console.log('[v0] Product select disabled:', productSelect.disabled);
        console.log('[v0] Product select options:', Array.from(productSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text
        })));
    }
    
    const harvestDate = document.getElementById('harvestDate');
    const packDate = document.getElementById('packDate');
    const eventDate = document.getElementById('eventDate');
    const dateValidation = document.getElementById('dateValidation');
    const submitBtn = document.getElementById('submitBtn');
    
    // Void functionality
    const voidModal = document.getElementById('voidModal');
    const voidForm = document.getElementById('voidForm');
    const voidEventType = document.getElementById('voidEventType');
    let currentEventId = null;
    let currentEventType = null;

    document.querySelectorAll('.void-event-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentEventId = this.dataset.eventId;
            currentEventType = this.dataset.eventType;
            const tlc = this.dataset.tlc;
            
            voidEventType.value = currentEventType;
            
            voidForm.action = `/cte/${currentEventType}/${currentEventId}/void`;
            
            voidModal.style.display = 'flex';
        });
    });

    function closeVoidModal() {
        voidModal.style.display = 'none';
        voidForm.reset();
        currentEventId = null;
        currentEventType = null;
    }

    voidModal.addEventListener('click', function(e) {
        if (e.target === voidModal) {
            closeVoidModal();
        }
    });

    voidForm.addEventListener('submit', function(e) {
        if (!confirm('{{ __("messages.void_confirmation") }}')) {
            e.preventDefault();
        }
    });

    // Auto-populate location fields
    if (locationSelect) {
        locationSelect.addEventListener('change', function() {
            console.log('[v0] Location changed:', this.value);
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                locationGLN.value = selectedOption.dataset.gln || '';
                locationName.value = selectedOption.dataset.name || '';
                validateGLN(locationGLN, glnValidation);
            } else {
                locationGLN.value = '';
                locationName.value = '';
            }
        });
    }
    
    // Auto-populate partner fields
    if (partnerSelect) {
        partnerSelect.addEventListener('change', function() {
            console.log('[v0] Partner changed:', this.value);
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                businessGLN.value = selectedOption.dataset.gln || '';
                businessName.value = selectedOption.dataset.name || '';
                validateGLN(businessGLN, businessGlnValidation);
            } else {
                businessGLN.value = '';
                businessName.value = '';
            }
        });
    }
    
    // Auto-populate product fields
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            console.log('[v0] Product changed:', this.value);
            // Additional logic for product auto-population can be added here if needed
        });
    }
    
    // GLN validation function
    function validateGLN(input, feedback) {
        const value = input.value.trim();
        if (value === '') {
            feedback.style.display = 'none';
            return true;
        }
        
        if (!/^\d{13}$/.test(value)) {
            feedback.textContent = '{{ __("messages.gln_must_be_13_digits") }}';
            feedback.style.color = 'var(--danger)';
            feedback.style.display = 'block';
            return false;
        } else {
            feedback.textContent = '✓ {{ __("messages.valid") }}';
            feedback.style.color = 'var(--success)';
            feedback.style.display = 'block';
            return true;
        }
    }
    
    // GLN input validation
    locationGLN.addEventListener('input', function() {
        validateGLN(this, glnValidation);
    });
    
    businessGLN.addEventListener('input', function() {
        validateGLN(this, businessGlnValidation);
    });
    
    // Date sequence validation
    function validateDateSequence() {
        const harvest = harvestDate.value;
        const pack = packDate.value;
        const event = eventDate.value ? eventDate.value.split('T')[0] : '';
        
        let isValid = true;
        let message = '';
        
        if (harvest && pack && harvest > pack) {
            isValid = false;
            message = '{{ __("messages.pack_date_after_harvest") }}';
        } else if (pack && event && pack > event) {
            isValid = false;
            message = '{{ __("messages.event_date_after_pack") }}';
        } else if (harvest && pack && event) {
            message = '✓ {{ __("messages.date_sequence_validation") }}: {{ __("messages.valid") }}';
        }
        
        if (message) {
            dateValidation.textContent = message;
            dateValidation.style.display = 'block';
            dateValidation.style.background = isValid ? 'var(--success-bg)' : 'var(--danger-bg)';
            dateValidation.style.color = isValid ? 'var(--success)' : 'var(--danger)';
            dateValidation.style.borderLeft = isValid ? '4px solid var(--success)' : '4px solid var(--danger)';
        } else {
            dateValidation.style.display = 'none';
        }
        
        return isValid;
    }
    
    harvestDate.addEventListener('change', validateDateSequence);
    packDate.addEventListener('change', validateDateSequence);
    eventDate.addEventListener('change', validateDateSequence);
    
    // Form submission validation
    document.getElementById('receivingForm').addEventListener('submit', function(e) {
        if (!validateDateSequence()) {
            e.preventDefault();
            alert('{{ __("messages.date_sequence_validation") }}: {{ __("messages.invalid") }}');
            return false;
        }
    });
});
</script>

<!-- Added void confirmation modal -->
<div id="voidModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 0.5rem; padding: 2rem; max-width: 500px; width: 90%;">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.void_event') }}</h3>
        
        <div style="padding: 1rem; background: var(--danger-bg); border-left: 4px solid var(--danger); border-radius: 0.375rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.875rem; color: var(--danger); margin: 0;">
                <strong>{{ __('messages.void_warning') }}</strong><br>
                {{ __('messages.signature_required_for_void') }}
            </p>
        </div>
        
        <form id="voidForm" method="POST">
            @csrf
            <input type="hidden" name="event_type" id="voidEventType">
            <input type="hidden" name="organization_id" value="{{ auth()->user()->organization_id }}">
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.void_reason') }} *</label>
                <select name="void_reason" class="form-select" required>
                    <option value="">{{ __('messages.select_type') }}</option>
                    <option value="data_entry_error">Data Entry Error</option>
                    <option value="duplicate_entry">Duplicate Entry</option>
                    <option value="incorrect_quantity">Incorrect Quantity</option>
                    <option value="wrong_product">Wrong Product</option>
                    <option value="wrong_date">Wrong Date</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.void_notes') }} *</label>
                <textarea name="void_notes" class="form-textarea" rows="3" required placeholder="{{ __('messages.describe_what_changed') }}"></textarea>
            </div>
            
            <!-- Added signature password field -->
            <div class="form-group" style="padding: 1rem; background: var(--warning-bg); border-radius: 0.375rem;">
                <label class="form-label" style="color: var(--warning);">{{ __('messages.password_for_signature') }} *</label>
                <input type="password" name="signature_password" class="form-input" required placeholder="{{ __('messages.enter_your_password') }}">
                <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                    {{ __('messages.signature_required_for_void') }}
                </small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeVoidModal()" style="flex: 1;">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    {{ __('messages.void_with_signature') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
