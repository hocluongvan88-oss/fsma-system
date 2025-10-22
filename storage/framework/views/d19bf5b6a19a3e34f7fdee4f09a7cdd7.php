

<?php $__env->startSection('title', __('messages.package_management')); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;"><?php echo e(__('messages.package_management')); ?></h2>
        <div style="color: var(--text-muted); font-size: 0.875rem;">
            <?php echo e(__('messages.manage_pricing_features_packages')); ?>

        </div>
    </div>

    <?php if(session('success')): ?>
    <div style="background: var(--success); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <div style="display: grid; gap: 1.5rem;">
        <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card" style="background: var(--card-bg); border: 2px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">
                        <?php echo e($package->name); ?>

                        <?php if(!$package->is_visible): ?>
                        <span style="background: var(--text-muted); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">
                            <?php echo e(__('messages.hidden')); ?>

                        </span>
                        <?php endif; ?>
                        <?php if(!$package->is_selectable): ?>
                        <span style="background: var(--warning); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">
                            <?php echo e(__('messages.not_selectable')); ?>

                        </span>
                        <?php endif; ?>
                    </h3>
                    <p style="color: var(--text-secondary); margin: 0;"><?php echo e($package->description); ?></p>
                </div>
                <a href="<?php echo e(route('admin.packages.edit', $package)); ?>" class="btn btn-primary">
                    <?php echo e(__('messages.edit_package')); ?>

                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;"><?php echo e(__('messages.cte_records_month')); ?></div>
                    <div style="font-size: 1.125rem; font-weight: 600;">
                        <?php echo e($package->hasUnlimitedCte() ? __('messages.unlimited') : number_format($package->max_cte_records_monthly)); ?>

                    </div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;"><?php echo e(__('messages.documents')); ?></div>
                    <div style="font-size: 1.125rem; font-weight: 600;">
                        <?php echo e($package->hasUnlimitedDocuments() ? __('messages.unlimited') : number_format($package->max_documents)); ?>

                    </div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;"><?php echo e(__('messages.users')); ?></div>
                    <div style="font-size: 1.125rem; font-weight: 600;">
                        <?php echo e($package->hasUnlimitedUsers() ? __('messages.unlimited') : $package->max_users); ?>

                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;"><?php echo e(__('messages.monthly_pricing')); ?></div>
                    <?php if($package->monthly_selling_price): ?>
                    <div style="display: flex; align-items: baseline; gap: 0.5rem;">
                        <?php if($package->monthly_list_price && $package->monthly_list_price > $package->monthly_selling_price): ?>
                        <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.875rem;">
                            $<?php echo e(number_format($package->monthly_list_price, 2)); ?>

                        </span>
                        <?php endif; ?>
                        <span style="font-size: 1.5rem; font-weight: 700;">
                            $<?php echo e(number_format($package->monthly_selling_price, 2)); ?>

                        </span>
                        <span style="color: var(--text-muted);"><?php echo e(__('messages.per_month')); ?></span>
                    </div>
                    <?php if($package->getMonthlyDiscount()): ?>
                    <div style="color: var(--success); font-size: 0.75rem; margin-top: 0.25rem;">
                        <?php echo e(__('messages.save_percent', ['percent' => number_format($package->getMonthlyDiscount(), 0)])); ?>

                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div style="color: var(--text-muted);"><?php echo e(__('messages.free')); ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;"><?php echo e(__('messages.yearly_pricing')); ?></div>
                    <?php if($package->yearly_selling_price): ?>
                    <div style="display: flex; align-items: baseline; gap: 0.5rem;">
                        <?php if($package->yearly_list_price && $package->yearly_list_price > $package->yearly_selling_price): ?>
                        <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.875rem;">
                            $<?php echo e(number_format($package->yearly_list_price, 2)); ?>

                        </span>
                        <?php endif; ?>
                        <span style="font-size: 1.5rem; font-weight: 700;">
                            $<?php echo e(number_format($package->yearly_selling_price, 2)); ?>

                        </span>
                        <span style="color: var(--text-muted);"><?php echo e(__('messages.per_year')); ?></span>
                    </div>
                    <?php if($package->getYearlySavings()): ?>
                    <div style="color: var(--success); font-size: 0.75rem; margin-top: 0.25rem;">
                        <?php echo e(__('messages.save_percent_vs_monthly', ['percent' => number_format($package->getYearlySavings(), 0)])); ?>

                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div style="color: var(--text-muted);"><?php echo e(__('messages.free')); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($package->show_promotion && $package->promotion_text): ?>
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-weight: 600;">
                <?php echo e($package->promotion_text); ?>

            </div>
            <?php endif; ?>

            <?php if($package->features): ?>
            <div>
                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;"><?php echo e(__('messages.features')); ?></div>
                <ul style="list-style: none; padding: 0; margin: 0; display: grid; gap: 0.5rem;">
                    <?php $__currentLoopData = $package->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li style="display: flex; align-items: start; gap: 0.5rem;">
                        <span style="color: var(--success); margin-top: 0.125rem;">✓</span>
                        <span><?php echo e($feature); ?></span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/admin/packages/index.blade.php ENDPATH**/ ?>