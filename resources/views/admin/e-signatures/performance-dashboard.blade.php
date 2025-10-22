@extends('layouts.app')

@section('title', 'E-Signature Performance Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Performance Metrics Dashboard</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-primary" onclick="refreshMetrics()">Refresh</button>
            <select id="periodFilter" class="form-select" onchange="filterByPeriod(this.value)" style="width: auto;">
                <option value="day">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
    </div>

    <!-- Performance Alerts -->
    <div id="alertsContainer" style="margin-bottom: 1.5rem;"></div>

    <!-- Key Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--accent-primary);">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">Avg Creation Time</p>
            <p style="font-size: 1.5rem; font-weight: 600; margin: 0;" id="avgCreationTime">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.5rem 0 0 0;" id="creationTimeStatus">-</p>
        </div>

        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--success);">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">Avg Verification Time</p>
            <p style="font-size: 1.5rem; font-weight: 600; margin: 0;" id="avgVerificationTime">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.5rem 0 0 0;" id="verificationTimeStatus">-</p>
        </div>

        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--warning);">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">Success Rate</p>
            <p style="font-size: 1.5rem; font-weight: 600; margin: 0;" id="successRate">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.5rem 0 0 0;">Total Signatures</p>
        </div>

        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--error);">
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">Total Errors</p>
            <p style="font-size: 1.5rem; font-weight: 600; margin: 0;" id="totalErrors">-</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.5rem 0 0 0;" id="errorStatus">-</p>
        </div>
    </div>

    <!-- Bottleneck Analysis -->
    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0;">Bottleneck Analysis</h3>
        <div id="bottleneckAnalysis" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <p style="color: var(--text-muted);">Loading...</p>
        </div>
    </div>

    <!-- TSA Performance -->
    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0;">TSA Provider Performance</h3>
        <div class="table-container" style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Requests</th>
                        <th>Avg Response Time</th>
                        <th>Success Rate</th>
                        <th>Retries</th>
                    </tr>
                </thead>
                <tbody id="tsaPerformanceTable">
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TSA Health Status -->
    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0;">TSA Health Status</h3>
        <div id="tsaHealthStatus" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <p style="color: var(--text-muted);">Loading...</p>
        </div>
    </div>
</div>

<style>
.form-select {
    padding: 0.5rem 0.75rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    font-size: 0.875rem;
}

.form-select:focus {
    outline: none;
    border-color: var(--accent-primary);
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--accent-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-hover);
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-healthy {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.status-unhealthy {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--error);
}

.status-unreachable {
    background-color: rgba(107, 114, 128, 0.1);
    color: var(--text-muted);
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-warning {
    background-color: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: var(--warning);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: var(--error);
}
</style>

<script>
async function refreshMetrics() {
    const period = document.getElementById('periodFilter').value;
    
    try {
        const response = await fetch(`{{ route('admin.e-signatures.performance-metrics') }}?period=${period}`);
        const data = await response.json();
        
        // Update key metrics
        document.getElementById('avgCreationTime').textContent = data.average_creation_time_ms + 'ms';
        document.getElementById('creationTimeStatus').textContent = 
            data.average_creation_time_ms > 5000 ? '⚠️ High' : '✓ Normal';
        
        document.getElementById('avgVerificationTime').textContent = data.average_verification_time_ms + 'ms';
        document.getElementById('verificationTimeStatus').textContent = 
            data.average_verification_time_ms > 2000 ? '⚠️ High' : '✓ Normal';
        
        document.getElementById('successRate').textContent = data.success_rate + '%';
        document.getElementById('totalErrors').textContent = data.total_errors;
        document.getElementById('errorStatus').textContent = 
            data.success_rate < 95 ? '⚠️ Check logs' : '✓ Healthy';
        
        // Update bottleneck analysis
        updateBottleneckAnalysis(data.bottleneck_analysis);
        
        // Update TSA performance
        updateTSAPerformance(data.tsa_performance);
        
        // Update alerts
        updateAlerts(data.alerts);
        
    } catch (error) {
        console.error('Error fetching metrics:', error);
    }
}

function updateBottleneckAnalysis(bottlenecks) {
    const container = document.getElementById('bottleneckAnalysis');
    
    if (!bottlenecks || bottlenecks.length === 0) {
        container.innerHTML = '<p style="color: var(--text-muted);">No bottlenecks detected</p>';
        return;
    }
    
    container.innerHTML = bottlenecks.map(b => `
        <div style="background: var(--bg-tertiary); padding: 0.75rem; border-radius: 0.5rem;">
            <p style="font-weight: 600; margin: 0 0 0.5rem 0;">${b.component}</p>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                ${b.average_time_ms}ms (${b.average_percentage}%)
            </p>
        </div>
    `).join('');
}

function updateTSAPerformance(tsaData) {
    const tbody = document.getElementById('tsaPerformanceTable');
    
    if (!tsaData || tsaData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No data</td></tr>';
        return;
    }
    
    tbody.innerHTML = tsaData.map(tsa => {
        const successRate = ((tsa.success_count / tsa.total_requests) * 100).toFixed(1);
        return `
            <tr>
                <td>${tsa.provider}</td>
                <td>${tsa.total_requests}</td>
                <td>${tsa.average_response_time_ms}ms</td>
                <td>${successRate}%</td>
                <td>${tsa.retry_count}</td>
            </tr>
        `;
    }).join('');
}

function updateAlerts(alerts) {
    const container = document.getElementById('alertsContainer');
    
    if (!alerts || alerts.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    container.innerHTML = alerts.map(alert => `
        <div class="alert alert-${alert.level}">
            ${alert.message}
        </div>
    `).join('');
}

function filterByPeriod(period) {
    refreshMetrics();
}

// Initial load
document.addEventListener('DOMContentLoaded', refreshMetrics);

// Auto-refresh every 30 seconds
setInterval(refreshMetrics, 30000);
</script>
@endsection
