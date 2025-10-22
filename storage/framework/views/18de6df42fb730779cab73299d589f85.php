

<?php $__env->startSection('title', __('messages.user_management')); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;"><?php echo e(__('messages.all_users')); ?></h2>
        <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary"><?php echo e(__('messages.add_new_user')); ?></a>
    </div>

    
    <?php
        $currentUser = auth()->user();
        
        $query = \App\Models\User::where('is_active', true);
        
        // Apply organization filter
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'organization_id') && $currentUser->organization_id) {
            $query = $query->where('organization_id', $currentUser->organization_id);
        }
        
        // Exclude system admin from count for non-admin users
        if (!$currentUser->isAdmin()) {
            $query = $query->where('email', '!=', 'admin@fsma204.com');
        }
        
        $activeUserCount = $query->count();
        
        $packageLimits = [
            'free' => 1,
            'basic' => 1,
            'premium' => 3,
            'enterprise' => 999999,
        ];
        
        $userLimit = $currentUser->isAdmin() ? 999999 : ($packageLimits[$currentUser->package_id] ?? 1);
    ?>

    <?php if($userLimit < 999999): ?>
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem;">
            <strong><?php echo e(__('messages.current_package')); ?>:</strong> <?php echo e(ucfirst($currentUser->package_id)); ?> - 
            <strong><?php echo e(__('messages.users')); ?>:</strong> <?php echo e($activeUserCount); ?>/<?php echo e($userLimit); ?>

            <?php if($activeUserCount >= $userLimit): ?>
                <span style="color: var(--error); margin-left: 0.5rem;">⚠️ <?php echo e(__('messages.limit_reached')); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem;">
            <strong><?php echo e(__('messages.current_package')); ?>:</strong> <?php echo e(ucfirst($currentUser->package_id)); ?> - 
            <strong><?php echo e(__('messages.users')); ?>:</strong> <?php echo e($activeUserCount); ?>/<?php echo e(__('messages.unlimited')); ?>

        </div>
    </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('messages.username')); ?></th>
                    <th><?php echo e(__('messages.full_name')); ?></th>
                    <th><?php echo e(__('messages.email')); ?></th>
                    <th><?php echo e(__('messages.role')); ?></th>
                    <th><?php echo e(__('messages.status')); ?></th>
                    <th><?php echo e(__('messages.last_login')); ?></th>
                    <th><?php echo e(__('messages.actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($user->username); ?></td>
                    <td><?php echo e($user->full_name); ?></td>
                    <td><?php echo e($user->email); ?></td>
                    <td>
                        <span class="badge badge-info"><?php echo e(ucfirst($user->role)); ?></span>
                    </td>
                    <td>
                        <?php if($user->is_active): ?>
                            <span class="badge badge-success"><?php echo e(__('messages.active')); ?></span>
                        <?php else: ?>
                            <span class="badge badge-error"><?php echo e(__('messages.inactive')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($user->last_login ? $user->last_login->format('Y-m-d H:i') : __('messages.never')); ?></td>
                    <td>
                        <?php if(auth()->user()->isAdmin() || $user->role !== 'admin'): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                <?php echo e(__('messages.edit')); ?>

                            </a>
                            
                            <?php if($user->id !== auth()->id()): ?>
                            <form action="<?php echo e(route('admin.users.toggle-status', $user)); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                    <?php echo e($user->is_active ? __('messages.lock') : __('messages.unlock')); ?>

                                </button>
                            </form>
                            
                            <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" style="display: inline;" onsubmit="return confirm('<?php echo e(__('messages.confirm_delete_user')); ?>');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; background: var(--error);">
                                    <?php echo e(__('messages.delete')); ?>

                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.75rem;"><?php echo e(__('messages.no_access')); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted);"><?php echo e(__('messages.no_users_found')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php echo e($users->links()); ?>

    </div>
</div>

<style>
    @media (max-width: 768px) {
        .table-container table {
            font-size: 0.75rem;
        }
        
        .table-container td > div {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .table-container .btn {
            width: 100%;
            min-width: auto;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/users/index.blade.php ENDPATH**/ ?>