

<?php $__env->startSection('title', __('messages.data_retention_policies')); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;"><?php echo e(__('messages.data_retention_policies')); ?></h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;"><?php echo e(__('messages.retention_description')); ?></p>
        </div>
        <button class="btn btn-primary" onclick="openCreatePolicyModal()">
            + <?php echo e(__('messages.create_policy')); ?>

        </button>
    </div>

    <?php if($errors->any()): ?>
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
            <ul style="margin-left: 1.5rem; color: var(--error);">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: var(--success);">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Statistics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dataType => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.75rem;"><?php echo e($stat['policy_name']); ?></h3>
                <div style="space-y: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.retention')); ?>:</span>
                        <span style="font-weight: 600;"><?php echo e($stat['retention_months']); ?> <?php echo e(__('messages.months')); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.records_to_delete')); ?>:</span>
                        <span style="color: var(--error); font-weight: 600;"><?php echo e($stat['records_to_delete']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.last_cleanup')); ?>:</span>
                        <span style="font-size: 0.875rem;">
                            <?php if($stat['last_cleanup']): ?>
                                <?php echo e($stat['last_cleanup']->format('M d, Y H:i')); ?>

                            <?php else: ?>
                                <span style="color: var(--text-muted);"><?php echo e(__('messages.never')); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Policies Table -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.active_policies')); ?></h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.policy_name')); ?></th>
                        <th><?php echo e(__('messages.data_type')); ?></th>
                        <th><?php echo e(__('messages.retention')); ?></th>
                        <th><?php echo e(__('messages.backup')); ?></th>
                        <th><?php echo e(__('messages.status')); ?></th>
                        <th><?php echo e(__('messages.last_executed')); ?></th>
                        <th><?php echo e(__('messages.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo e($policy->policy_name); ?></td>
                            <td style="color: var(--text-secondary);"><?php echo e(str_replace('_', ' ', ucfirst($policy->data_type))); ?></td>
                            <td><?php echo e($policy->retention_months); ?> <?php echo e(__('messages.months')); ?></td>
                            <td>
                                <?php if($policy->backup_before_deletion): ?>
                                    <span class="badge badge-success"><?php echo e(__('messages.enabled')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?php echo e(__('messages.disabled')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($policy->is_active): ?>
                                    <span class="badge badge-success"><?php echo e(__('messages.active')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?php echo e(__('messages.inactive')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.875rem; color: var(--text-secondary);">
                                <?php if($policy->last_executed_at): ?>
                                    <?php echo e($policy->last_executed_at->format('M d, Y H:i')); ?>

                                <?php else: ?>
                                    <span style="color: var(--text-muted);"><?php echo e(__('messages.never')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button onclick="executeCleanup(<?php echo e($policy->id); ?>)" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                        <?php echo e(__('messages.execute')); ?>

                                    </button>
                                    <button onclick="editPolicy(<?php echo e($policy->id); ?>)" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                        <?php echo e(__('messages.edit')); ?>

                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                <?php echo e(__('messages.no_policies_found')); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Logs -->
    <div>
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.recent_cleanup_logs')); ?></h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.data_type')); ?></th>
                        <th><?php echo e(__('messages.deleted')); ?></th>
                        <th><?php echo e(__('messages.backed_up')); ?></th>
                        <th><?php echo e(__('messages.status')); ?></th>
                        <th><?php echo e(__('messages.executed_at')); ?></th>
                        <th><?php echo e(__('messages.duration')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo e(str_replace('_', ' ', ucfirst($log->data_type))); ?></td>
                            <td><?php echo e($log->records_deleted); ?></td>
                            <td><?php echo e($log->records_backed_up); ?></td>
                            <td>
                                <?php if($log->status === 'success'): ?>
                                    <span class="badge badge-success"><?php echo e(__('messages.success')); ?></span>
                                <?php elseif($log->status === 'failed'): ?>
                                    <span class="badge badge-error"><?php echo e(__('messages.failed')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?php echo e(__('messages.partial')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo e($log->executed_at->format('M d, Y H:i:s')); ?></td>
                            <td style="font-size: 0.875rem; color: var(--text-secondary);"><?php echo e($log->duration_seconds); ?>s</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                <?php echo e(__('messages.no_cleanup_logs')); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Policy Modal -->
<div id="policyModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <h3 id="modalTitle" style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;"><?php echo e(__('messages.create_retention_policy')); ?></h3>
        
        <form id="policyForm" method="POST" action="<?php echo e(route('admin.retention.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="policyId" name="policy_id">
            <input type="hidden" id="methodField" name="_method" value="POST">
            
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.policy_name')); ?></label>
                <input type="text" id="policyName" name="policy_name" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.data_type')); ?></label>
                <select id="dataType" name="data_type" class="form-select" required>
                    <option value=""><?php echo e(__('messages.select_data_type')); ?></option>
                    <option value="trace_records"><?php echo e(__('messages.trace_records')); ?></option>
                    <option value="cte_events"><?php echo e(__('messages.cte_events')); ?></option>
                    <option value="audit_logs"><?php echo e(__('messages.audit_logs')); ?></option>
                    <option value="e_signatures"><?php echo e(__('messages.e_signatures')); ?></option>
                    <option value="error_logs"><?php echo e(__('messages.error_logs')); ?></option>
                    <option value="notifications"><?php echo e(__('messages.notifications')); ?></option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.retention_months')); ?></label>
                <input type="number" id="retentionMonths" name="retention_months" class="form-input" min="0" max="120" required>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="backupBeforeDeletion" name="backup_before_deletion" value="1" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <span style="color: var(--text-secondary);"><?php echo e(__('messages.backup_before_deletion')); ?></span>
                </label>
            </div>

            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.description')); ?></label>
                <textarea id="description" name="description" class="form-textarea" rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closePolicyModal()" class="btn btn-secondary">
                    <?php echo e(__('messages.cancel')); ?>

                </button>
                <button type="submit" class="btn btn-primary">
                    <?php echo e(__('messages.save_policy')); ?>

                </button>
            </div>
        </form>
    </div>
</div>

<!-- Execute Cleanup Modal -->
<div id="executeModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 400px;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.execute_cleanup')); ?></h3>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;"><?php echo e(__('messages.choose_execution_mode')); ?></p>
        
        <form id="executeForm" method="POST" data-base-url="<?php echo e(url('admin/retention')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="executePolicyId" name="policy_id">
            
            <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
                <label style="flex: 1; display: flex; align-items: center; cursor: pointer; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <input type="radio" name="dry_run" value="0" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <span><?php echo e(__('messages.execute')); ?></span>
                </label>
                <label style="flex: 1; display: flex; align-items: center; cursor: pointer; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <input type="radio" name="dry_run" value="1" style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <span><?php echo e(__('messages.dry_run')); ?></span>
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
function openCreatePolicyModal() {
    document.getElementById('modalTitle').textContent = '<?php echo e(__('messages.create_retention_policy')); ?>';
    document.getElementById('policyForm').action = '<?php echo e(route("admin.retention.store")); ?>';
    document.getElementById('methodField').value = 'POST';
    document.getElementById('policyId').value = '';
    document.getElementById('policyName').value = '';
    document.getElementById('dataType').value = '';
    document.getElementById('retentionMonths').value = '';
    document.getElementById('backupBeforeDeletion').checked = true;
    document.getElementById('description').value = '';
    document.getElementById('policyModal').style.display = 'flex';
}

function editPolicy(policyId) {
    fetch(`<?php echo e(route('admin.retention.edit', '')); ?>/${policyId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = '<?php echo e(__('messages.edit_retention_policy')); ?>';
            document.getElementById('policyForm').action = `<?php echo e(route('admin.retention.update', '')); ?>/${policyId}`;
            document.getElementById('methodField').value = 'PUT';
            
            document.getElementById('policyId').value = policyId;
            document.getElementById('policyName').value = data.policy_name;
            document.getElementById('dataType').value = data.data_type;
            document.getElementById('retentionMonths').value = data.retention_months;
            document.getElementById('backupBeforeDeletion').checked = data.backup_before_deletion;
            document.getElementById('description').value = data.description || '';
            document.getElementById('policyModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error fetching policy:', error);
            alert('Failed to load policy data');
        });
}

function closePolicyModal() {
    document.getElementById('policyModal').style.display = 'none';
}

function executeCleanup(policyId) {
    const baseUrl = document.getElementById('executeForm').dataset.baseUrl;
    const executeUrl = `${baseUrl}/${policyId}/execute`;
    
    if (!executeUrl.includes('/execute')) {
        console.error('[v0] Invalid execute URL:', executeUrl);
        alert('Error: Invalid policy ID. Please try again.');
        return;
    }
    
    document.getElementById('executeForm').action = executeUrl;
    document.getElementById('executePolicyId').value = policyId;
    
    document.getElementById('executeModal').style.display = 'flex';
}

function closeExecuteModal() {
    document.getElementById('executeModal').style.display = 'none';
}

document.getElementById('policyModal').addEventListener('click', function(e) {
    if (e.target === this) closePolicyModal();
});

document.getElementById('executeModal').addEventListener('click', function(e) {
    if (e.target === this) closeExecuteModal();
});

document.getElementById('executeForm').addEventListener('submit', function(e) {
    const action = this.action;
    
    if (!action || !action.includes('/execute')) {
        e.preventDefault();
        console.error('[v0] Form action not properly set:', action);
        alert('Error: Form configuration error. Please close and try again.');
        return false;
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/admin/retention/index.blade.php ENDPATH**/ ?>