@extends('layouts.app')

@section('title', __('messages.kde_compliance_report'))

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('messages.kde_compliance_report') }}</h1>
    <p style="color: var(--text-secondary);">{{ __('messages.fsma_204_key_data_elements_validation') }}</p>
</div>

<!-- KDE Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_events_checked') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_events'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.fully_compliant') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);">{{ $stats['compliant_events'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.with_warnings') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">{{ $stats['warning_events'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.non_compliant') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger);">{{ $stats['non_compliant_events'] }}</div>
    </div>
</div>

<!-- KDE Breakdown -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.kde_breakdown') }}</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.kde_name') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.compliance_rate') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kdeBreakdown as $kde)
                <tr>
                    <td><strong>{{ $kde['name'] }}</strong></td>
                    <td style="font-size: 0.875rem; color: var(--text-secondary);">{{ $kde['description'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex: 1; height: 0.5rem; background: var(--bg-tertiary); border-radius: 0.25rem; overflow: hidden;">
                                <div style="height: 100%; background: {{ $kde['rate'] >= 80 ? 'var(--success)' : ($kde['rate'] >= 60 ? 'var(--warning)' : 'var(--danger)') }}; width: {{ $kde['rate'] }}%;"></div>
                            </div>
                            <span style="font-weight: 600; min-width: 3rem;">{{ $kde['rate'] }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $kde['rate'] >= 80 ? 'badge-success' : ($kde['rate'] >= 60 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $kde['rate'] >= 80 ? __('messages.compliant') : ($kde['rate'] >= 60 ? __('messages.warning') : __('messages.non_compliant')) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Events with KDE Status -->
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.recent_events_kde_status') }}</h2>
    
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        @forelse($recentEvents as $event)
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid {{ $event['kde_status'] === 'compliant' ? 'var(--success)' : ($event['kde_status'] === 'warning' ? 'var(--warning)' : 'var(--danger)') }};">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                <div>
                    <strong>{{ $event['tlc'] }}</strong>
                    <span class="badge badge-info" style="margin-left: 0.5rem;">{{ $event['event_type'] }}</span>
                </div>
                <span class="badge {{ $event['kde_status'] === 'compliant' ? 'badge-success' : ($event['kde_status'] === 'warning' ? 'badge-warning' : 'badge-danger') }}">
                    {{ $event['kde_status'] === 'compliant' ? __('messages.compliant') : ($event['kde_status'] === 'warning' ? __('messages.warning') : __('messages.non_compliant')) }}
                </span>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                {{ $event['product_name'] }} | {{ $event['event_date'] }}
            </div>
            @if($event['missing_kdes'] && count($event['missing_kdes']) > 0)
            <div style="font-size: 0.8rem; color: var(--danger);">
                {{ __('messages.missing_kdes') }}: {{ implode(', ', $event['missing_kdes']) }}
            </div>
            @endif
        </div>
        @empty
        <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
            {{ __('messages.no_events_found') }}
        </div>
        @endforelse
    </div>
</div>
@endsection
