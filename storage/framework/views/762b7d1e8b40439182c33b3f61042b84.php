

<?php $__env->startSection('title', __('messages.partners')); ?>

<?php $__env->startSection('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="<?php echo e(__('messages.search_partners')); ?>" value="<?php echo e(request('search')); ?>" style="max-width: 300px;">
        
        <select name="type" class="form-select" style="max-width: 150px;">
            <option value=""><?php echo e(__('messages.all_types')); ?></option>
            <option value="supplier" <?php echo e(request('type') === 'supplier' ? 'selected' : ''); ?>><?php echo e(__('messages.supplier')); ?></option>
            <option value="customer" <?php echo e(request('type') === 'customer' ? 'selected' : ''); ?>><?php echo e(__('messages.customer')); ?></option>
            <option value="both" <?php echo e(request('type') === 'both' ? 'selected' : ''); ?>><?php echo e(__('messages.both')); ?></option>
        </select>
        
        <button type="submit" class="btn btn-secondary"><?php echo e(__('messages.filter')); ?></button>
        <?php if(request()->hasAny(['search', 'type'])): ?>
            <a href="<?php echo e(route('master-data.partners.index')); ?>" class="btn btn-secondary"><?php echo e(__('messages.clear')); ?></a>
        <?php endif; ?>
    </form>
    
    <a href="<?php echo e(route('master-data.partners.create')); ?>" class="btn btn-primary"><?php echo e(__('messages.add_partner')); ?></a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.partner_name')); ?></th>
                    <th><?php echo e(__('messages.type')); ?></th>
                    <th><?php echo e(__('messages.contact_person')); ?></th>
                    <th><?php echo e(__('messages.email')); ?></th>
                    <th><?php echo e(__('messages.phone')); ?></th>
                    <th><?php echo e(__('messages.actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($partner->partner_name); ?></strong></td>
                    <td>
                        <span class="badge badge-<?php echo e($partner->partner_type === 'supplier' ? 'success' : ($partner->partner_type === 'customer' ? 'warning' : 'info')); ?>">
                            <?php echo e(ucfirst($partner->partner_type)); ?>

                        </span>
                    </td>
                    <td><?php echo e($partner->contact_person ?? '-'); ?></td>
                    <td><?php echo e($partner->email ?? '-'); ?></td>
                    <td><?php echo e($partner->phone ?? '-'); ?></td>
                    <td>
                        <a href="<?php echo e(route('master-data.partners.edit', $partner)); ?>" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;"><?php echo e(__('messages.edit')); ?></a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);"><?php echo e(__('messages.no_partners_found')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php echo e($partners->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/master-data/partners/index.blade.php ENDPATH**/ ?>