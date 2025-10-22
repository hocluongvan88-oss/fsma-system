

<?php $__env->startSection('title', __('messages.archived_data') . ' - ' . ucfirst(str_replace('_', ' ', $dataType))); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                <?php echo e(__('messages.archived_data')); ?>: <?php echo e(ucfirst(str_replace('_', ' ', $dataType))); ?>

            </h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                <?php echo e(__('messages.archived_data_description')); ?>

            </p>
        </div>
        <a href="<?php echo e(route('admin.archival.index')); ?>" class="btn btn-secondary">
            <?php echo e(__('messages.back_to_dashboard')); ?>

        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.original_id')); ?></th>
                    <th><?php echo e(__('messages.archived_at')); ?></th>
                    <th><?php echo e(__('messages.created_at')); ?></th>
                    <th><?php echo e(__('messages.details')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $archivedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="font-family: monospace;">#<?php echo e($record->original_id); ?></td>
                        <td style="font-size: 0.875rem;"><?php echo e(\Carbon\Carbon::parse($record->archived_at)->format('M d, Y H:i')); ?></td>
                        <td style="font-size: 0.875rem;"><?php echo e(\Carbon\Carbon::parse($record->created_at)->format('M d, Y H:i')); ?></td>
                        <td>
                            <button onclick="viewDetails(<?php echo e(json_encode($record)); ?>)" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                <?php echo e(__('messages.view_details')); ?>

                            </button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            <?php echo e(__('messages.no_archived_data')); ?>

                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        <?php echo e($archivedData->links()); ?>

    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.record_details')); ?></h3>
        <pre id="detailsContent" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; overflow-x: auto; font-size: 0.875rem;"></pre>
        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
            <button onclick="closeDetailsModal()" class="btn btn-secondary">
                <?php echo e(__('messages.close')); ?>

            </button>
        </div>
    </div>
</div>

<script>
function viewDetails(record) {
    document.getElementById('detailsContent').textContent = JSON.stringify(record, null, 2);
    document.getElementById('detailsModal').style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetailsModal();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/admin/archival/view.blade.php ENDPATH**/ ?>