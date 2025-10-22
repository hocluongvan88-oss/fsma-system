

<?php $__env->startSection('title', __('messages.traceability_results')); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">
            <?php echo e(__('messages.traceability_results_for')); ?>: <span style="color: var(--accent-primary);"><?php echo e($results['query_tlc']); ?></span>
        </h2>
        <div style="display: flex; gap: 0.75rem;">
            <a href="<?php echo e(route('reports.traceability')); ?>" class="btn btn-secondary">
                <?php echo e(__('messages.new_search')); ?>

            </a>
            <a href="<?php echo e(route('reports.traceability.export', ['tlc' => $results['query_tlc'], 'direction' => $results['direction']])); ?>" class="btn btn-secondary">
                <?php echo e(__('messages.export_csv')); ?>

            </a>
            <a href="<?php echo e(route('reports.traceability.export-pdf', ['tlc' => $results['query_tlc'], 'direction' => $results['direction']])); ?>" class="btn btn-primary">
                <?php echo e(__('messages.export_pdf')); ?>

            </a>
        </div>
    </div>

    <div style="padding: 1.5rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.current_record')); ?></h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.product')); ?></div>
                <div style="font-weight: 600;"><?php echo e($results['record']->product->product_name); ?></div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">SKU: <?php echo e($results['record']->product->sku); ?></div>
            </div>
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.quantity')); ?></div>
                <div style="font-weight: 600;">
                    <?php echo e($results['record']->quantity); ?> <?php echo e($results['record']->unit); ?>

                </div>
            </div>
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;"><?php echo e(__('messages.location')); ?></div>
                <div style="font-weight: 600;"><?php echo e($results['record']->location->location_name); ?></div>
            </div>
        </div>

        <?php if(isset($results['gs1_data'])): ?>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; color: var(--accent-primary);">
                <?php echo e(__('messages.gs1_standards')); ?>

            </h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.75rem;"><?php echo e(__('messages.gs1_digital_link')); ?></div>
                    <input type="text" class="form-input" value="<?php echo e($results['gs1_data']['digital_link']); ?>" readonly style="font-size: 0.75rem;">
                </div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.75rem;"><?php echo e(__('messages.gs1_128_barcode')); ?></div>
                    <input type="text" class="form-input" value="<?php echo e($results['gs1_data']['gs1_128']); ?>" readonly style="font-size: 0.75rem;">
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">
            <i class="bi bi-clock-history"></i> <?php echo e(__('messages.traceability_timeline')); ?>

        </h3>
        
        <div style="position: relative; padding-left: 30px;">
            <div style="position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: var(--border-color);"></div>
            
            <?php $__currentLoopData = $results['events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="position: relative; margin-bottom: 2rem;">
                <div style="position: absolute; left: -24px; top: 5px; width: 12px; height: 12px; border-radius: 50%; 
                    background: <?php echo e($event->event_type === 'receiving' ? 'var(--success)' : ($event->event_type === 'shipping' ? 'var(--warning)' : 'var(--info)')); ?>; 
                    border: 3px solid var(--bg-primary); box-shadow: 0 0 0 2px <?php echo e($event->event_type === 'receiving' ? 'var(--success)' : ($event->event_type === 'shipping' ? 'var(--warning)' : 'var(--info)')); ?>;"></div>
                
                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h6 style="font-weight: 600; margin-bottom: 0.5rem;">
                                <span class="badge badge-<?php echo e($event->event_type === 'receiving' ? 'success' : ($event->event_type === 'shipping' ? 'warning' : 'info')); ?>">
                                    <?php echo e(__('messages.' . $event->event_type)); ?>

                                </span>
                            </h6>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">
                                <?php echo e($event->event_date->format('F d, Y H:i')); ?>

                            </p>
                            <?php if($event->location): ?>
                            <p style="margin-bottom: 0.25rem;">
                                <i class="bi bi-geo-alt"></i> <?php echo e($event->location->location_name); ?>

                            </p>
                            <?php endif; ?>
                            <?php if($event->partner): ?>
                            <p style="margin-bottom: 0.25rem;">
                                <i class="bi bi-building"></i> <?php echo e($event->partner->partner_name); ?>

                            </p>
                            <?php endif; ?>
                            <?php if($event->notes): ?>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.5rem;"><?php echo e($event->notes); ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 600;"><?php echo e($event->quantity); ?> <?php echo e($event->unit); ?></div>
                            <div style="color: var(--text-secondary); font-size: 0.75rem;"><?php echo e(__('messages.created_by')); ?>: <?php echo e($event->creator->full_name); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <?php if(in_array($results['direction'], ['backward', 'both']) && count($results['backward']) > 0): ?>
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--success);">
            ← <?php echo e(__('messages.trace_backward')); ?> (<?php echo e(count($results['backward'])); ?> <?php echo e(__('messages.records')); ?>)
        </h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.event_type')); ?></th>
                        <th><?php echo e(__('messages.date')); ?></th>
                        <th><?php echo e(__('messages.from')); ?></th>
                        <th><?php echo e(__('messages.to')); ?></th>
                        <th><?php echo e(__('messages.product')); ?></th>
                        <th><?php echo e(__('messages.quantity')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $results['backward']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <span class="badge badge-info"><?php echo e(__('messages.' . $step['event']['type'])); ?></span>
                        </td>
                        <td><?php echo e(\Carbon\Carbon::parse($step['event']['date'])->format('Y-m-d H:i')); ?></td>
                        <td><?php echo e($step['from']['name'] ?? '-'); ?></td>
                        <td><?php echo e($step['to']['name'] ?? '-'); ?></td>
                        <td><?php echo e($step['record']['product']['name'] ?? '-'); ?></td>
                        <td><?php echo e($step['event']['quantity']); ?> <?php echo e($step['event']['unit']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if(in_array($results['direction'], ['forward', 'both']) && count($results['forward']) > 0): ?>
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--warning);">
            → <?php echo e(__('messages.trace_forward')); ?> (<?php echo e(count($results['forward'])); ?> <?php echo e(__('messages.records')); ?>)
        </h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.event_type')); ?></th>
                        <th><?php echo e(__('messages.date')); ?></th>
                        <th><?php echo e(__('messages.from')); ?></th>
                        <th><?php echo e(__('messages.to')); ?></th>
                        <th><?php echo e(__('messages.product')); ?></th>
                        <th><?php echo e(__('messages.quantity')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $results['forward']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <span class="badge badge-warning"><?php echo e(__('messages.' . $step['event']['type'])); ?></span>
                        </td>
                        <td><?php echo e(\Carbon\Carbon::parse($step['event']['date'])->format('Y-m-d H:i')); ?></td>
                        <td><?php echo e($step['from']['name'] ?? '-'); ?></td>
                        <td><?php echo e($step['to']['name'] ?? '-'); ?></td>
                        <td><?php echo e($step['record']['product']['name'] ?? '-'); ?></td>
                        <td><?php echo e($step['event']['quantity']); ?> <?php echo e($step['event']['unit']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/reports/traceability-result.blade.php ENDPATH**/ ?>