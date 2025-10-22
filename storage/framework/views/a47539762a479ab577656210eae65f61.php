

<?php $__env->startSection('title', __('messages.receiving')); ?>

<?php $__env->startSection('content'); ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.record_receiving_event')); ?></h2>
        
        <form method="POST" action="<?php echo e(route('cte.receiving')); ?>" id="receivingForm">
            <?php echo csrf_field(); ?>
            
            <!-- Basic Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.basic_information')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.tlc_traceability_lot_code')); ?> *</label>
                    <input type="text" name="tlc" class="form-input" value="<?php echo e(old('tlc')); ?>" required placeholder="<?php echo e(__('messages.eg_rcv_2024_001')); ?>">
                    <?php $__errorArgs = ['tlc'];
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
                    <label class="form-label"><?php echo e(__('messages.product_lot_code')); ?> *
                        <span style="color: var(--danger); font-size: 0.75rem;"><?php echo e(__('messages.required_for_fda_compliance')); ?></span>
                    </label>
                    <input type="text" name="product_lot_code" class="form-input" value="<?php echo e(old('product_lot_code')); ?>" required placeholder="<?php echo e(__('messages.eg_lot_2024_abc123')); ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.original_product_lot_code_from_supplier')); ?></small>
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
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.product')); ?> *</label>
                    <select name="product_id" id="productSelect" class="form-select" required>
                        <option value=""><?php echo e(__('messages.select_product')); ?></option>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($product->id); ?>" <?php echo e(old('product_id') == $product->id ? 'selected' : ''); ?>>
                                <?php echo e($product->product_name); ?> (<?php echo e($product->sku); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <!-- Added debug info to show product count -->
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                        <?php echo e(count($products)); ?> <?php echo e(__('messages.products_available')); ?>

                        <?php if(count($products) == 0): ?>
                            <span style="color: var(--danger);">
                                - <?php echo e(__('messages.no_products_found')); ?>. <?php echo e(__('messages.please_add_products_in_master_data')); ?>

                            </span>
                        <?php endif; ?>
                    </small>
                    <?php $__errorArgs = ['product_id'];
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
                    <label class="form-label"><?php echo e(__('messages.product_description')); ?></label>
                    <textarea name="product_description" class="form-textarea" rows="2" placeholder="<?php echo e(__('messages.detailed_product_description_fda')); ?>"><?php echo e(old('product_description')); ?></textarea>
                </div>
            </div>

            <!-- Quantity Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.quantity_information')); ?></h3>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.quantity_received')); ?> *</label>
                        <input type="number" name="quantity_received" class="form-input" value="<?php echo e(old('quantity_received')); ?>" step="0.01" min="0.01" required>
                        <?php $__errorArgs = ['quantity_received'];
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
                    
                    <!-- Fixed field name from quantity_unit to unit to match backend -->
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.unit')); ?> *</label>
                        <select name="unit" class="form-select" required>
                            <option value=""><?php echo e(__('messages.select_unit')); ?></option>
                            <option value="kg" <?php echo e(old('unit') == 'kg' ? 'selected' : ''); ?>>kg</option>
                            <option value="lb" <?php echo e(old('unit') == 'lb' ? 'selected' : ''); ?>>lb</option>
                            <option value="box" <?php echo e(old('unit') == 'box' ? 'selected' : ''); ?>><?php echo e(__('messages.box')); ?></option>
                            <option value="case" <?php echo e(old('unit') == 'case' ? 'selected' : ''); ?>><?php echo e(__('messages.case')); ?></option>
                            <option value="pallet" <?php echo e(old('unit') == 'pallet' ? 'selected' : ''); ?>><?php echo e(__('messages.pallet')); ?></option>
                        </select>
                        <?php $__errorArgs = ['unit'];
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
                </div>
            </div>

            <!-- Location Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.receiving_location')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.receiving_location')); ?> *</label>
                    <!-- Added ID for JavaScript auto-population -->
                    <select name="location_id" id="locationSelect" class="form-select" required>
                        <option value=""><?php echo e(__('messages.select_location')); ?></option>
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($location->id); ?>" 
                                    data-gln="<?php echo e($location->gln); ?>" 
                                    data-name="<?php echo e($location->location_name); ?>"
                                    <?php echo e(old('location_id') == $location->id ? 'selected' : ''); ?>>
                                <?php echo e($location->location_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['location_id'];
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
                
                <!-- Added ID for auto-population and validation feedback -->
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.receiving_location_gln')); ?> 
                        <span style="color: var(--danger); font-size: 0.75rem;"><?php echo e(__('messages.required_for_fda_compliance')); ?></span>
                    </label>
                    <input type="text" name="receiving_location_gln" id="locationGLN" class="form-input gln-input" value="<?php echo e(old('receiving_location_gln')); ?>" placeholder="<?php echo e(__('messages.global_location_number')); ?>" pattern="^\d{13}$" title="<?php echo e(__('messages.gln_must_be_13_digits')); ?>" maxlength="13">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.gln_13_digits')); ?></small>
                    <small id="glnValidation" style="display: none; margin-top: 0.25rem;"></small>
                    <?php $__errorArgs = ['receiving_location_gln'];
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
                
                <!-- Added ID for auto-population -->
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.receiving_location_name')); ?></label>
                    <input type="text" name="receiving_location_name" id="locationName" class="form-input" value="<?php echo e(old('receiving_location_name')); ?>" placeholder="<?php echo e(__('messages.full_location_name_fda')); ?>">
                </div>
                
                <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--info); margin-top: 1rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--info);"><?php echo e(__('messages.harvest_location_information')); ?></h4>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.harvest_location_gln')); ?>

                            <span style="color: var(--text-secondary); font-size: 0.75rem;">(<?php echo e(__('messages.optional_for_fresh_produce')); ?>)</span>
                        </label>
                        <input type="text" name="harvest_location_gln" class="form-input gln-input" value="<?php echo e(old('harvest_location_gln')); ?>" placeholder="<?php echo e(__('messages.farm_or_harvest_location_gln')); ?>" pattern="^\d{13}$|^$" title="<?php echo e(__('messages.gln_must_be_13_digits')); ?>" maxlength="13">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.gln_13_digits_or_leave_blank')); ?></small>
                        <?php $__errorArgs = ['harvest_location_gln'];
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
                        <label class="form-label"><?php echo e(__('messages.harvest_location_name')); ?>

                            <span style="color: var(--text-secondary); font-size: 0.75rem;">(<?php echo e(__('messages.optional')); ?>)</span>
                        </label>
                        <input type="text" name="harvest_location_name" class="form-input" value="<?php echo e(old('harvest_location_name')); ?>" placeholder="<?php echo e(__('messages.farm_name_or_harvest_location')); ?>">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.farm_or_harvest_location_name')); ?></small>
                    </div>
                </div>
            </div>

            <!-- Business Information section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.supplier_information')); ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.supplier')); ?> *</label>
                    <!-- Added ID for JavaScript auto-population -->
                    <select name="partner_id" id="partnerSelect" class="form-select" required>
                        <option value=""><?php echo e(__('messages.select_supplier')); ?></option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>" 
                                    data-gln="<?php echo e($supplier->gln); ?>" 
                                    data-name="<?php echo e($supplier->partner_name); ?>"
                                    <?php echo e(old('partner_id') == $supplier->id ? 'selected' : ''); ?>>
                                <?php echo e($supplier->partner_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['partner_id'];
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
                
                <!-- Added ID for auto-population -->
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.business_name')); ?></label>
                    <input type="text" name="business_name" id="businessName" class="form-input" value="<?php echo e(old('business_name')); ?>" placeholder="<?php echo e(__('messages.supplier_business_name')); ?>">
                </div>
                
                <!-- Added ID for auto-population and validation -->
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.business_gln')); ?></label>
                    <input type="text" name="business_gln" id="businessGLN" class="form-input gln-input" value="<?php echo e(old('business_gln')); ?>" placeholder="<?php echo e(__('messages.global_location_number')); ?>" pattern="^\d{13}$" title="<?php echo e(__('messages.gln_must_be_13_digits')); ?>" maxlength="13">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.gln_13_digits')); ?></small>
                    <small id="businessGlnValidation" style="display: none; margin-top: 0.25rem;"></small>
                    <?php $__errorArgs = ['business_gln'];
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
                    <label class="form-label"><?php echo e(__('messages.business_address')); ?></label>
                    <textarea name="business_address" class="form-textarea" rows="2" placeholder="<?php echo e(__('messages.full_business_address')); ?>"><?php echo e(old('business_address')); ?></textarea>
                </div>
            </div>

            <!-- Dates section -->
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.dates')); ?></h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <!-- Added IDs for date validation -->
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.harvest_date')); ?></label>
                        <input type="date" name="harvest_date" id="harvestDate" class="form-input" value="<?php echo e(old('harvest_date')); ?>">
                        <?php $__errorArgs = ['harvest_date'];
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
                        <label class="form-label"><?php echo e(__('messages.pack_date')); ?></label>
                        <input type="date" name="pack_date" id="packDate" class="form-input" value="<?php echo e(old('pack_date')); ?>">
                        <?php $__errorArgs = ['pack_date'];
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
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.cooling_date')); ?>

                        <span style="color: var(--text-secondary); font-size: 0.75rem;">(<?php echo e(__('messages.optional_for_fresh_produce')); ?>)</span>
                    </label>
                    <input type="datetime-local" name="cooling_date" class="form-input" value="<?php echo e(old('cooling_date')); ?>" placeholder="<?php echo e(__('messages.date_time_product_was_cooled')); ?>">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;"><?php echo e(__('messages.cooling_date_for_fresh_produce_compliance')); ?></small>
                    <?php $__errorArgs = ['cooling_date'];
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
                    <label class="form-label"><?php echo e(__('messages.event_date')); ?> *</label>
                    <input type="datetime-local" name="event_date" id="eventDate" class="form-input" value="<?php echo e(old('event_date', now()->format('Y-m-d\TH:i'))); ?>" required>
                    <?php $__errorArgs = ['event_date'];
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
                
                <!-- Added date validation feedback -->
                <div id="dateValidation" style="display: none; padding: 0.75rem; border-radius: 0.375rem; margin-top: 0.5rem;"></div>
            </div>

            <!-- Reference & Compliance section -->
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;"><?php echo e(__('messages.reference_compliance')); ?></h3>
                
                <!-- Made reference_doc required with indicator -->
                <div class="form-group">
                    <label class="form-label"><?php echo e(__('messages.reference_document_po_invoice_bol')); ?> *
                        <span style="color: var(--danger); font-size: 0.75rem;"><?php echo e(__('messages.required_for_fda_compliance')); ?></span>
                    </label>
                    <input type="text" name="reference_doc" class="form-input" value="<?php echo e(old('reference_doc')); ?>" placeholder="<?php echo e(__('messages.eg_po_12345')); ?>" required>
                    <?php $__errorArgs = ['reference_doc'];
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
            
            <!-- Enhanced validation info box -->
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--info);"><?php echo e(__('messages.date_sequence_validation')); ?></h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0;">
                    <?php echo e(__('messages.harvest_date')); ?> ≤ <?php echo e(__('messages.pack_date')); ?> ≤ <?php echo e(__('messages.event_date')); ?>

                </p>
            </div>
            
            <!-- Conditional E-Signature section - only show for Enterprise users -->
            <?php if(auth()->user()->hasFeature('e_signatures')): ?>
                <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--warning-bg); border-radius: 0.5rem; border-left: 4px solid var(--warning);">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--warning); margin-bottom: 1rem; text-transform: uppercase;">
                        <?php echo e(__('messages.electronic_signature_optional')); ?>

                    </h3>
                    
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        <?php echo e(__('messages.add_signature_for_compliance')); ?>

                    </p>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo e(__('messages.password_for_signature')); ?></label>
                        <input type="password" name="signature_password" class="form-input" placeholder="<?php echo e(__('messages.enter_your_password')); ?>">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                            <?php echo e(__('messages.password_verification_required')); ?>

                        </small>
                        <?php $__errorArgs = ['signature_password'];
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
                        <label class="form-label"><?php echo e(__('messages.signature_reason')); ?></label>
                        <input type="text" name="signature_reason" class="form-input" placeholder="<?php echo e(__('messages.eg_initial_receiving_record')); ?>">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                            <?php echo e(__('messages.optional_but_recommended')); ?>

                        </small>
                    </div>
                </div>
            <?php else: ?>
                <!-- Upgrade prompt for non-Enterprise users -->
                <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--info-bg); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--info); margin-bottom: 0.5rem; text-transform: uppercase;">
                        <?php echo e(__('messages.e_signatures_available_in_enterprise')); ?>

                    </h3>
                    
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        <?php echo e(__('messages.electronic_signature_is_premium_feature')); ?>

                    </p>
                    
                    <a href="<?php echo e(route('pricing')); ?>" class="btn btn-sm btn-primary" style="display: inline-block;">
                        <?php echo e(__('messages.upgrade_to_enterprise')); ?>

                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Dynamic button text based on package -->
            <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;">
                <?php if(auth()->user()->hasFeature('e_signatures')): ?>
                    <?php echo e(__('messages.record_receiving_with_signature')); ?>

                <?php else: ?>
                    <?php echo e(__('messages.record_receiving')); ?>

                <?php endif; ?>
            </button>
        </form>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.recent_receiving_events')); ?></h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; <?php echo e((isset($event->status) && $event->status === 'voided') || (isset($event->is_voided) && $event->is_voided) ? 'opacity: 0.6; border: 2px dashed var(--danger);' : ''); ?>">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong><?php echo e($event->traceRecord->tlc); ?></strong>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge badge-success"><?php echo e(__('messages.receiving')); ?></span>
                        
                        <?php if(isset($event->status) && $event->status === 'voided'): ?>
                            <span class="badge badge-danger"><?php echo e(__('messages.voided')); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    <?php echo e($event->traceRecord->product->product_name); ?><br>
                    <?php echo e($event->traceRecord->quantity); ?> <?php echo e($event->traceRecord->unit); ?><br>
                    <?php echo e(__('messages.from')); ?>: <?php echo e($event->partner->partner_name); ?><br>
                    <?php echo e($event->event_date->format('Y-m-d H:i')); ?>

                    
                    <?php if(isset($event->status) && $event->status === 'voided'): ?>
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
                    ?>
                    
                    <?php if($canVoid): ?>
                        <form method="POST" action="<?php echo e(route('cte.receiving.void', ['event' => $event->id])); ?>" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <button type="button" 
                                    class="btn btn-sm btn-danger void-event-btn" 
                                    data-event-id="<?php echo e($event->id); ?>"
                                    data-event-type="receiving"
                                    data-tlc="<?php echo e($event->traceRecord->tlc); ?>"
                                    style="margin-top: 0.5rem; font-size: 0.75rem;">
                                <?php echo e(__('messages.void_event')); ?>

                            </button>
                        </form>
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
                <?php echo e(__('messages.no_recent_receiving_events')); ?>

            </div>
            <?php endif; ?>
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
        if (!confirm('<?php echo e(__("messages.void_confirmation")); ?>')) {
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
            feedback.textContent = '<?php echo e(__("messages.gln_must_be_13_digits")); ?>';
            feedback.style.color = 'var(--danger)';
            feedback.style.display = 'block';
            return false;
        } else {
            feedback.textContent = '✓ <?php echo e(__("messages.valid")); ?>';
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
            message = '<?php echo e(__("messages.pack_date_after_harvest")); ?>';
        } else if (pack && event && pack > event) {
            isValid = false;
            message = '<?php echo e(__("messages.event_date_after_pack")); ?>';
        } else if (harvest && pack && event) {
            message = '✓ <?php echo e(__("messages.date_sequence_validation")); ?>: <?php echo e(__("messages.valid")); ?>';
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
            alert('<?php echo e(__("messages.date_sequence_validation")); ?>: <?php echo e(__("messages.invalid")); ?>');
            return false;
        }
    });
});
</script>

<!-- Added void confirmation modal -->
<div id="voidModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 0.5rem; padding: 2rem; max-width: 500px; width: 90%;">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.void_event')); ?></h3>
        
        <div style="padding: 1rem; background: var(--danger-bg); border-left: 4px solid var(--danger); border-radius: 0.375rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.875rem; color: var(--danger); margin: 0;">
                <strong><?php echo e(__('messages.void_warning')); ?></strong><br>
                <?php echo e(__('messages.signature_required_for_void')); ?>

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
            
            <!-- Added signature password field -->
            <div class="form-group" style="padding: 1rem; background: var(--warning-bg); border-radius: 0.375rem;">
                <label class="form-label" style="color: var(--warning);"><?php echo e(__('messages.password_for_signature')); ?> *</label>
                <input type="password" name="signature_password" class="form-input" required placeholder="<?php echo e(__('messages.enter_your_password')); ?>">
                <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                    <?php echo e(__('messages.signature_required_for_void')); ?>

                </small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeVoidModal()" style="flex: 1;">
                    <?php echo e(__('messages.cancel')); ?>

                </button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    <?php echo e(__('messages.void_with_signature')); ?>

                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/cte/receiving.blade.php ENDPATH**/ ?>