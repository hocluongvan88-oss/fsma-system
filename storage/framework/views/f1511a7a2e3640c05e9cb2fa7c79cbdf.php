

<?php $__env->startSection('title', __('messages.data_archival_management')); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;"><?php echo e(__('messages.data_archival_management')); ?></h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                <?php echo e(__('messages.archival_description')); ?>

            </p>
        </div>
        <button class="btn btn-primary" onclick="openExecuteModal()">
            <?php echo e(__('messages.execute_archival')); ?>

        </button>
    </div>

    <?php if(session('success')): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: var(--success);">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: var(--error);">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Configuration Info -->
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 2rem;">
        <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 1rem;">
            <?php echo e(__('messages.archival_configuration')); ?>

        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.strategy')); ?>:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;"><?php echo e(ucfirst($config['strategy'])); ?></span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.hot_data_period')); ?>:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;"><?php echo e($config['hot_data_months']); ?> <?php echo e(__('messages.months')); ?></span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.batch_size')); ?>:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;"><?php echo e(number_format($config['batch_size'])); ?></span>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                <?php echo e(__('messages.total_operations')); ?>

            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo e(number_format($stats['total_archival_operations'])); ?></p>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                <?php echo e(__('messages.records_archived')); ?>

            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;"><?php echo e(number_format($stats['total_records_archived'])); ?></p>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                <?php echo e(__('messages.success_rate')); ?>

            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;">
                <?php if($stats['total_archival_operations'] > 0): ?>
                    <?php echo e(number_format(($stats['successful_operations'] / $stats['total_archival_operations']) * 100, 1)); ?>%
                <?php else: ?>
                    0%
                <?php endif; ?>
            </p>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                <?php echo e(__('messages.last_archival')); ?>

            </h3>
            <p style="font-size: 1rem; font-weight: 600; margin: 0;">
                <?php if($stats['last_archival']): ?>
                    <?php echo e($stats['last_archival']->executed_at->diffForHumans()); ?>

                <?php else: ?>
                    <?php echo e(__('messages.never')); ?>

                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Data Type Statistics -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.data_type_statistics')); ?></h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            <?php $__currentLoopData = $dataTypeStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dataType => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin: 0;">
                            <?php echo e($stat['name']); ?>

                        </h4>
                        <?php if($config['strategy'] === 'database'): ?>
                            <a href="<?php echo e(route('admin.archival.view', $dataType)); ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                <?php echo e(__('messages.view')); ?>

                            </a>
                        <?php endif; ?>
                    </div>
                    <div style="space-y: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.hot_records')); ?>:</span>
                            <span style="font-weight: 600; color: var(--success);"><?php echo e(number_format($stat['hot_records'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.cold_records')); ?>:</span>
                            <span style="font-weight: 600; color: var(--warning);"><?php echo e(number_format($stat['cold_records'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.total_archived')); ?>:</span>
                            <span style="font-weight: 600;"><?php echo e(number_format($stat['total_archived'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.last_archival')); ?>:</span>
                            <span style="font-size: 0.875rem;">
                                <?php if($stat['last_archival']): ?>
                                    <?php echo e($stat['last_archival']->format('M d, Y')); ?>

                                <?php else: ?>
                                    <span style="color: var(--text-muted);"><?php echo e(__('messages.never')); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Recent Archival Logs -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0;"><?php echo e(__('messages.recent_archival_logs')); ?></h3>
            <a href="<?php echo e(route('admin.archival.logs')); ?>" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                <?php echo e(__('messages.view_all_logs')); ?>

            </a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.data_type')); ?></th>
                        <th><?php echo e(__('messages.strategy')); ?></th>
                        <th><?php echo e(__('messages.archived')); ?></th>
                        <th><?php echo e(__('messages.verified')); ?></th>
                        <th><?php echo e(__('messages.deleted')); ?></th>
                        <th><?php echo e(__('messages.status')); ?></th>
                        <th><?php echo e(__('messages.executed_at')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo e(ucfirst(str_replace('_', ' ', $log->data_type))); ?></td>
                            <td style="color: var(--text-secondary);"><?php echo e(ucfirst($log->strategy)); ?></td>
                            <td><?php echo e(number_format($log->records_archived)); ?></td>
                            <td><?php echo e(number_format($log->records_verified)); ?></td>
                            <td><?php echo e(number_format($log->records_deleted_from_hot)); ?></td>
                            <td>
                                <?php if($log->status === 'success'): ?>
                                    <span class="badge badge-success"><?php echo e(__('messages.success')); ?></span>
                                <?php elseif($log->status === 'failed'): ?>
                                    <span class="badge badge-error"><?php echo e(__('messages.failed')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?php echo e(__('messages.partial')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo e($log->executed_at->format('M d, Y H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                <?php echo e(__('messages.no_archival_logs')); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Execute Archival Modal -->
<div id="executeModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 500px;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.execute_archival')); ?></h3>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
            <?php echo e(__('messages.archival_warning')); ?>

        </p>
        
        <form method="POST" action="<?php echo e(route('admin.archival.execute')); ?>">
            <?php echo csrf_field(); ?>
            
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0.75rem;">
                    <input type="radio" name="dry_run" value="1" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <div>
                        <div style="font-weight: 600;"><?php echo e(__('messages.dry_run')); ?></div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.dry_run_description')); ?></div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="radio" name="dry_run" value="0" style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <div>
                        <div style="font-weight: 600;"><?php echo e(__('messages.execute')); ?></div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.execute_description')); ?></div>
                    </div>
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeExecuteModal()" class="btn btn-secondary">
                    <?php echo e(__('messages.cancel')); ?>

                </button>
                <button type="submit" class="btn btn-primary">
                    <?php echo e(__('messages.proceed')); ?>

                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openExecuteModal() {
    document.getElementById('executeModal').style.display = 'flex';
}

function closeExecuteModal() {
    document.getElementById('executeModal').style.display = 'none';
}

document.getElementById('executeModal').addEventListener('click', function(e) {
    if (e.target === this) closeExecuteModal();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/admin/archival/index.blade.php ENDPATH**/ ?>