

<?php $__env->startSection('title', __('messages.compliance_report')); ?>

<?php $__env->startSection('content'); ?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.compliance_score')); ?></div>
        <div style="font-size: 3rem; font-weight: 700; color: <?php echo e($complianceScore >= 80 ? 'var(--success)' : ($complianceScore >= 60 ? 'var(--warning)' : 'var(--error)')); ?>;">
            <?php echo e($complianceScore); ?>%
        </div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.ftl_products')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['total_products']); ?></div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.total_cte_events')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['total_cte_events']); ?></div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.audit_logs_30d')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['recent_audit_logs']); ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.cte_events_breakdown')); ?></h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo e(__('messages.receiving_events')); ?></span>
                <span class="badge badge-success"><?php echo e($stats['receiving_events']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo e(__('messages.transformation_events')); ?></span>
                <span class="badge badge-info"><?php echo e($stats['transformation_events']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo e(__('messages.shipping_events')); ?></span>
                <span class="badge badge-warning"><?php echo e($stats['shipping_events']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.trace_records_status')); ?></h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo e(__('messages.total_records')); ?></span>
                <span class="badge badge-info"><?php echo e($stats['total_trace_records']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo e(__('messages.active_records')); ?></span>
                <span class="badge badge-success"><?php echo e($stats['active_records']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo e(__('messages.processed_shipped')); ?></span>
                <span class="badge badge-warning"><?php echo e($stats['total_trace_records'] - $stats['active_records']); ?></span>
            </div>
        </div>
    </div>
</div>

<?php if(count($inactiveProducts) > 0): ?>
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">
        <?php echo e(__('messages.products_without_recent_activity')); ?>

    </h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.sku')); ?></th>
                    <th><?php echo e(__('messages.product_name')); ?></th>
                    <th><?php echo e(__('messages.category')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $inactiveProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($product->sku); ?></strong></td>
                    <td><?php echo e($product->product_name); ?></td>
                    <td><?php echo e($product->category ?? '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/reports/compliance.blade.php ENDPATH**/ ?>