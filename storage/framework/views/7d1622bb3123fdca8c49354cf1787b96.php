

<?php $__env->startSection('title', __('messages.create_product')); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="max-width: 600px;">
    <form method="POST" action="<?php echo e(route('master-data.products.store')); ?>">
        <?php echo csrf_field(); ?>
        
        <div class="form-group">
            <label class="form-label"><?php echo e(__('messages.sku')); ?> *</label>
            <input type="text" name="sku" class="form-input" value="<?php echo e(old('sku')); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo e(__('messages.product_name')); ?> *</label>
            <input type="text" name="product_name" class="form-input" value="<?php echo e(old('product_name')); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo e(__('messages.description')); ?></label>
            <textarea name="description" class="form-textarea"><?php echo e(old('description')); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo e(__('messages.category')); ?></label>
            <input type="text" name="category" class="form-input" value="<?php echo e(old('category')); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo e(__('messages.unit_of_measure')); ?> *</label>
            <select name="unit_of_measure" class="form-select" required>
                <option value="kg"><?php echo e(__('messages.kilogram')); ?> (kg)</option>
                <option value="lb"><?php echo e(__('messages.pound')); ?> (lb)</option>
                <option value="box"><?php echo e(__('messages.box')); ?></option>
                <option value="case"><?php echo e(__('messages.case')); ?></option>
                <option value="pallet"><?php echo e(__('messages.pallet')); ?></option>
            </select>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_ftl" value="1" <?php echo e(old('is_ftl') ? 'checked' : ''); ?>>
                <span class="form-label" style="margin: 0;"><?php echo e(__('messages.food_traceability_list_ftl')); ?></span>
            </label>
        </div>
        
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary"><?php echo e(__('messages.create_product')); ?></button>
            <a href="<?php echo e(route('master-data.products.index')); ?>" class="btn btn-secondary"><?php echo e(__('messages.cancel')); ?></a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/master-data/products/create.blade.php ENDPATH**/ ?>