

<?php $__env->startSection('title', __('messages.traceability_analytics')); ?>

<?php $__env->startSection('content'); ?>
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;"><?php echo e(__('messages.traceability_analytics')); ?></h2>
        <a href="<?php echo e(route('reports.traceability')); ?>" class="btn btn-secondary"><?php echo e(__('messages.back_to_reports')); ?></a>
    </div>

    <form method="GET" action="<?php echo e(route('reports.traceability.analytics')); ?>" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.date_from')); ?></label>
                <input type="date" name="date_from" class="form-input" value="<?php echo e($dateFrom); ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?php echo e(__('messages.date_to')); ?></label>
                <input type="date" name="date_to" class="form-input" value="<?php echo e($dateTo); ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo e(__('messages.apply_filters')); ?></button>
    </form>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;"><?php echo e(__('messages.total_queries')); ?></div>
            <div style="font-size: 2rem; font-weight: 700;"><?php echo e(number_format($queryStats->total_queries)); ?></div>
        </div>
        
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 0.5rem; color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;"><?php echo e(__('messages.unique_records')); ?></div>
            <div style="font-size: 2rem; font-weight: 700;"><?php echo e(number_format($queryStats->unique_records)); ?></div>
        </div>
        
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 0.5rem; color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;"><?php echo e(__('messages.unique_users')); ?></div>
            <div style="font-size: 2rem; font-weight: 700;"><?php echo e(number_format($queryStats->unique_ips)); ?></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.queries_by_day')); ?></h3>
            <canvas id="queriesByDayChart" style="max-height: 300px;"></canvas>
        </div>
        
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.query_types')); ?></h3>
            <canvas id="queryTypesChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;"><?php echo e(__('messages.most_queried_products')); ?></h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.rank')); ?></th>
                        <th><?php echo e(__('messages.product_name')); ?></th>
                        <th><?php echo e(__('messages.query_count')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong>#<?php echo e($index + 1); ?></strong></td>
                        <td><?php echo e($product->product_name); ?></td>
                        <td><?php echo e(number_format($product->query_count)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const queriesByDayData = <?php echo json_encode($queriesByDay, 15, 512) ?>;
    const queryTypesData = <?php echo json_encode($queryTypes, 15, 512) ?>;
    const directionsData = <?php echo json_encode($directions, 15, 512) ?>;

    new Chart(document.getElementById('queriesByDayChart'), {
        type: 'line',
        data: {
            labels: queriesByDayData.map(d => d.date),
            datasets: [{
                label: '<?php echo e(__('messages.queries')); ?>',
                data: queriesByDayData.map(d => d.count),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('queryTypesChart'), {
        type: 'doughnut',
        data: {
            labels: queryTypesData.map(d => d.query_type),
            datasets: [{
                data: queryTypesData.map(d => d.count),
                backgroundColor: ['#667eea', '#f093fb', '#4facfe']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/reports/traceability-analytics.blade.php ENDPATH**/ ?>