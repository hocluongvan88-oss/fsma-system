@extends('layouts.app')

@section('title', __('messages.shipping'))

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.record_shipping_event') }}</h2>
        
        <form method="POST" action="{{ route('cte.shipping') }}" id="shippingForm">
            @csrf
            
            <!-- Translated TLC Selection section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.select_items_to_ship') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_lot_code') }} *
                        <span style="color: var(--danger); font-size: 0.75rem;">{{ __('messages.required_for_fda_compliance') }}</span>
                    </label>
                    <input type="text" name="product_lot_code" class="form-input" value="{{ old('product_lot_code') }}" required placeholder="{{ __('messages.eg_lot_2024_abc123') }}">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.product_lot_code_being_shipped') }}</small>
                    @error('product_lot_code')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.5rem;">
                    @foreach($activeTLCs as $tlc)
                    <div style="padding: 0.75rem; border-radius: 0.375rem; border: 1px solid var(--border-color); margin-bottom: 0.5rem;" class="tlc-ship-item" data-tlc-id="{{ $tlc->id }}">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" 
                                   name="trace_record_ids[]" 
                                   value="{{ $tlc->id }}" 
                                   class="tlc-checkbox"
                                   data-available="{{ $tlc->available_quantity }}"
                                   data-unit="{{ $tlc->unit }}"
                                   {{ in_array($tlc->id, old('trace_record_ids', [])) ? 'checked' : '' }}>
                            <span style="flex: 1;">
                                <strong>{{ $tlc->tlc }}</strong>
                                @if($tlc->available_quantity < $tlc->quantity)
                                    <span class="badge badge-info" style="font-size: 0.7rem;">{{ __('messages.partially_consumed') }}</span>
                                @endif
                                <br>
                                <span style="font-size: 0.875rem; color: var(--text-secondary);">
                                    {{ $tlc->product->product_name }}<br>
                                    <span style="color: var(--success); font-weight: 600;">
                                        {{ __('messages.available') }}: {{ $tlc->available_quantity }} {{ $tlc->unit }}
                                    </span>
                                    @ {{ $tlc->location->location_name }}
                                </span>
                            </span>
                        </label>
                        
                        <!-- Added quantity input field that appears when checkbox is checked -->
                        <div class="quantity-input-container" style="display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
                            <label class="form-label" style="font-size: 0.875rem;">{{ __('messages.quantity_to_ship') }} *</label>
                            <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.5rem; align-items: center;">
                                <input type="number" 
                                       name="quantities_shipped[{{ $tlc->id }}]" 
                                       class="form-input quantity-input" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="{{ $tlc->available_quantity }}"
                                       value="{{ old('quantities_shipped.' . $tlc->id, $tlc->available_quantity) }}"
                                       placeholder="0.00"
                                       disabled>
                                <span class="unit-label" style="color: var(--text-secondary); font-weight: 600;">{{ $tlc->unit }}</span>
                            </div>
                            <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                                {{ __('messages.max') }}: {{ $tlc->available_quantity }} {{ $tlc->unit }}
                            </small>
                            <small class="quantity-validation" style="display: none; margin-top: 0.25rem;"></small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Translated Shipping From Location section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.shipping_from_location') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.shipping_from_location') }} *</label>
                    <select name="location_id" class="form-select" required>
                        <option value="">{{ __('messages.select_location') }}</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->location_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Translated Shipping To Location section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.shipping_to_location') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.ship_to_customer') }} *</label>
                    <select name="partner_id" class="form-select" required>
                        <option value="">{{ __('messages.select_customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('partner_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->partner_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.shipping_location_gln') }}</label>
                    <input type="text" name="shipping_location_gln" class="form-input gln-input" value="{{ old('shipping_location_gln') }}" placeholder="{{ __('messages.global_location_number') }}" pattern="^\d{13}$" title="{{ __('messages.gln_must_be_13_digits') }}">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.gln_13_digits') }}</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.shipping_location_name') }}</label>
                    <input type="text" name="shipping_location_name" class="form-input" value="{{ old('shipping_location_name') }}" placeholder="{{ __('messages.full_destination_location_name') }}">
                </div>
            </div>

            <!-- Translated Dates section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.dates') }}</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.shipping_date') }} *</label>
                        <input type="datetime-local" name="event_date" class="form-input" value="{{ old('event_date', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.expected_receiving_date') }}</label>
                        <input type="date" name="receiving_date_expected" class="form-input" value="{{ old('receiving_date_expected') }}">
                    </div>
                </div>
            </div>

            <!-- Translated Reference & Compliance section -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.reference_compliance') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.shipping_reference_document_bol_invoice') }} *</label>
                    <input type="text" name="shipping_reference_doc" class="form-input" value="{{ old('shipping_reference_doc') }}" placeholder="{{ __('messages.eg_bol_12345') }}" required>
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.required_for_fda_compliance') }}</small>
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
            
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;">{{ __('messages.record_shipping') }}</button>
        </form>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.recent_shipping_events') }}</h2>
        
        <!-- CHANGE: Added total shipped quantity display -->
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_shipped_quantity') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);" id="totalShippedQuantity">0</div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($recentEvents as $event)
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; {{ $event->is_voided ? 'opacity: 0.6; border: 2px dashed var(--danger);' : '' }}">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong>{{ $event->traceRecord?->tlc ?? 'N/A' }}</strong>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge badge-warning">{{ __('messages.shipping') }}</span>
                        @if($event->is_voided)
                            <span class="badge badge-danger">{{ __('messages.voided') }}</span>
                        @endif
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    {{ $event->traceRecord?->product?->product_name ?? __('messages.product_deleted') }}<br>
                    <!-- CHANGE: Display quantity_shipped instead of quantity_received -->
                    {{ $event->quantity_shipped ?? $event->traceRecord?->quantity ?? 'N/A' }} {{ $event->traceRecord?->unit ?? '' }}<br>
                    {{ __('messages.to') }}: {{ $event->partner?->partner_name ?? 'N/A' }}<br>
                    {{ $event->event_date->format('Y-m-d H:i') }}
                    
                    @if($event->is_voided)
                        <br>
                        <span style="color: var(--danger); font-weight: 600;">
                            {{ __('messages.voided_by') }}: {{ $event->voidedBy?->full_name ?? 'System' }}<br>
                            {{ __('messages.voided_at') }}: {{ $event->voided_at ? $event->voided_at->format('Y-m-d H:i') : 'N/A' }}
                        </span>
                    @endif
                </div>
                
                @if(!$event->is_voided && ($event->void_count ?? 0) < 1)
                    @php
                        $canVoid = false;
                        $voidReason = '';
                        
                        if(auth()->user()->role === 'Admin') {
                            $canVoid = true;
                            $voidReason = __('messages.admin_only');
                        }
                        elseif($event->created_at->diffInHours(now()) < 2) {
                            $canVoid = true;
                            $voidReason = __('messages.within_2_hours');
                        }
                    @endphp
                    
                    @if($canVoid)
                        <button type="button" 
                                class="btn btn-sm btn-danger void-event-btn" 
                                data-event-id="{{ $event->id }}"
                                data-event-type="shipping"
                                data-tlc="{{ $event->traceRecord?->tlc ?? 'N/A' }}"
                                style="margin-top: 0.5rem; font-size: 0.75rem;">
                            {{ __('messages.void_event') }}
                        </button>
                    @else
                        <small style="display: block; margin-top: 0.5rem; color: var(--text-muted); font-style: italic;">
                            {{ __('messages.void_time_restriction') }}
                        </small>
                    @endif
                @elseif(($event->void_count ?? 0) >= 1)
                    <small style="display: block; margin-top: 0.5rem; color: var(--danger); font-weight: 600;">
                        {{ __('messages.event_already_voided_once') }}
                    </small>
                @endif
            </div>
            @empty
            <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                {{ __('messages.no_recent_shipping_events') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Added void confirmation modal --}}
<div id="voidModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 0.5rem; padding: 2rem; max-width: 500px; width: 90%;">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.void_event') }}</h3>
        
        <div style="padding: 1rem; background: var(--danger-bg); border-left: 4px solid var(--danger); border-radius: 0.375rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.875rem; color: var(--danger); margin: 0;">
                <strong>{{ __('messages.void_warning') }}</strong>
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
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeVoidModal()" style="flex: 1;">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    {{ __('messages.void_event') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.tlc-ship-item:has(input.tlc-checkbox:checked) {
    background: rgba(59, 130, 246, 0.1);
    border-color: var(--accent-primary);
}

.gln-input:invalid {
    border-color: var(--danger);
}

.gln-input:valid {
    border-color: var(--success);
}

.quantity-input:invalid {
    border-color: var(--danger);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const partnerSelect = document.querySelector('select[name="partner_id"]');
    const shippingGLN = document.querySelector('input[name="shipping_location_gln"]');
    const shippingLocationName = document.querySelector('input[name="shipping_location_name"]');
    const shippingDate = document.querySelector('input[name="event_date"]');
    const receivingDate = document.querySelector('input[name="receiving_date_expected"]');
    const submitBtn = document.getElementById('submitBtn');
    const totalShippedQuantityEl = document.getElementById('totalShippedQuantity');
    
    const tlcCheckboxes = document.querySelectorAll('.tlc-checkbox');
    
    tlcCheckboxes.forEach(checkbox => {
        const container = checkbox.closest('.tlc-ship-item');
        const quantityContainer = container.querySelector('.quantity-input-container');
        const quantityInput = container.querySelector('.quantity-input');
        const quantityValidation = container.querySelector('.quantity-validation');
        const maxQuantity = parseFloat(checkbox.dataset.available);
        const unit = checkbox.dataset.unit;
        
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                quantityContainer.style.display = 'block';
                quantityInput.disabled = false;
                quantityInput.required = true;
                quantityInput.value = maxQuantity; // Default to full available quantity
                validateQuantity();
            } else {
                quantityContainer.style.display = 'none';
                quantityInput.disabled = true;
                quantityInput.required = false;
                quantityInput.value = '';
            }
            validateForm();
            updateTotalShippedQuantity();
        });
        
        quantityInput.addEventListener('input', function() {
            validateQuantity();
            validateForm();
            updateTotalShippedQuantity();
        });
        
        function validateQuantity() {
            const value = parseFloat(quantityInput.value) || 0;
            
            if (value <= 0) {
                quantityValidation.textContent = '{{ __("messages.quantity_must_be_greater_than_zero") }}';
                quantityValidation.style.color = 'var(--danger)';
                quantityValidation.style.display = 'block';
                quantityInput.style.borderColor = 'var(--danger)';
                return false;
            } else if (value > maxQuantity) {
                quantityValidation.textContent = `{{ __("messages.exceeds_available") }}: ${maxQuantity} ${unit}`;
                quantityValidation.style.color = 'var(--danger)';
                quantityValidation.style.display = 'block';
                quantityInput.style.borderColor = 'var(--danger)';
                return false;
            } else {
                quantityValidation.textContent = `✓ {{ __("messages.valid") }}`;
                quantityValidation.style.color = 'var(--success)';
                quantityValidation.style.display = 'block';
                quantityInput.style.borderColor = 'var(--success)';
                return true;
            }
        }
        
        // Initialize state if checkbox is already checked (from old input)
        if (checkbox.checked) {
            quantityContainer.style.display = 'block';
            quantityInput.disabled = false;
            quantityInput.required = true;
            validateQuantity();
        }
    });
    
    // CHANGE: Calculate total shipped quantity from selected items
    function updateTotalShippedQuantity() {
        let totalShipped = 0;
        let selectedUnit = '';
        
        tlcCheckboxes.forEach(checkbox => {
            if (checkbox.checked && !checkbox.disabled) {
                const container = checkbox.closest('.tlc-ship-item');
                const quantityInput = container.querySelector('.quantity-input');
                const quantity = parseFloat(quantityInput.value) || 0;
                const unit = checkbox.dataset.unit;
                
                totalShipped += quantity;
                if (!selectedUnit && unit) {
                    selectedUnit = unit;
                }
            }
        });
        
        totalShippedQuantityEl.textContent = totalShipped.toFixed(2) + (selectedUnit ? ' ' + selectedUnit : '');
    }
    
    function validateForm() {
        const checkedBoxes = document.querySelectorAll('.tlc-checkbox:checked');
        let allValid = checkedBoxes.length > 0;
        
        checkedBoxes.forEach(checkbox => {
            const container = checkbox.closest('.tlc-ship-item');
            const quantityInput = container.querySelector('.quantity-input');
            const value = parseFloat(quantityInput.value) || 0;
            const maxQuantity = parseFloat(checkbox.dataset.available);
            
            if (value <= 0 || value > maxQuantity) {
                allValid = false;
            }
        });
        
        submitBtn.disabled = !allValid;
    }
    
    // Auto-populate customer/partner fields when selected
    partnerSelect.addEventListener('change', async function() {
        const partnerId = this.value;
        
        if (!partnerId) {
            shippingGLN.value = '';
            shippingLocationName.value = '';
            return;
        }
        
        try {
            const response = await fetch(`/api/master-data/partners/${partnerId}`);
            if (response.ok) {
                const data = await response.json();
                shippingGLN.value = data.gln || '';
                shippingLocationName.value = data.partner_name || '';
                validateGLN(shippingGLN);
            }
        } catch (error) {
            console.error('Error fetching partner data:', error);
        }
    });
    
    // GLN validation function
    function validateGLN(input) {
        const value = input.value.trim();
        if (value === '') {
            input.style.borderColor = '';
            return true;
        }
        
        if (!/^\d{13}$/.test(value)) {
            input.style.borderColor = 'var(--danger)';
            return false;
        } else {
            input.style.borderColor = 'var(--success)';
            return true;
        }
    }
    
    // Real-time GLN validation
    shippingGLN.addEventListener('input', function() {
        validateGLN(this);
    });
    
    // Validate expected receiving date is after shipping date
    function validateDates() {
        if (!shippingDate.value || !receivingDate.value) return true;
        
        const shipDate = new Date(shippingDate.value);
        const recvDate = new Date(receivingDate.value);
        
        if (recvDate < shipDate) {
            receivingDate.setCustomValidity('{{ __("messages.receiving_date_must_be_after_shipping") }}');
            return false;
        } else {
            receivingDate.setCustomValidity('');
            return true;
        }
    }
    
    shippingDate.addEventListener('change', validateDates);
    receivingDate.addEventListener('change', validateDates);
    
    // Initial validation
    validateForm();
    updateTotalShippedQuantity();
    
    // Void functionality
    const voidModal = document.getElementById('voidModal');
    const voidForm = document.getElementById('voidForm');
    const voidEventType = document.getElementById('voidEventType');
    let currentEventId = null;

    document.querySelectorAll('.void-event-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentEventId = this.dataset.eventId;
            const eventType = this.dataset.eventType;
            const tlc = this.dataset.tlc;
            
            voidEventType.value = eventType;
            voidForm.action = `/cte/${eventType}/${currentEventId}/void`;
            
            voidModal.style.display = 'flex';
        });
    });

    function closeVoidModal() {
        voidModal.style.display = 'none';
        voidForm.reset();
        currentEventId = null;
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
});
</script>
@endpush

@endsection
