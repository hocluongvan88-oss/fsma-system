

<?php $__env->startSection('title', __('messages.query_traceability')); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.query_traceability')); ?></h2>
    
    <form method="POST" action="<?php echo e(route('reports.traceability.query')); ?>">
        <?php echo csrf_field(); ?>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.tlc_traceability_lot_code')); ?> *</label>
                <input type="text" name="tlc" class="form-input" required placeholder="<?php echo e(__('messages.enter_tlc_to_trace')); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.trace_direction')); ?> *</label>
                <select name="direction" class="form-select" required>
                    <option value="backward"><?php echo e(__('messages.trace_backward_find_sources')); ?></option>
                    <option value="forward"><?php echo e(__('messages.trace_forward_find_destinations')); ?></option>
                    <option value="both"><?php echo e(__('messages.both_directions')); ?></option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo e(__('messages.query_traceability')); ?></button>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;"><?php echo e(__('messages.recent_trace_records')); ?></h2>
        <a href="<?php echo e(route('reports.traceability.analytics')); ?>" class="btn btn-secondary">
            <i class="bi bi-graph-up"></i> <?php echo e(__('messages.view_analytics')); ?>

        </a>
    </div>

    <form method="GET" action="<?php echo e(route('reports.traceability')); ?>" style="margin-bottom: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.search')); ?></label>
                <input type="text" name="search" class="form-input" value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('messages.search_tlc_lot')); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.product')); ?></label>
                <select name="product_id" class="form-select">
                    <option value=""><?php echo e(__('messages.all_products')); ?></option>
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($product->id); ?>" <?php echo e(request('product_id') == $product->id ? 'selected' : ''); ?>>
                        <?php echo e($product->product_name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.location')); ?></label>
                <select name="location_id" class="form-select">
                    <option value=""><?php echo e(__('messages.all_locations')); ?></option>
                    <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($location->id); ?>" <?php echo e(request('location_id') == $location->id ? 'selected' : ''); ?>>
                        <?php echo e($location->location_name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.status')); ?></label>
                <select name="status" class="form-select">
                    <option value=""><?php echo e(__('messages.all_statuses')); ?></option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>><?php echo e(__('messages.active')); ?></option>
                    <option value="consumed" <?php echo e(request('status') == 'consumed' ? 'selected' : ''); ?>><?php echo e(__('messages.consumed')); ?></option>
                    <option value="shipped" <?php echo e(request('status') == 'shipped' ? 'selected' : ''); ?>><?php echo e(__('messages.shipped')); ?></option>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1rem;">
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.date_from')); ?></label>
                <input type="date" name="date_from" class="form-input" value="<?php echo e(request('date_from')); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.date_to')); ?></label>
                <input type="date" name="date_to" class="form-input" value="<?php echo e(request('date_to')); ?>">
            </div>
        </div>
        
        <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary"><?php echo e(__('messages.apply_filters')); ?></button>
            <a href="<?php echo e(route('reports.traceability')); ?>" class="btn btn-secondary"><?php echo e(__('messages.clear_filters')); ?></a>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.tlc')); ?></th>
                    <th><?php echo e(__('messages.product')); ?></th>
                    <th><?php echo e(__('messages.lot_code')); ?></th>
                    <th><?php echo e(__('messages.quantity')); ?></th>
                    <th><?php echo e(__('messages.location')); ?></th>
                    <th><?php echo e(__('messages.status')); ?></th>
                    <th><?php echo e(__('messages.created_at')); ?></th>
                    <th><?php echo e(__('messages.actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($record->tlc); ?></strong></td>
                    <td><?php echo e($record->product->product_name); ?></td>
                    <td><?php echo e($record->lot_code); ?></td>
                    <td><?php echo e($record->quantity); ?> <?php echo e($record->unit); ?></td>
                    <td><?php echo e($record->location->location_name); ?></td>
                    <td>
                        <span class="badge badge-<?php echo e($record->status === 'active' ? 'success' : 'info'); ?>">
                            <?php echo e(__('messages.' . $record->status)); ?>

                        </span>
                    </td>
                    <td><?php echo e($record->created_at->format('Y-m-d H:i')); ?></td>
                    <td>
                        <form method="POST" action="<?php echo e(route('reports.traceability.query')); ?>" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="tlc" value="<?php echo e($record->tlc); ?>">
                            <input type="hidden" name="direction" value="both">
                            <button type="submit" class="btn btn-sm btn-primary"><?php echo e(__('messages.trace')); ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        <?php echo e(__('messages.no_records_found')); ?>

                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        <?php echo e($records->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/reports/traceability.blade.php ENDPATH**/ ?>