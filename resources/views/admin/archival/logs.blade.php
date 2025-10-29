@extends('layouts.app')

@section('title', __('messages.archival_logs'))

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">{{ __('messages.archival_logs') }}</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                {{ __('messages.archival_logs_description') }}
            </p>
        </div>
        <a href="{{ route('admin.archival.index') }}" class="btn btn-secondary">
            {{ __('messages.back_to_dashboard') }}
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.id') }}</th>
                    <th>{{ __('messages.data_type') }}</th>
                    <th>{{ __('messages.strategy') }}</th>
                    <th>{{ __('messages.archived') }}</th>
                    <th>{{ __('messages.verified') }}</th>
                    <th>{{ __('messages.deleted') }}</th>
                    <th>{{ __('messages.location') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.executed_by') }}</th>
                    <th>{{ __('messages.executed_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="font-family: monospace; color: var(--text-secondary);">#{{ $log->id }}</td>
                        <td style="font-weight: 500;">{{ ucfirst(str_replace('_', ' ', $log->data_type)) }}</td>
                        <td>{{ ucfirst($log->strategy) }}</td>
                        <td>{{ number_format($log->records_archived) }}</td>
                        <td>{{ number_format($log->records_verified) }}</td>
                        <td>{{ number_format($log->records_deleted_from_hot) }}</td>
                        <td style="font-size: 0.75rem; color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->archival_location }}">
                            {{ $log->archival_location }}
                        </td>
                        <td>
                            @if ($log->status === 'success')
                                <span class="badge badge-success">{{ __('messages.success') }}</span>
                            @elseif ($log->status === 'failed')
                                <span class="badge badge-error">{{ __('messages.failed') }}</span>
                            @else
                                <span class="badge badge-warning">{{ __('messages.partial') }}</span>
                            @endif
                        </td>
                        <td style="font-size: 0.875rem;">
                            @if ($log->executed_by)
                                {{ $log->executed_by }}
                            @else
                                <span style="color: var(--text-muted);">{{ __('messages.system') }}</span>
                            @endif
                        </td>
                        <td style="font-size: 0.875rem;">{{ $log->executed_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            {{ __('messages.no_archival_logs') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
