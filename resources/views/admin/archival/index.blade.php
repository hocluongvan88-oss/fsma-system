@extends('layouts.app')

@section('title', __('messages.data_archival_management'))

@section('content')
<div class="card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">{{ __('messages.data_archival_management') }}</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                {{ __('messages.archival_description') }}
            </p>
        </div>
        <button class="btn btn-primary" onclick="openExecuteModal()">
            {{ __('messages.execute_archival') }}
        </button>
    </div>

    @if (session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: var(--error);">
            {{ session('error') }}
        </div>
    @endif

    <!-- Configuration Info -->
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 2rem;">
        <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 1rem;">
            {{ __('messages.archival_configuration') }}
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.strategy') }}:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ ucfirst($config['strategy']) }}</span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.hot_data_period') }}:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ $config['hot_data_months'] }} {{ __('messages.months') }}</span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.batch_size') }}:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ number_format($config['batch_size']) }}</span>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                {{ __('messages.total_operations') }}
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;">{{ number_format($stats['total_archival_operations']) }}</p>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                {{ __('messages.records_archived') }}
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;">{{ number_format($stats['total_records_archived']) }}</p>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                {{ __('messages.success_rate') }}
            </h3>
            <p style="font-size: 2rem; font-weight: 700; margin: 0;">
                @if ($stats['total_archival_operations'] > 0)
                    {{ number_format(($stats['successful_operations'] / $stats['total_archival_operations']) * 100, 1) }}%
                @else
                    0%
                @endif
            </p>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 0.5rem; padding: 1.25rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                {{ __('messages.last_archival') }}
            </h3>
            <p style="font-size: 1rem; font-weight: 600; margin: 0;">
                @if ($stats['last_archival'])
                    {{ $stats['last_archival']->executed_at->diffForHumans() }}
                @else
                    {{ __('messages.never') }}
                @endif
            </p>
        </div>
    </div>

    <!-- Data Type Statistics -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.data_type_statistics') }}</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            @foreach ($dataTypeStats as $dataType => $stat)
                <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin: 0;">
                            {{ $stat['name'] }}
                        </h4>
                        @if ($config['strategy'] === 'database')
                            <a href="{{ route('admin.archival.view', $dataType) }}" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                {{ __('messages.view') }}
                            </a>
                        @endif
                    </div>
                    <div style="space-y: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.hot_records') }}:</span>
                            <span style="font-weight: 600; color: var(--success);">{{ number_format($stat['hot_records']) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.cold_records') }}:</span>
                            <span style="font-weight: 600; color: var(--warning);">{{ number_format($stat['cold_records']) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.total_archived') }}:</span>
                            <span style="font-weight: 600;">{{ number_format($stat['total_archived']) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.last_archival') }}:</span>
                            <span style="font-size: 0.875rem;">
                                @if ($stat['last_archival'])
                                    {{ $stat['last_archival']->format('M d, Y') }}
                                @else
                                    <span style="color: var(--text-muted);">{{ __('messages.never') }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Archival Logs -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">{{ __('messages.recent_archival_logs') }}</h3>
            <a href="{{ route('admin.archival.logs') }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                {{ __('messages.view_all_logs') }}
            </a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.data_type') }}</th>
                        <th>{{ __('messages.strategy') }}</th>
                        <th>{{ __('messages.archived') }}</th>
                        <th>{{ __('messages.verified') }}</th>
                        <th>{{ __('messages.deleted') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.executed_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr>
                            <td style="font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $log->data_type)) }}</td>
                            <td style="color: var(--text-secondary);">{{ ucfirst($log->strategy) }}</td>
                            <td>{{ number_format($log->records_archived) }}</td>
                            <td>{{ number_format($log->records_verified) }}</td>
                            <td>{{ number_format($log->records_deleted_from_hot) }}</td>
                            <td>
                                @if ($log->status === 'success')
                                    <span class="badge badge-success">{{ __('messages.success') }}</span>
                                @elseif ($log->status === 'failed')
                                    <span class="badge badge-error">{{ __('messages.failed') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('messages.partial') }}</span>
                                @endif
                            </td>
                            <td style="font-size: 0.875rem;">{{ $log->executed_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                {{ __('messages.no_archival_logs') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Execute Archival Modal -->
<div id="executeModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 500px;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.execute_archival') }}</h3>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
            {{ __('messages.archival_warning') }}
        </p>
        
        <form method="POST" action="{{ route('admin.archival.execute') }}">
            @csrf
            
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0.75rem;">
                    <input type="radio" name="dry_run" value="1" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <div>
                        <div style="font-weight: 600;">{{ __('messages.dry_run') }}</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.dry_run_description') }}</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="radio" name="dry_run" value="0" style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <div>
                        <div style="font-weight: 600;">{{ __('messages.execute') }}</div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.execute_description') }}</div>
                    </div>
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeExecuteModal()" class="btn btn-secondary">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('messages.proceed') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openExecuteModal() {
    document.getElementById('executeModal').style.display = 'flex';
}

function closeExecuteModal() {
    document.getElementById('executeModal').style.display = 'none';
}

document.getElementById('executeModal').addEventListener('click', function(e) {
    if (e.target === this) closeExecuteModal();
});
</script>
@endsection
