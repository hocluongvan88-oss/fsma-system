@extends('layouts.app')

@section('title', __('messages.retention_logs'))

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Retention Cleanup Logs</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                Complete audit trail of all retention policy executions
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button onclick="exportLogs()" class="btn btn-secondary">Export CSV</button>
            <button onclick="openFilterModal()" class="btn btn-primary">Filter</button>
        </div>
    </div>

    <!-- Filter Summary -->
    @if($hasFilters)
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            @if($filters['data_type'])
                <span class="badge badge-info">Data Type: {{ ucfirst(str_replace('_', ' ', $filters['data_type'])) }}</span>
            @endif
            @if($filters['status'])
                <span class="badge badge-info">Status: {{ ucfirst($filters['status']) }}</span>
            @endif
            @if($filters['date_from'])
                <span class="badge badge-info">From: {{ $filters['date_from'] }}</span>
            @endif
            @if($filters['date_to'])
                <span class="badge badge-info">To: {{ $filters['date_to'] }}</span>
            @endif
        </div>
        <a href="{{ route('admin.retention.logs') }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
            Clear Filters
        </a>
    </div>
    @endif

    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">
                Total Executions
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;">{{ number_format($stats['total_executions']) }}</p>
        </div>
        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">
                Records Deleted
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--error);">{{ number_format($stats['total_deleted']) }}</p>
        </div>
        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">
                Success Rate
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--success);">{{ number_format($stats['success_rate'], 1) }}%</p>
        </div>
        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">
                Backups Created
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;">{{ number_format($stats['total_backed_up']) }}</p>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data Type</th>
                    <th>Deleted</th>
                    <th>Backed Up</th>
                    <th>Status</th>
                    <th>Executed By</th>
                    <th>Executed At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-family: monospace; color: var(--text-secondary);">#{{ $log->id }}</td>
                    <td style="font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $log->data_type)) }}</td>
                    <td style="color: var(--error); font-weight: 600;">{{ number_format($log->records_deleted) }}</td>
                    <td>{{ number_format($log->records_backed_up) }}</td>
                    <td>
                        @if($log->status === 'success')
                            <span class="badge badge-success">Success</span>
                        @elseif($log->status === 'failed')
                            <span class="badge badge-error">Failed</span>
                        @else
                            <span class="badge badge-warning">Partial</span>
                        @endif
                    </td>
                    <td style="font-size: 0.875rem;">{{ $log->executed_by_user->full_name ?? 'System' }}</td>
                    <td style="font-size: 0.875rem; color: var(--text-secondary);">
                        {{ $log->executed_at->format('M d, Y H:i:s') }}
                    </td>
                    <td>
                        <button onclick="viewLogDetails({{ $log->id }})" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                            View Details
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        No logs found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        {{ $logs->links() }}
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 500px;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Filter Logs</h3>
        <form method="GET" action="{{ route('admin.retention.logs') }}">
            <div class="form-group">
                <label class="form-label">Data Type</label>
                <select name="data_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="error_logs" {{ request('data_type') === 'error_logs' ? 'selected' : '' }}>Error Logs</option>
                    <option value="notifications" {{ request('data_type') === 'notifications' ? 'selected' : '' }}>Notifications</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeFilterModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Log Details</h3>
        <div id="logDetailsContent"></div>
        <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
            <button onclick="closeLogDetailsModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<script>
function openFilterModal() {
    document.getElementById('filterModal').style.display = 'flex';
}

function closeFilterModal() {
    document.getElementById('filterModal').style.display = 'none';
}

function viewLogDetails(logId) {
    fetch(`/admin/retention/logs/${logId}`)
        .then(r => r.json())
        .then(data => {
            const content = `
                <div style="space-y: 1rem;">
                    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
                        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 0.75rem;">
                            <div style="color: var(--text-secondary);">Data Type:</div>
                            <div style="font-weight: 600;">${data.data_type}</div>
                            <div style="color: var(--text-secondary);">Records Deleted:</div>
                            <div style="font-weight: 600; color: var(--error);">${data.records_deleted.toLocaleString()}</div>
                            <div style="color: var(--text-secondary);">Records Backed Up:</div>
                            <div style="font-weight: 600;">${data.records_backed_up.toLocaleString()}</div>
                            <div style="color: var(--text-secondary);">Status:</div>
                            <div><span class="badge badge-${data.status === 'success' ? 'success' : 'error'}">${data.status}</span></div>
                            <div style="color: var(--text-secondary);">Executed By:</div>
                            <div>${data.executed_by}</div>
                            <div style="color: var(--text-secondary);">Executed At:</div>
                            <div>${data.executed_at}</div>
                        </div>
                    </div>
                    ${data.error_message ? `
                        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem; color: var(--error);">Error Message:</div>
                            <div style="font-family: monospace; font-size: 0.875rem;">${data.error_message}</div>
                        </div>
                    ` : ''}
                    ${data.backup_path ? `
                        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">Backup Location:</div>
                            <div style="font-family: monospace; font-size: 0.875rem; color: var(--text-secondary);">${data.backup_path}</div>
                        </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('logDetailsContent').innerHTML = content;
            document.getElementById('logDetailsModal').style.display = 'flex';
        })
        .catch(e => alert('Failed to load log details'));
}

function closeLogDetailsModal() {
    document.getElementById('logDetailsModal').style.display = 'none';
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = `{{ route('admin.retention.logs') }}?${params.toString()}`;
}

document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) closeFilterModal();
});

document.getElementById('logDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) closeLogDetailsModal();
});
</script>
@endsection
