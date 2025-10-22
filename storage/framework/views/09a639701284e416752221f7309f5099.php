

<?php $__env->startSection('title', __('messages.shipping')); ?>

<?php $__env->startSection('content'); ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.record_shipping_event')); ?></h2>
        
        <form method="POST" action="<?php echo e(route('cte.shipping')); ?>" id="shippingForm">
            <?php echo csrf_field(); ?>
            
            <!-- Translated TLC Selection section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.select_items_to_ship')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.product_lot_code')); ?> *
                        <span style="color: var(--danger); font-size: 0.75rem;"><?php echo e(__('messages.required_for_fda_compliance')); ?></span>
                    </label>
                    <input type="text" name="product_lot_code" class="form-input" value="<?php echo e(old('product_lot_code')); ?>" required placeholder="<?php echo e(__('messages.eg_lot_2024_abc123')); ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.product_lot_code_being_shipped')); ?></small>
                    <?php $__errorArgs = ['product_lot_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small style="color: var(--danger);"><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.5rem;">
                    <?php $__currentLoopData = $activeTLCs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tlc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="padding: 0.75rem; border-radius: 0.375rem; border: 1px solid var(--border-color); margin-bottom: 0.5rem;" class="tlc-ship-item" data-tlc-id="<?php echo e($tlc->id); ?>">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" 
                                   name="trace_record_ids[]" 
                                   value="<?php echo e($tlc->id); ?>" 
                                   class="tlc-checkbox"
                                   data-available="<?php echo e($tlc->available_quantity); ?>"
                                   data-unit="<?php echo e($tlc->unit); ?>"
                                   <?php echo e(in_array($tlc->id, old('trace_record_ids', [])) ? 'checked' : ''); ?>>
                            <span style="flex: 1;">
                                <strong><?php echo e($tlc->tlc); ?></strong>
                                <?php if($tlc->available_quantity < $tlc->quantity): ?>
                                    <span class="badge badge-info" style="font-size: 0.7rem;"><?php echo e(__('messages.partially_consumed')); ?></span>
                                <?php endif; ?>
                                <br>
                                <span style="font-size: 0.875rem; color: var(--text-secondary);">
                                    <?php echo e($tlc->product->product_name); ?><br>
                                    <span style="color: var(--success); font-weight: 600;">
                                        <?php echo e(__('messages.available')); ?>: <?php echo e($tlc->available_quantity); ?> <?php echo e($tlc->unit); ?>

                                    </span>
                                    @ <?php echo e($tlc->location->location_name); ?>

                                </span>
                            </span>
                        </label>
                        
                        <!-- Added quantity input field that appears when checkbox is checked -->
                        <div class="quantity-input-container" style="display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
                            <label class="form-label" style="font-size: 0.875rem;"><?php echo e(__('messages.quantity_to_ship')); ?> *</label>
                            <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.5rem; align-items: center;">
                                <input type="number" 
                                       name="quantities_shipped[<?php echo e($tlc->id); ?>]" 
                                       class="form-input quantity-input" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="<?php echo e($tlc->available_quantity); ?>"
                                       value="<?php echo e(old('quantities_shipped.' . $tlc->id, $tlc->available_quantity)); ?>"
                                       placeholder="0.00"
                                       disabled>
                                <span class="unit-label" style="color: var(--text-secondary); font-weight: 600;"><?php echo e($tlc->unit); ?></span>
                            </div>
                            <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                                <?php echo e(__('messages.max')); ?>: <?php echo e($tlc->available_quantity); ?> <?php echo e($tlc->unit); ?>

                            </small>
                            <small class="quantity-validation" style="display: none; margin-top: 0.25rem;"></small>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Translated Shipping From Location section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.shipping_from_location')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.shipping_from_location')); ?> *</label>
                    <select name="location_id" class="form-select" required>
                        <option value=""><?php echo e(__('messages.select_location')); ?></option>
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($location->id); ?>" <?php echo e(old('location_id') == $location->id ? 'selected' : ''); ?>>
                                <?php echo e($location->location_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <!-- Translated Shipping To Location section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.shipping_to_location')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.ship_to_customer')); ?> *</label>
                    <select name="partner_id" class="form-select" required>
                        <option value=""><?php echo e(__('messages.select_customer')); ?></option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($customer->id); ?>" <?php echo e(old('partner_id') == $customer->id ? 'selected' : ''); ?>>
                                <?php echo e($customer->partner_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.shipping_location_gln')); ?></label>
                    <input type="text" name="shipping_location_gln" class="form-input gln-input" value="<?php echo e(old('shipping_location_gln')); ?>" placeholder="<?php echo e(__('messages.global_location_number')); ?>" pattern="^\d{13}$" title="<?php echo e(__('messages.gln_must_be_13_digits')); ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.gln_13_digits')); ?></small>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.shipping_location_name')); ?></label>
                    <input type="text" name="shipping_location_name" class="form-input" value="<?php echo e(old('shipping_location_name')); ?>" placeholder="<?php echo e(__('messages.full_destination_location_name')); ?>">
                </div>
            </div>

            <!-- Translated Dates section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.dates')); ?></h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.shipping_date')); ?> *</label>
                        <input type="datetime-local" name="event_date" class="form-input" value="<?php echo e(old('event_date', now()->format('Y-m-d\TH:i'))); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.expected_receiving_date')); ?></label>
                        <input type="date" name="receiving_date_expected" class="form-input" value="<?php echo e(old('receiving_date_expected')); ?>">
                    </div>
                </div>
            </div>

            <!-- Translated Reference & Compliance section -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.reference_compliance')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.shipping_reference_document_bol_invoice')); ?> *</label>
                    <input type="text" name="shipping_reference_doc" class="form-input" value="<?php echo e(old('shipping_reference_doc')); ?>" placeholder="<?php echo e(__('messages.eg_bol_12345')); ?>" required>
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.required_for_fda_compliance')); ?></small>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.reference_document_type')); ?> *
                        <span style="color: var(--danger); font-size: 0.75rem;"><?php echo e(__('messages.required_for_fda_compliance')); ?></span>
                    </label>
                    <select name="reference_doc_type" class="form-select" required>
                        <option value=""><?php echo e(__('messages.select_document_type')); ?></option>
                        <option value="PO" <?php echo e(old('reference_doc_type') == 'PO' ? 'selected' : ''); ?>><?php echo e(__('messages.purchase_order')); ?> (PO)</option>
                        <option value="Invoice" <?php echo e(old('reference_doc_type') == 'Invoice' ? 'selected' : ''); ?>><?php echo e(__('messages.invoice')); ?></option>
                        <option value="BOL" <?php echo e(old('reference_doc_type') == 'BOL' ? 'selected' : ''); ?>><?php echo e(__('messages.bill_of_lading')); ?> (BOL)</option>
                        <option value="AWB" <?php echo e(old('reference_doc_type') == 'AWB' ? 'selected' : ''); ?>><?php echo e(__('messages.air_waybill')); ?> (AWB)</option>
                        <option value="Other" <?php echo e(old('reference_doc_type') == 'Other' ? 'selected' : ''); ?>><?php echo e(__('messages.other')); ?></option>
                    </select>
                    <?php $__errorArgs = ['reference_doc_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small style="color: var(--danger);"><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.fda_compliance_notes')); ?></label>
                    <textarea name="fda_compliance_notes" class="form-textarea" rows="2" placeholder="<?php echo e(__('messages.any_compliance_notes')); ?>"><?php echo e(old('fda_compliance_notes')); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.notes')); ?></label>
                    <textarea name="notes" class="form-textarea" rows="2"><?php echo e(old('notes')); ?></textarea>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;"><?php echo e(__('messages.record_shipping')); ?></button>
        </form>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.recent_shipping_events')); ?></h2>
        
        <!-- CHANGE: Added total shipped quantity display -->
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.total_shipped_quantity')); ?></div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);" id="totalShippedQuantity">0</div>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; <?php echo e($event->is_voided ? 'opacity: 0.6; border: 2px dashed var(--danger);' : ''); ?>">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong><?php echo e($event->traceRecord->tlc); ?></strong>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge badge-warning"><?php echo e(__('messages.shipping')); ?></span>
                        <?php if($event->is_voided): ?>
                            <span class="badge badge-danger"><?php echo e(__('messages.voided')); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    <?php echo e($event->traceRecord->product->product_name); ?><br>
                    <!-- CHANGE: Display quantity_shipped instead of quantity_received -->
                    <?php echo e($event->quantity_shipped ?? $event->traceRecord->quantity); ?> <?php echo e($event->traceRecord->unit); ?><br>
                    <?php echo e(__('messages.to')); ?>: <?php echo e($event->partner->partner_name); ?><br>
                    <?php echo e($event->event_date->format('Y-m-d H:i')); ?>

                    
                    <?php if($event->is_voided): ?>
                        <br>
                        <span style="color: var(--danger); font-weight: 600;">
                            <?php echo e(__('messages.voided_by')); ?>: <?php echo e($event->voidedBy->full_name ?? 'System'); ?><br>
                            <?php echo e(__('messages.voided_at')); ?>: <?php echo e($event->voided_at ? $event->voided_at->format('Y-m-d H:i') : 'N/A'); ?>

                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if(!$event->is_voided && ($event->void_count ?? 0) < 1): ?>
                    <?php
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
                    ?>
                    
                    <?php if($canVoid): ?>
                        <button type="button" 
                                class="btn btn-sm btn-danger void-event-btn" 
                                data-event-id="<?php echo e($event->id); ?>"
                                data-event-type="shipping"
                                data-tlc="<?php echo e($event->traceRecord->tlc); ?>"
                                style="margin-top: 0.5rem; font-size: 0.75rem;">
                            <?php echo e(__('messages.void_event')); ?>

                        </button>
                    <?php else: ?>
                        <small style="display: block; margin-top: 0.5rem; color: var(--text-muted); font-style: italic;">
                            <?php echo e(__('messages.void_time_restriction')); ?>

                        </small>
                    <?php endif; ?>
                <?php elseif(($event->void_count ?? 0) >= 1): ?>
                    <small style="display: block; margin-top: 0.5rem; color: var(--danger); font-weight: 600;">
                        <?php echo e(__('messages.event_already_voided_once')); ?>

                    </small>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                <?php echo e(__('messages.no_recent_shipping_events')); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div id="voidModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 0.5rem; padding: 2rem; max-width: 500px; width: 90%;">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.void_event')); ?></h3>
        
        <div style="padding: 1rem; background: var(--danger-bg); border-left: 4px solid var(--danger); border-radius: 0.375rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.875rem; color: var(--danger); margin: 0;">
                <strong><?php echo e(__('messages.void_warning')); ?></strong>
            </p>
        </div>
        
        <form id="voidForm" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="event_type" id="voidEventType">
            <input type="hidden" name="organization_id" value="<?php echo e(auth()->user()->organization_id); ?>">
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.void_reason')); ?> *</label>
                <select name="void_reason" class="form-select" required>
                    <option value=""><?php echo e(__('messages.select_type')); ?></option>
                    <option value="data_entry_error">Data Entry Error</option>
                    <option value="duplicate_entry">Duplicate Entry</option>
                    <option value="incorrect_quantity">Incorrect Quantity</option>
                    <option value="wrong_product">Wrong Product</option>
                    <option value="wrong_date">Wrong Date</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.void_notes')); ?> *</label>
                <textarea name="void_notes" class="form-textarea" rows="3" required placeholder="<?php echo e(__('messages.describe_what_changed')); ?>"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeVoidModal()" style="flex: 1;">
                    <?php echo e(__('messages.cancel')); ?>

                </button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    <?php echo e(__('messages.void_event')); ?>

                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
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
                quantityValidation.textContent = '<?php echo e(__("messages.quantity_must_be_greater_than_zero")); ?>';
                quantityValidation.style.color = 'var(--danger)';
                quantityValidation.style.display = 'block';
                quantityInput.style.borderColor = 'var(--danger)';
                return false;
            } else if (value > maxQuantity) {
                quantityValidation.textContent = `<?php echo e(__("messages.exceeds_available")); ?>: ${maxQuantity} ${unit}`;
                quantityValidation.style.color = 'var(--danger)';
                quantityValidation.style.display = 'block';
                quantityInput.style.borderColor = 'var(--danger)';
                return false;
            } else {
                quantityValidation.textContent = `✓ <?php echo e(__("messages.valid")); ?>`;
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
            receivingDate.setCustomValidity('<?php echo e(__("messages.receiving_date_must_be_after_shipping")); ?>');
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
        if (!confirm('<?php echo e(__("messages.void_confirmation")); ?>')) {
            e.preventDefault();
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/cte/shipping.blade.php ENDPATH**/ ?>