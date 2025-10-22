

<?php $__env->startSection('title', __('messages.locations')); ?>

<?php $__env->startSection('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="<?php echo e(__('messages.search_locations')); ?>" value="<?php echo e(request('search')); ?>" style="max-width: 300px;">
        
        <select name="type" class="form-select" style="max-width: 150px;">
            <option value=""><?php echo e(__('messages.all_types')); ?></option>
            <option value="warehouse" <?php echo e(request('type') === 'warehouse' ? 'selected' : ''); ?>><?php echo e(__('messages.warehouse')); ?></option>
            <option value="farm" <?php echo e(request('type') === 'farm' ? 'selected' : ''); ?>><?php echo e(__('messages.farm')); ?></option>
            <option value="processing" <?php echo e(request('type') === 'processing' ? 'selected' : ''); ?>><?php echo e(__('messages.processing')); ?></option>
            <option value="distribution" <?php echo e(request('type') === 'distribution' ? 'selected' : ''); ?>><?php echo e(__('messages.distribution')); ?></option>
        </select>
        
        <button type="submit" class="btn btn-secondary"><?php echo e(__('messages.filter')); ?></button>
        <?php if(request()->hasAny(['search', 'type'])): ?>
            <a href="<?php echo e(route('master-data.locations.index')); ?>" class="btn btn-secondary"><?php echo e(__('messages.clear')); ?></a>
        <?php endif; ?>
    </form>
    
    <a href="<?php echo e(route('master-data.locations.create')); ?>" class="btn btn-primary"><?php echo e(__('messages.add_location')); ?></a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.location_name')); ?></th>
                    <th><?php echo e(__('messages.type')); ?></th>
                    <th><?php echo e(__('messages.gln')); ?></th>
                    <th><?php echo e(__('messages.ffrn')); ?></th>
                    <th><?php echo e(__('messages.city_state')); ?></th>
                    <th><?php echo e(__('messages.actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($location->location_name); ?></strong></td>
                    <td>
                        <span class="badge badge-info"><?php echo e(ucfirst($location->location_type)); ?></span>
                    </td>
                    <td><?php echo e($location->gln ?? '-'); ?></td>
                    <td><?php echo e($location->ffrn ?? '-'); ?></td>
                    <td><?php echo e($location->city); ?>, <?php echo e($location->state); ?></td>
                    <td>
                        <a href="<?php echo e(route('master-data.locations.edit', $location)); ?>" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;"><?php echo e(__('messages.edit')); ?></a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);"><?php echo e(__('messages.no_locations_found')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php echo e($locations->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/master-data/locations/index.blade.php ENDPATH**/ ?>