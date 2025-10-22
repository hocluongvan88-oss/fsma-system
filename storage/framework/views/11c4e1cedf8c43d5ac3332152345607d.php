

<?php $__env->startSection('title', __('messages.e_signature_performance_dashboard')); ?>

<?php $__env->startSection('content'); ?>
<div style="padding: 0;">
    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
        <div style="display: flex; gap: 0.75rem;">
            <button class="btn btn-primary" onclick="refreshMetrics()" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36M20.49 15a9 9 0 0 1-14.85 3.36"></path>
                </svg>
                <?php echo e(__('messages.refresh')); ?>

            </button>
            <select id="periodFilter" class="form-select" onchange="filterByPeriod(this.value)" style="width: auto; padding: 0.625rem 1rem;">
                <option value="day"><?php echo e(__('messages.today')); ?></option>
                <option value="week"><?php echo e(__('messages.this_week')); ?></option>
                <option value="month"><?php echo e(__('messages.this_month')); ?></option>
            </select>
        </div>
    </div>

    <!-- Alerts Container -->
    <div id="alertsContainer" style="margin-bottom: 1.5rem;"></div>

    <!-- Key Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <!-- Total Signatures -->
        <div class="card" style="border-left: 4px solid var(--accent-primary); padding: 1.5rem;">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.75rem 0; font-weight: 500;"><?php echo e(__('messages.total_signatures')); ?></p>
            <p style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--accent-primary);" id="totalSignatures">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.75rem 0 0 0;" id="totalSignaturesChange">-</p>
        </div>

        <!-- Success Rate -->
        <div class="card" style="border-left: 4px solid var(--success); padding: 1.5rem;">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.75rem 0; font-weight: 500;"><?php echo e(__('messages.success_rate')); ?></p>
            <p style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--success);" id="successRate">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.75rem 0 0 0;" id="successRateStatus">-</p>
        </div>

        <!-- Avg Creation Time -->
        <div class="card" style="border-left: 4px solid var(--warning); padding: 1.5rem;">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.75rem 0; font-weight: 500;"><?php echo e(__('messages.avg_creation_time')); ?></p>
            <p style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--warning);" id="avgCreationTime">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.75rem 0 0 0;" id="creationTimeStatus">-</p>
        </div>

        <!-- Avg Verification Time -->
        <div class="card" style="border-left: 4px solid #8b5cf6; padding: 1.5rem;">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.75rem 0; font-weight: 500;"><?php echo e(__('messages.avg_verification_time')); ?></p>
            <p style="font-size: 2rem; font-weight: 700; margin: 0; color: #8b5cf6;" id="avgVerificationTime">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.75rem 0 0 0;" id="verificationTimeStatus">-</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Performance Chart -->
        <div class="card">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1.5rem 0;"><?php echo e(__('messages.performance_trend')); ?></h3>
            <canvas id="performanceChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Status Distribution -->
        <div class="card">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1.5rem 0;"><?php echo e(__('messages.status_distribution')); ?></h3>
            <canvas id="statusChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Recent Signatures Table -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0;"><?php echo e(__('messages.recent_signatures_last_10')); ?></h3>
            <a href="<?php echo e(route('admin.e-signatures.index')); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.5rem 1rem;"><?php echo e(__('messages.view_all')); ?></a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?php echo e(__('messages.id')); ?></th>
                        <th><?php echo e(__('messages.record_type')); ?></th>
                        <th><?php echo e(__('messages.user')); ?></th>
                        <th><?php echo e(__('messages.status')); ?></th>
                        <th><?php echo e(__('messages.created_at')); ?></th>
                        <th><?php echo e(__('messages.signed_at')); ?></th>
                        <th><?php echo e(__('messages.action')); ?></th>
                    </tr>
                </thead>
                <tbody id="recentSignaturesTable">
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;"><?php echo e(__('messages.loading')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Performance Metrics Details -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
        <!-- Bottleneck Analysis -->
        <div class="card">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1.5rem 0;"><?php echo e(__('messages.bottleneck_analysis')); ?></h3>
            <div id="bottleneckAnalysis" style="space-y: 1rem;">
                <p style="color: var(--text-muted);"><?php echo e(__('messages.loading')); ?></p>
            </div>
        </div>

        <!-- Error Summary -->
        <div class="card">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1.5rem 0;"><?php echo e(__('messages.error_summary')); ?></h3>
            <div id="errorSummary" style="space-y: 1rem;">
                <p style="color: var(--text-muted);"><?php echo e(__('messages.loading')); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.form-select {
    padding: 0.625rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.form-select:hover {
    border-color: var(--accent-primary);
}

.form-select:focus {
    outline: none;
    border-color: var(--accent-primary);
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background: var(--accent-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-hover);
}

.btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-secondary);
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-completed {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.status-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.status-failed {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--error);
}

.status-revoked {
    background-color: rgba(107, 114, 128, 0.1);
    color: var(--text-muted);
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    border-left: 4px solid;
}

.alert-warning {
    background-color: rgba(245, 158, 11, 0.1);
    border-left-color: var(--warning);
    color: var(--warning);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    border-left-color: var(--error);
    color: var(--error);
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    border-left-color: var(--success);
    color: var(--success);
}

#bottleneckAnalysis > div,
#errorSummary > div {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: var(--bg-tertiary);
    border-radius: 0.5rem;
}

#bottleneckAnalysis > div:last-child,
#errorSummary > div:last-child {
    margin-bottom: 0;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let performanceChart = null;
let statusChart = null;

async function refreshMetrics() {
    const period = document.getElementById('periodFilter').value;
    
    try {
        const response = await fetch(`/admin/e-signatures/performance-metrics?period=${period}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        console.log('[v0] API Response:', result);
        
        if (!result.success) {
            showAlert('Error loading metrics: ' + (result.message || 'Unknown error'), 'error');
            return;
        }
        
        const data = result.data;
        
        // Update metrics with null safety
        document.getElementById('totalSignatures').textContent = data.total_signatures || 0;
        document.getElementById('totalSignaturesChange').textContent = 
            `${data.signatures_this_period || 0} this period`;
        
        document.getElementById('successRate').textContent = (data.success_rate || 0).toFixed(1) + '%';
        document.getElementById('successRateStatus').textContent = 
            data.success_rate >= 95 ? '✓ Excellent' : data.success_rate >= 90 ? '⚠️ Good' : '❌ Needs attention';
        
        document.getElementById('avgCreationTime').textContent = 
            formatTime(data.average_creation_time || 0);
        document.getElementById('creationTimeStatus').textContent = 
            data.average_creation_time > 5000 ? '⚠️ High' : '✓ Normal';
        
        document.getElementById('avgVerificationTime').textContent = 
            formatTime(data.average_verification_time || 0);
        document.getElementById('verificationTimeStatus').textContent = 
            data.average_verification_time > 2000 ? '⚠️ High' : '✓ Normal';
        
        // Update charts
        updatePerformanceChart(data.performance_trend || []);
        updateStatusChart(data.status_distribution || {});
        
        // Update recent signatures
        updateRecentSignatures(data.recent_signatures || []);
        
        // Update bottleneck analysis
        updateBottleneckAnalysis(data.bottleneck_analysis || []);
        
        // Update error summary
        updateErrorSummary(data.error_summary || {});
        
        // Update alerts
        updateAlerts(data.alerts || []);
        
    } catch (error) {
        console.error('[v0] Error fetching metrics:', error);
        showAlert('Failed to load metrics: ' + error.message, 'error');
    }
}

function formatTime(ms) {
    if (ms < 1000) return ms.toFixed(0) + 'ms';
    return (ms / 1000).toFixed(2) + 's';
}

function updatePerformanceChart(data) {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    if (performanceChart) {
        performanceChart.destroy();
    }
    
    const labels = data.map(d => d.date || d.label || '');
    const successRates = data.map(d => d.success_rate || 0);
    const avgTimes = data.map(d => (d.average_time || 0) / 100);
    
    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Success Rate (%)',
                    data: successRates,
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.2)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y',
                    borderWidth: 3,
                    pointBackgroundColor: '#34d399',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Avg Time (ms/100)',
                    data: avgTimes,
                    borderColor: '#60a5fa',
                    backgroundColor: 'rgba(96, 165, 250, 0.2)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                    borderWidth: 3,
                    pointBackgroundColor: '#60a5fa',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Success Rate (%)',
                        color: '#9ca3af',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    },
                    ticks: {
                        color: '#9ca3af',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Avg Time (ms/100)',
                        color: '#9ca3af',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    },
                    ticks: {
                        color: '#9ca3af',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    ticks: {
                        color: '#9ca3af',
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(156, 163, 175, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#d1d5db',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleColor: '#f3f4f6',
                    bodyColor: '#d1d5db',
                    borderColor: 'rgba(156, 163, 175, 0.2)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6
                }
            }
        }
    });
}

function updateStatusChart(distribution) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    if (statusChart) {
        statusChart.destroy();
    }
    
    const labels = Object.keys(distribution);
    const values = Object.values(distribution);
    
    const colors = {
        'completed': '#34d399',
        'pending': '#fbbf24',
        'failed': '#f87171',
        'revoked': '#94a3b8'
    };
    
    const hoverColors = {
        'completed': '#6ee7b7',
        'pending': '#fcd34d',
        'failed': '#fca5a5',
        'revoked': '#cbd5e1'
    };
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
            datasets: [{
                data: values,
                backgroundColor: labels.map(l => colors[l] || '#60a5fa'),
                hoverBackgroundColor: labels.map(l => hoverColors[l] || '#93c5fd'),
                borderColor: '#1f2937',
                borderWidth: 3,
                hoverBorderColor: '#374151',
                hoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#d1d5db',
                        padding: 20,
                        font: {
                            size: 13,
                            weight: '500'
                        },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 12,
                        boxHeight: 12
                    },
                    position: 'bottom'
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleColor: '#f3f4f6',
                    bodyColor: '#d1d5db',
                    borderColor: 'rgba(156, 163, 175, 0.2)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 6,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
}

function updateRecentSignatures(signatures) {
    const tbody = document.getElementById('recentSignaturesTable');
    
    if (!signatures || signatures.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No signatures found</td></tr>';
        return;
    }
    
    tbody.innerHTML = signatures.slice(0, 10).map(sig => {
        const status = sig.status || (sig.signed_at ? 'completed' : 'pending');
        const statusClass = `status-${status}`;
        const createdAt = sig.created_at ? new Date(sig.created_at).toLocaleString() : '-';
        const signedAt = sig.signed_at ? new Date(sig.signed_at).toLocaleString() : '-';
        
        return `
            <tr>
                <td>#${sig.id}</td>
                <td>${sig.record_type || '-'}</td>
                <td>${sig.user_name || '-'}</td>
                <td><span class="status-badge ${statusClass}">${status}</span></td>
                <td>${createdAt}</td>
                <td>${signedAt}</td>
                <td>
                    <a href="/admin/e-signatures/${sig.id}" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.375rem 0.75rem;">View</a>
                </td>
            </tr>
        `;
    }).join('');
}

function updateBottleneckAnalysis(bottlenecks) {
    const container = document.getElementById('bottleneckAnalysis');
    
    if (!bottlenecks || bottlenecks.length === 0) {
        container.innerHTML = '<p style="color: var(--text-muted); margin: 0;">No bottlenecks detected</p>';
        return;
    }
    
    container.innerHTML = bottlenecks.map(b => `
        <div>
            <p style="font-weight: 600; margin: 0 0 0.25rem 0; color: var(--text-primary);">${b.component || 'Unknown'}</p>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                ${formatTime(b.average_time || 0)} (${(b.percentage || 0).toFixed(1)}%)
            </p>
        </div>
    `).join('');
}

function updateErrorSummary(errors) {
    const container = document.getElementById('errorSummary');
    
    if (!errors || Object.keys(errors).length === 0) {
        container.innerHTML = '<p style="color: var(--text-muted); margin: 0;">No errors</p>';
        return;
    }
    
    container.innerHTML = Object.entries(errors).map(([type, count]) => `
        <div>
            <p style="font-weight: 600; margin: 0 0 0.25rem 0; color: var(--error);">${type}</p>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">${count} occurrence${count !== 1 ? 's' : ''}</p>
        </div>
    `).join('');
}

function updateAlerts(alerts) {
    const container = document.getElementById('alertsContainer');
    
    if (!alerts || alerts.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="alert alert-${alert.level || 'warning'}">
            ${alert.message || alert}
        </div>
    `).join('');
}

function filterByPeriod(period) {
    refreshMetrics();
}

function showAlert(message, level = 'warning') {
    const container = document.getElementById('alertsContainer');
    const alertHtml = `<div class="alert alert-${level}">${message}</div>`;
    container.innerHTML = alertHtml;
}

// Initial load
document.addEventListener('DOMContentLoaded', refreshMetrics);

// Auto-refresh every 30 seconds
setInterval(refreshMetrics, 30000);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/fxiasdcg/root/resources/views/admin/e-signatures/index.blade.php ENDPATH**/ ?>