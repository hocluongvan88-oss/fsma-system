@extends('layouts.app')

@section('title', __('messages.audit_log'))

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('messages.audit_log') }}</h1>
    <p style="color: var(--text-secondary);">{{ __('messages.comprehensive_system_audit_trail') }}</p>
</div>

<!-- Audit Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_audit_logs') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_logs'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.last_24_hours') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['logs_24h'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.critical_events') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger);">{{ $stats['critical_events'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.unique_users') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['unique_users'] }}</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.filters') }}</h2>
    
    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="form-group">
            <label class="form-label">{{ __('messages.search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="{{ __('messages.search_by_user_action') }}">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.action_type') }}</label>
            <select name="action_type" class="form-select">
                <option value="">{{ __('messages.all') }}</option>
                <option value="create" {{ request('action_type') == 'create' ? 'selected' : '' }}>{{ __('messages.create') }}</option>
                <option value="update" {{ request('action_type') == 'update' ? 'selected' : '' }}>{{ __('messages.update') }}</option>
                <option value="delete" {{ request('action_type') == 'delete' ? 'selected' : '' }}>{{ __('messages.delete') }}</option>
                <option value="sign" {{ request('action_type') == 'sign' ? 'selected' : '' }}>{{ __('messages.sign') }}</option>
                <option value="verify" {{ request('action_type') == 'verify' ? 'selected' : '' }}>{{ __('messages.verify') }}</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.severity') }}</label>
            <select name="severity" class="form-select">
                <option value="">{{ __('messages.all') }}</option>
                <option value="info" {{ request('severity') == 'info' ? 'selected' : '' }}>{{ __('messages.info') }}</option>
                <option value="warning" {{ request('severity') == 'warning' ? 'selected' : '' }}>{{ __('messages.warning') }}</option>
                <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>{{ __('messages.critical') }}</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.date_from') }}</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.date_to') }}</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">{{ __('messages.filter') }}</button>
            <a href="{{ route('reports.audit-log') }}" class="btn btn-secondary" style="flex: 1;">{{ __('messages.reset') }}</a>
        </div>
    </form>
</div>

<!-- Audit Log Table -->
<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.timestamp') }}</th>
                    <th>{{ __('messages.user') }}</th>
                    <th>{{ __('messages.action') }}</th>
                    <th>{{ __('messages.entity_type') }}</th>
                    <th>{{ __('messages.entity_id') }}</th>
                    <th>{{ __('messages.severity') }}</th>
                    <th>{{ __('messages.details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                <tr>
                    <td style="font-size: 0.875rem;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <strong>{{ $log->user->full_name ?? 'System' }}</strong>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $log->user->email ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $log->action }}</span>
                    </td>
                    <td style="font-size: 0.875rem;">{{ $log->entity_type }}</td>
                    <td style="font-size: 0.875rem; font-family: monospace;">{{ $log->entity_id }}</td>
                    <td>
                        <span class="badge {{ $log->severity === 'critical' ? 'badge-danger' : ($log->severity === 'warning' ? 'badge-warning' : 'badge-success') }}">
                            {{ $log->severity }}
                        </span>
                    </td>
                    <td style="font-size: 0.875rem; color: var(--text-secondary);">
                        {{ Str::limit($log->description, 50) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        {{ __('messages.no_audit_logs_found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 1.5rem;">
        {{ $auditLogs->links() }}
    </div>
</div>
@endsection
