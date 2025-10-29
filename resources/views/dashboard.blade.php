@extends('layouts.app')

@section('title', __('messages.dashboard'))

@section('content')
{{-- Added trial status banner component --}}
<x-trial-status-banner />

{{-- Added translation support for package usage warning --}}
@if($packageStats['show_warning'])
<div class="card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; margin-bottom: 1.5rem; border: none;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="font-size: 2rem;">⚠️</div>
        <div style="flex: 1;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.usage_warning') }}</h3>
            <p style="margin: 0; opacity: 0.95;">
                {{ __('messages.usage_warning_message', [
                    'percentage' => number_format($packageStats['cte_percentage'], 1),
                    'used' => number_format($packageStats['cte_usage']),
                    'limit' => number_format($packageStats['cte_limit'])
                ]) }}
            </p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.users.edit', auth()->user()) }}" class="btn btn-light" style="background: white; color: #ff6b6b;">
            {{ __('messages.upgrade_package') }}
        </a>
        @endif
    </div>
</div>
@endif

{{-- Added translation support for package info card --}}
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 1.5rem; border: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">{{ __('messages.current_package') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700;">{{ $packageStats['package_name'] }}</div>
        </div>
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;">{{ __('messages.cte_records') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600;">
                    {{ number_format($packageStats['cte_usage']) }}/{{ number_format($packageStats['cte_limit']) }}
                </div>
                <div style="width: 100px; height: 4px; background: rgba(255,255,255,0.3); border-radius: 2px; margin-top: 0.25rem;">
                    <div style="width: {{ min($packageStats['cte_percentage'], 100) }}%; height: 100%; background: white; border-radius: 2px;"></div>
                </div>
            </div>
            @if($packageStats['document_limit'] < 999999)
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;">{{ __('messages.documents') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600;">
                    {{ $packageStats['document_count'] }}/{{ $packageStats['document_limit'] }}
                </div>
            </div>
            @endif
            @if($packageStats['user_limit'] < 999999)
            <div>
                <div style="font-size: 0.75rem; opacity: 0.8;">{{ __('messages.users') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600;">
                    {{ $packageStats['user_count'] }}/{{ $packageStats['user_limit'] }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Added translation support for stats grid --}}
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_products') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_products'] }}</div>
        <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem;">
            {{ $stats['ftl_products'] }} {{ __('messages.ftl_items') }}
        </div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.active_inventory') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['active_inventory'] }}</div>
        <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem;">
            {{ number_format($stats['total_inventory_qty'], 2) }} {{ __('messages.kg_total') }}
        </div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.locations') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_locations'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.partners') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_partners'] }}</div>
    </div>
</div>

{{-- Added translation support for recent events table --}}
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.recent_cte_events') }}</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.event_type') }}</th>
                    <th>{{ __('messages.tlc') }}</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.location') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.created_by') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['recent_events'] as $event)
                <tr>
                    <td>
                        <span class="badge badge-{{ $event->event_type === 'receiving' ? 'success' : ($event->event_type === 'shipping' ? 'warning' : 'info') }}">
                            {{ __('messages.' . $event->event_type) }}
                        </span>
                    </td>
                    <td>{{ $event->traceRecord?->tlc ?? 'N/A' }}</td>
                    <td>{{ $event->traceRecord?->product?->product_name ?? __('messages.product_deleted') }}</td>
                    <td>{{ $event->location?->location_name ?? 'N/A' }}</td>
                    <td>{{ $event->event_date->format('Y-m-d H:i') }}</td>
                    <td>{{ $event->creator?->full_name ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_recent_events') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
