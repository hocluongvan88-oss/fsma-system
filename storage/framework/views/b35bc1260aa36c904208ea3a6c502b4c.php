

<?php $__env->startSection('title', __('messages.audit_log')); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="form-group" style="margin: 0;">
            <label class="form-label"><?php echo e(__('messages.user')); ?></label>
            <select name="user_id" class="form-select">
                <option value=""><?php echo e(__('messages.all_users')); ?></option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                        <?php echo e($user->full_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label"><?php echo e(__('messages.table')); ?></label>
            <select name="table_name" class="form-select">
                <option value=""><?php echo e(__('messages.all_tables')); ?></option>
                <?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($table); ?>" <?php echo e(request('table_name') === $table ? 'selected' : ''); ?>>
                        <?php echo e($table); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label"><?php echo e(__('messages.start_date')); ?></label>
            <input type="date" name="start_date" class="form-input" value="<?php echo e(request('start_date')); ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label"><?php echo e(__('messages.end_date')); ?></label>
            <input type="date" name="end_date" class="form-input" value="<?php echo e(request('end_date')); ?>">
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label"><?php echo e(__('messages.search_action')); ?></label>
            <input type="text" name="search" class="form-input" value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('messages.search')); ?>">
        </div>
        
        <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary"><?php echo e(__('messages.filter')); ?></button>
            <?php if(request()->hasAny(['user_id', 'table_name', 'start_date', 'end_date', 'search'])): ?>
                <a href="<?php echo e(route('reports.audit-log')); ?>" class="btn btn-secondary"><?php echo e(__('messages.clear')); ?></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.timestamp')); ?></th>
                    <th><?php echo e(__('messages.user')); ?></th>
                    <th><?php echo e(__('messages.action')); ?></th>
                    <th><?php echo e(__('messages.table')); ?></th>
                    <th><?php echo e(__('messages.record_id')); ?></th>
                    <th><?php echo e(__('messages.ip_address')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr style="cursor: pointer;" onclick="viewAuditDetails(<?php echo e($log->id); ?>)">
                    <td><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                    <td><?php echo e($log->user?->full_name ?? __('messages.system')); ?></td>
                    <td><code style="font-size: 0.75rem;"><?php echo e($log->action); ?></code></td>
                    <td><?php echo e($log->table_name ?? '-'); ?></td>
                    <td><?php echo e($log->record_id ?? '-'); ?></td>
                    <td style="font-size: 0.75rem; color: var(--text-muted);"><?php echo e($log->ip_address); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);"><?php echo e(__('messages.no_audit_logs_found')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php echo e($logs->links('vendor.pagination.custom')); ?>

</div>

<!-- Translate modal for audit details -->
<div id="auditModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; padding: 2rem;">
    <div style="max-width: 800px; margin: 0 auto; background: var(--bg-secondary); border-radius: 1rem; padding: 2rem; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600;"><?php echo e(__('messages.audit_log_details')); ?></h2>
            <button onclick="closeAuditModal()" class="btn btn-secondary"><?php echo e(__('messages.close')); ?></button>
        </div>
        <div id="auditDetails"></div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove any standalone large SVG elements
    const allSvgs = document.querySelectorAll('svg');
    allSvgs.forEach(svg => {
        const width = svg.getAttribute('width') || svg.clientWidth;
        const height = svg.getAttribute('height') || svg.clientHeight;
        
        // If SVG is larger than 50px, hide it
        if (width > 50 || height > 50) {
            svg.style.display = 'none';
            console.log('[v0] Removed oversized SVG:', width, height);
        }
    });
});

function viewAuditDetails(id) {
    fetch(`/reports/audit-log/${id}`)
        .then(res => res.json())
        .then(data => {
            const log = data.log;
            const changes = data.changes;
            
            let html = `
                <div style="display: grid; gap: 1rem;">
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.timestamp')); ?></div>
                        <div style="font-weight: 600;">${log.created_at}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.user')); ?></div>
                        <div style="font-weight: 600;">${log.user?.full_name || '<?php echo e(__('messages.system')); ?>'}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.action')); ?></div>
                        <div style="font-weight: 600;">${log.action}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.table_record')); ?></div>
                        <div style="font-weight: 600;">${log.table_name || '-'} / ${log.record_id || '-'}</div>
                    </div>
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.ip_address')); ?></div>
                        <div style="font-weight: 600;">${log.ip_address}</div>
                    </div>
            `;
            
            if (Object.keys(changes).length > 0) {
                html += `
                    <div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;"><?php echo e(__('messages.changes')); ?></div>
                        <table style="width: 100%; font-size: 0.875rem;">
                            <thead>
                                <tr style="background: var(--bg-tertiary);">
                                    <th style="padding: 0.5rem;"><?php echo e(__('messages.field')); ?></th>
                                    <th style="padding: 0.5rem;"><?php echo e(__('messages.old_value')); ?></th>
                                    <th style="padding: 0.5rem;"><?php echo e(__('messages.new_value')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                for (const [field, change] of Object.entries(changes)) {
                    html += `
                        <tr>
                            <td style="padding: 0.5rem;"><strong>${field}</strong></td>
                            <td style="padding: 0.5rem; color: var(--error);">${change.old || '-'}</td>
                            <td style="padding: 0.5rem; color: var(--success);">${change.new || '-'}</td>
                        </tr>
                    `;
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            html += '</div>';
            
            document.getElementById('auditDetails').innerHTML = html;
            document.getElementById('auditModal').style.display = 'block';
        });
}

function closeAuditModal() {
    document.getElementById('auditModal').style.display = 'none';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/reports/audit-log.blade.php ENDPATH**/ ?>