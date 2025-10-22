

<?php $__env->startSection('title', __('messages.dashboard')); ?>

<?php $__env->startSection('content'); ?>

<?php if($packageStats['show_warning']): ?>
<div class="card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; margin-bottom: 1.5rem; border: none;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="font-size: 2rem;">⚠️</div>
        <div style="flex: 1;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;"><?php echo e(__('messages.usage_warning')); ?></h3>
            <p style="margin: 0; opacity: 0.95;">
                <?php echo e(__('messages.usage_warning_message', [
                    'percentage' => number_format($packageStats['cte_percentage'], 1),
                    'used' => number_format($packageStats['cte_usage']),
                    'limit' => number_format($packageStats['cte_limit'])
                ])); ?>

            </p>
        </div>
        <?php if(auth()->user()->isAdmin()): ?>
        <a href="<?php echo e(route('admin.users.edit', auth()->user())); ?>" class="btn btn-light" style="background: white; color: #ff6b6b;">
            <?php echo e(__('messages.upgrade_package')); ?>

        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 1.5rem; border: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;"><?php echo e(__('messages.current_package')); ?></div>
            <div style="font-size: 1.5rem; font-weight: 700;"><?php echo e($packageStats['package_name']); ?></div>
        </div>
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;"><?php echo e(__('messages.cte_records')); ?></div>
                <div style="font-size: 1.25rem; font-weight: 600;">
                    <?php echo e(number_format($packageStats['cte_usage'])); ?>/<?php echo e(number_format($packageStats['cte_limit'])); ?>

                </div>
                <div style="width: 100px; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px; margin-top: 0.25rem;">
                    <div style="width: <?php echo e(min($packageStats['cte_percentage'], 100)); ?>%; height: 100%; background: white; border-radius: 2px;"></div>
                </div>
            </div>
            <?php if($packageStats['document_limit'] < 999999): ?>
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;"><?php echo e(__('messages.documents')); ?></div>
                <div style="font-size: 1.25rem; font-weight: 600;">
                    <?php echo e($packageStats['document_count']); ?>/<?php echo e($packageStats['document_limit']); ?>

                </div>
            </div>
            <?php endif; ?>
            <?php if($packageStats['user_limit'] < 999999): ?>
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;"><?php echo e(__('messages.users')); ?></div>
                <div style="font-size: 1.25rem; font-weight: 600;">
                    <?php echo e($packageStats['user_count']); ?>/<?php echo e($packageStats['user_limit']); ?>

                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.total_products')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['total_products']); ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem;">
            <?php echo e($stats['ftl_products']); ?> <?php echo e(__('messages.ftl_items')); ?>

        </div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.active_inventory')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['active_inventory']); ?></div>
        <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem;">
            <?php echo e(number_format($stats['total_inventory_qty'], 2)); ?> <?php echo e(__('messages.kg_total')); ?>

        </div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.locations')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['total_locations']); ?></div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.partners')); ?></div>
        <div style="font-size: 2rem; font-weight: 700;"><?php echo e($stats['total_partners']); ?></div>
    </div>
</div>


<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.recent_cte_events')); ?></h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.event_type')); ?></th>
                    <th><?php echo e(__('messages.tlc')); ?></th>
                    <th><?php echo e(__('messages.product')); ?></th>
                    <th><?php echo e(__('messages.location')); ?></th>
                    <th><?php echo e(__('messages.date')); ?></th>
                    <th><?php echo e(__('messages.created_by')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $stats['recent_events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <span class="badge badge-<?php echo e($event->event_type === 'receiving' ? 'success' : ($event->event_type === 'shipping' ? 'warning' : 'info')); ?>">
                            <?php echo e(__('messages.' . $event->event_type)); ?>

                        </span>
                    </td>
                    <td><?php echo e($event->traceRecord->tlc); ?></td>
                    <td><?php echo e($event->traceRecord->product->product_name); ?></td>
                    <td><?php echo e($event->location->location_name); ?></td>
                    <td><?php echo e($event->event_date->format('Y-m-d H:i')); ?></td>
                    <td><?php echo e($event->creator->full_name); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);"><?php echo e(__('messages.no_recent_events')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/dashboard.blade.php ENDPATH**/ ?>