

<?php $__env->startSection('title', __('messages.products')); ?>

<?php $__env->startSection('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="<?php echo e(__('messages.search_products')); ?>" value="<?php echo e(request('search')); ?>" style="max-width: 300px;">
        
        <select name="ftl" class="form-select" style="max-width: 150px;">
            <option value=""><?php echo e(__('messages.all_products')); ?></option>
            <option value="yes" <?php echo e(request('ftl') === 'yes' ? 'selected' : ''); ?>><?php echo e(__('messages.ftl_only')); ?></option>
            <option value="no" <?php echo e(request('ftl') === 'no' ? 'selected' : ''); ?>><?php echo e(__('messages.non_ftl')); ?></option>
        </select>
        
        <select name="category" class="form-select" style="max-width: 150px;">
            <option value=""><?php echo e(__('messages.all_categories')); ?></option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category); ?>" <?php echo e(request('category') === $category ? 'selected' : ''); ?>>
                    <?php echo e($category); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        
        <button type="submit" class="btn btn-secondary"><?php echo e(__('messages.filter')); ?></button>
        <?php if(request()->hasAny(['search', 'ftl', 'category'])): ?>
            <a href="<?php echo e(route('master-data.products.index')); ?>" class="btn btn-secondary"><?php echo e(__('messages.clear')); ?></a>
        <?php endif; ?>
    </form>
    
    <a href="<?php echo e(route('master-data.products.create')); ?>" class="btn btn-primary"><?php echo e(__('messages.add_product')); ?></a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.sku')); ?></th>
                    <th><?php echo e(__('messages.product_name')); ?></th>
                    <th><?php echo e(__('messages.category')); ?></th>
                    <th><?php echo e(__('messages.ftl')); ?></th>
                    <th><?php echo e(__('messages.unit')); ?></th>
                    <th><?php echo e(__('messages.actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($product->sku); ?></strong></td>
                    <td><?php echo e($product->product_name); ?></td>
                    <td><?php echo e($product->category ?? '-'); ?></td>
                    <td>
                        <?php if($product->is_ftl): ?>
                            <span class="badge badge-success"><?php echo e(__('messages.yes')); ?></span>
                        <?php else: ?>
                            <span class="badge badge-info"><?php echo e(__('messages.no')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($product->unit_of_measure); ?></td>
                    <td>
                        <a href="<?php echo e(route('master-data.products.edit', $product)); ?>" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;"><?php echo e(__('messages.edit')); ?></a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);"><?php echo e(__('messages.no_products_found')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php echo e($products->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/master-data/products/index.blade.php ENDPATH**/ ?>