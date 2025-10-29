@extends('layouts.app')

@section('title', __('messages.transformation'))

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.record_transformation_event') }}</h2>
        
        <form method="POST" action="{{ route('cte.transformation') }}" id="transformationForm">
            @csrf
            
            <!-- Translated Output Product Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.output_product') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.output_tlc') }} *</label>
                    {{-- Make TLC read-only and auto-generated --}}
                    <input type="text" name="output_tlc" class="form-input" value="{{ $generatedTLC ?? old('output_tlc') }}" readonly style="background-color: var(--bg-tertiary); cursor: not-allowed;">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.tlc_auto_generated_system') }}</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.product_lot_code') }} *
                        <span style="color: var(--danger); font-size: 0.75rem;">{{ __('messages.required_for_fda_compliance') }}</span>
                    </label>
                    <input type="text" name="product_lot_code" class="form-input" value="{{ old('product_lot_code') }}" required placeholder="{{ __('messages.eg_lot_2024_xyz789') }}">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.new_lot_code_for_transformed_product') }}</small>
                    @error('product_lot_code')
                        <small style="color: var(--danger);">{{ $message }}</small>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.output_product') }} *</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">{{ __('messages.select_product') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->product_name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Translated Output Quantity section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.output_quantity') }}</h3>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.output_quantity') }} *</label>
                        <input type="number" name="quantity" class="form-input" value="{{ old('quantity') }}" step="0.01" min="0.01" required>
                    </div>
                    
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
                    </div>
                </div>
            </div>

            <!-- Translated Processing Location section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.processing_location') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.processing_location') }} *</label>
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

            <!-- Translated Input TLCs section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.input_tlcs') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.input_tlcs_select_multiple') }} *</label>
                    
                    @if($activeTLCs->isEmpty())
                        <div style="padding: 2rem; text-align: center; background: var(--bg-tertiary); border-radius: 0.5rem; border: 2px dashed var(--border-color);">
                            <p style="color: var(--text-muted); margin: 0;">{{ __('messages.no_active_tlcs_available') }}</p>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.5rem;">{{ __('messages.please_create_receiving_event_first') }}</p>
                        </div>
                    @else
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.5rem;">
                            @foreach($activeTLCs as $tlc)
                            <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; cursor: pointer; border-radius: 0.375rem;" class="tlc-option">
                                <input type="checkbox" 
                                       name="input_trace_record_ids[]" 
                                       value="{{ $tlc->id }}" 
                                       data-tlc-id="{{ $tlc->id }}"
                                       data-tlc-code="{{ $tlc->tlc }}"
                                       data-available-quantity="{{ $tlc->available_quantity ?? 0 }}"
                                       data-total-quantity="{{ $tlc->quantity ?? 0 }}"
                                       data-unit="{{ $tlc->unit ?? 'kg' }}"
                                       data-product-name="{{ $tlc->product->product_name ?? 'Unknown' }}"
                                       class="tlc-checkbox"
                                       {{ in_array($tlc->id, old('input_trace_record_ids', [])) ? 'checked' : '' }}
                                       {{ ($tlc->available_quantity ?? 0) <= 0 ? 'disabled' : '' }}>
                                <span style="flex: 1;">
                                    <strong>{{ $tlc->tlc }}</strong>
                                    @if(($tlc->available_quantity ?? 0) <= 0)
                                        <span class="badge badge-danger" style="font-size: 0.7rem;">{{ __('messages.depleted') }}</span>
                                    @elseif(($tlc->available_quantity ?? 0) < ($tlc->quantity ?? 0) * 0.2)
                                        <span class="badge badge-warning" style="font-size: 0.7rem;">{{ __('messages.low_stock') }}</span>
                                    @endif
                                    <br>
                                    <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                        {{ $tlc->product->product_name ?? 'Unknown Product' }}<br>
                                        <span style="color: {{ ($tlc->available_quantity ?? 0) > 0 ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                                            {{ __('messages.available') }}: {{ number_format($tlc->available_quantity ?? 0, 2) }} {{ $tlc->unit ?? 'kg' }}
                                        </span>
                                        <span style="color: var(--text-muted);">
                                            / {{ __('messages.total') }}: {{ number_format($tlc->quantity ?? 0, 2) }} {{ $tlc->unit ?? 'kg' }}
                                        </span>
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            {{ __('messages.only_available_quantity_can_be_used') }}
                        </small>
                    @endif
                </div>
            </div>

            <!-- Translated Transformation Details section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.transformation_details') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.transformation_description') }} *</label>
                    <textarea name="transformation_description" class="form-textarea" rows="2" placeholder="{{ __('messages.describe_transformation_process') }}" required>{{ old('transformation_description') }}</textarea>
                </div>
                
                <!-- Removed confusing output_tlcs JSON field, system will use output_tlc automatically -->
                <input type="hidden" name="output_tlcs" value="">
                
                <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0;">
                        <strong>{{ __('messages.note') }}:</strong> {{ __('messages.output_tlc_will_be_created_automatically') }}
                    </p>
                </div>
            </div>

            <!-- Translated Event Date section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.event_date') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.event_date') }} *</label>
                    <input type="datetime-local" name="event_date" class="form-input" value="{{ old('event_date', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
            </div>

            <!-- Translated Reference & Compliance section -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.reference_compliance') }}</h3>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.reference_document') }}</label>
                    <input type="text" name="reference_doc" class="form-input" value="{{ old('reference_doc') }}" placeholder="{{ __('messages.eg_batch_12345') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.reference_document_type') }}
                        <span style="color: var(--text-secondary); font-size: 0.75rem;">({{ __('messages.optional') }})</span>
                    </label>
                    <select name="reference_doc_type" class="form-select">
                        <option value="">{{ __('messages.select_document_type') }}</option>
                        <option value="PO" {{ old('reference_doc_type') == 'PO' ? 'selected' : '' }}>{{ __('messages.purchase_order') }} (PO)</option>
                        <option value="Invoice" {{ old('reference_doc_type') == 'Invoice' ? 'selected' : '' }}>{{ __('messages.invoice') }}</option>
                        <option value="BOL" {{ old('reference_doc_type') == 'BOL' ? 'selected' : '' }}>{{ __('messages.bill_of_lading') }} (BOL)</option>
                        <option value="AWB" {{ old('reference_doc_type') == 'AWB' ? 'selected' : '' }}>{{ __('messages.air_waybill') }} (AWB)</option>
                        <option value="Other" {{ old('reference_doc_type') == 'Other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                    </select>
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
            
            <!-- Added quantity conservation validation display -->
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--warning);">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--warning);">{{ __('messages.quantity_conservation_rule') }}</h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0;">
                    {{ __('messages.output_quantity_cannot_exceed_input') }}
                </p>
            </div>

            <!-- Added real-time quantity validation feedback -->
            <div id="quantityValidationFeedback" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.5rem; display: none;" class="validation-feedback">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span id="validationIcon"></span>
                    <span id="validationMessage" style="font-size: 0.875rem;"></span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;" id="submitBtn">{{ __('messages.record_transformation') }}</button>
        </form>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.quantity_tracking') }}</h2>
        
        <!-- Added consumed quantity display for selected TLCs -->
        <div id="quantitySummary" style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_input_quantity') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);" id="totalInputQuantity">0</div>
            </div>
            
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_consumed_quantity') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--danger);" id="totalConsumedQuantity">0</div>
            </div>
            
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.output_quantity_limit') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);" id="outputQuantityLimit">0</div>
            </div>
            
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.yield_percentage') }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--info);" id="yieldPercentage">0%</div>
            </div>
        </div>

        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">
        
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.recent_transformation_events') }}</h3>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($recentEvents as $event)
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; {{ $event->is_voided ? 'opacity: 0.6; border: 2px dashed var(--danger);' : '' }}">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong>{{ $event->traceRecord?->tlc ?? 'N/A' }}</strong>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge badge-info">{{ __('messages.transformation') }}</span>
                        @if($event->is_voided)
                            <span class="badge badge-danger">{{ __('messages.voided') }}</span>
                        @endif
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    {{ $event->traceRecord?->product?->product_name ?? __('messages.product_deleted') }}<br>
                    {{ $event->traceRecord?->quantity ?? 'N/A' }} {{ $event->traceRecord?->unit ?? '' }}<br>
                    {{ __('messages.inputs') }}: {{ count($event->input_tlcs ?? []) }} {{ __('messages.tlcs') }}<br>
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
                                data-event-type="transformation"
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
                {{ __('messages.no_recent_transformation_events') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Added void confirmation modal -->
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
.tlc-option:hover {
    background: var(--bg-secondary);
}

.validation-feedback {
    background: var(--bg-tertiary);
}

.validation-feedback.success {
    border-left: 4px solid var(--success);
}

.validation-feedback.error {
    border-left: 4px solid var(--danger);
}

.validation-feedback.warning {
    border-left: 4px solid var(--warning);
}

.gln-input:invalid {
    border-color: var(--danger);
}

.gln-input:valid {
    border-color: var(--success);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Transformation quantity tracking initialized');
    
    const form = document.getElementById('transformationForm');
    const tlcCheckboxes = document.querySelectorAll('.tlc-checkbox');
    const outputQuantityInput = document.querySelector('input[name="quantity"]');
    const validationFeedback = document.getElementById('quantityValidationFeedback');
    const validationIcon = document.getElementById('validationIcon');
    const validationMessage = document.getElementById('validationMessage');
    const totalInputQuantityEl = document.getElementById('totalInputQuantity');
    const totalConsumedQuantityEl = document.getElementById('totalConsumedQuantity');
    const outputQuantityLimitEl = document.getElementById('outputQuantityLimit');
    const yieldPercentageEl = document.getElementById('yieldPercentage');
    const submitBtn = document.getElementById('submitBtn');

    console.log('[v0] Found ' + tlcCheckboxes.length + ' TLC checkboxes');

    function updateQuantityValidation() {
        console.log('[v0] === Updating quantity validation ===');
        
        let totalInputQuantity = 0;
        let totalConsumedQuantity = 0;
        let selectedUnit = '';
        let selectedCount = 0;
        let selectedTLCs = [];
        
        tlcCheckboxes.forEach(checkbox => {
            if (checkbox.checked && !checkbox.disabled) {
                const availableQty = parseFloat(checkbox.dataset.availableQuantity) || 0;
                const totalQty = parseFloat(checkbox.dataset.totalQuantity) || 0;
                const unit = checkbox.dataset.unit || 'kg';
                const tlcCode = checkbox.dataset.tlcCode || '';
                const productName = checkbox.dataset.productName || '';
                
                const consumedQty = totalQty - availableQty;
                
                console.log('[v0] Selected TLC:', {
                    code: tlcCode,
                    product: productName,
                    total: totalQty,
                    available: availableQty,
                    consumed: consumedQty,
                    unit: unit
                });
                
                totalInputQuantity += availableQty;
                totalConsumedQuantity += consumedQty;
                selectedCount++;
                selectedTLCs.push(tlcCode);
                
                if (!selectedUnit && unit) {
                    selectedUnit = unit;
                }
            }
        });

        console.log('[v0] Summary:', {
            selectedCount: selectedCount,
            totalInput: totalInputQuantity,
            totalConsumed: totalConsumedQuantity,
            unit: selectedUnit,
            tlcs: selectedTLCs
        });

        const outputQuantity = parseFloat(outputQuantityInput.value) || 0;
        const isValid = outputQuantity <= totalInputQuantity && totalInputQuantity > 0 && outputQuantity > 0;
        const yield_pct = totalInputQuantity > 0 ? ((outputQuantity / totalInputQuantity) * 100).toFixed(1) : 0;

        totalInputQuantityEl.textContent = totalInputQuantity.toFixed(2) + (selectedUnit ? ' ' + selectedUnit : '');
        totalConsumedQuantityEl.textContent = totalConsumedQuantity.toFixed(2) + (selectedUnit ? ' ' + selectedUnit : '');
        outputQuantityLimitEl.textContent = totalInputQuantity.toFixed(2) + (selectedUnit ? ' ' + selectedUnit : '');
        yieldPercentageEl.textContent = yield_pct + '%';

        console.log('[v0] Validation:', {
            output: outputQuantity,
            yield: yield_pct + '%',
            valid: isValid
        });

        if (selectedCount === 0) {
            validationFeedback.style.display = 'block';
            validationFeedback.className = 'validation-feedback warning';
            validationIcon.innerHTML = '⚠️';
            validationMessage.textContent = '{{ __("messages.please_select_input_tlcs") }}';
            submitBtn.disabled = true;
        } else if (totalInputQuantity === 0) {
            validationFeedback.style.display = 'block';
            validationFeedback.className = 'validation-feedback error';
            validationIcon.innerHTML = '❌';
            validationMessage.textContent = 'Selected TLCs have 0 available quantity. Please select TLCs with available stock.';
            submitBtn.disabled = true;
        } else if (outputQuantity === 0) {
            validationFeedback.style.display = 'block';
            validationFeedback.className = 'validation-feedback warning';
            validationIcon.innerHTML = '⚠️';
            validationMessage.textContent = '{{ __("messages.please_enter_output_quantity") }}';
            submitBtn.disabled = true;
        } else if (isValid) {
            validationFeedback.style.display = 'block';
            validationFeedback.className = 'validation-feedback success';
            validationIcon.innerHTML = '✅';
            validationMessage.textContent = '{{ __("messages.quantity_validation_passed") }} ({{ __("messages.yield") }}: ' + yield_pct + '%)';
            submitBtn.disabled = false;
        } else {
            validationFeedback.style.display = 'block';
            validationFeedback.className = 'validation-feedback error';
            validationIcon.innerHTML = '❌';
            validationMessage.textContent = '{{ __("messages.output_exceeds_input") }} (' + outputQuantity.toFixed(2) + ' > ' + totalInputQuantity.toFixed(2) + ' ' + selectedUnit + ')';
            submitBtn.disabled = true;
        }
    }

    tlcCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('[v0] Checkbox changed:', this.dataset.tlcCode, 'Checked:', this.checked);
            updateQuantityValidation();
        });
    });

    outputQuantityInput.addEventListener('input', function() {
        console.log('[v0] Output quantity changed:', this.value);
        updateQuantityValidation();
    });
    
    console.log('[v0] Running initial validation');
    updateQuantityValidation();
});

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
</script>
@endpush
@endsection
