@extends('layouts.app')

@section('title', __('messages.void_management'))

@section('content')
<div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.void_management_dashboard') }}</h2>
        
        <!-- Statistics Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--danger);">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_voided_events') }}</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--danger);">{{ $totalVoidedCount }}</div>
            </div>
            
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--warning);">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.receiving_events_voided') }}</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--warning);">{{ $receivingVoidedCount }}</div>
            </div>
            
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--info);">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.transformation_events_voided') }}</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--info);">{{ $transformationVoidedCount }}</div>
            </div>
            
            <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--success);">
                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.shipping_events_voided') }}</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--success);">{{ $shippingVoidedCount }}</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div style="margin-bottom: 2rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.filters') }}</h3>
            
            <form method="GET" action="{{ route('cte.void-management') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.event_type') }}</label>
                    <select name="event_type" class="form-select">
                        <option value="">{{ __('messages.all_types') }}</option>
                        <option value="receiving" {{ request('event_type') == 'receiving' ? 'selected' : '' }}>{{ __('messages.receiving') }}</option>
                        <option value="transformation" {{ request('event_type') == 'transformation' ? 'selected' : '' }}>{{ __('messages.transformation') }}</option>
                        <option value="shipping" {{ request('event_type') == 'shipping' ? 'selected' : '' }}>{{ __('messages.shipping') }}</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.date_range') }}</label>
                    <input type="date" name="from_date" class="form-input" value="{{ request('from_date') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.to_date') }}</label>
                    <input type="date" name="to_date" class="form-input" value="{{ request('to_date') }}">
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.search_tlc') }}</label>
                    <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_by_tlc') }}" value="{{ request('search') }}">
                </div>
                
                <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">{{ __('messages.filter') }}</button>
                    <a href="{{ route('cte.void-management') }}" class="btn btn-secondary" style="flex: 1;">{{ __('messages.reset') }}</a>
                </div>
            </form>
        </div>

        <!-- Voided Events List -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-tertiary); border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('messages.tlc') }}</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('messages.event_type') }}</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('messages.product') }}</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('messages.voided_by') }}</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('messages.voided_at') }}</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary);">{{ __('messages.void_reason') }}</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--text-secondary);">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($voidedEvents as $event)
                    <tr style="border-bottom: 1px solid var(--border-color); hover: background var(--bg-tertiary);">
                        <td style="padding: 1rem;">
                            <strong>{{ $event->traceRecord->tlc }}</strong>
                        </td>
                        <td style="padding: 1rem;">
                            @if($event->event_type === 'receiving')
                                <span class="badge badge-success">{{ __('messages.receiving') }}</span>
                            @elseif($event->event_type === 'transformation')
                                <span class="badge badge-info">{{ __('messages.transformation') }}</span>
                            @elseif($event->event_type === 'shipping')
                                <span class="badge badge-warning">{{ __('messages.shipping') }}</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; color: var(--text-secondary);">
                            {{ $event->traceRecord->product->product_name ?? 'N/A' }}
                        </td>
                        <td style="padding: 1rem; color: var(--text-secondary);">
                            {{ $event->voidedBy->full_name ?? 'System' }}
                        </td>
                        <td style="padding: 1rem; color: var(--text-secondary);">
                            {{ $event->voided_at ? $event->voided_at->format('Y-m-d H:i') : 'N/A' }}
                        </td>
                        <td style="padding: 1rem; color: var(--text-secondary); font-size: 0.875rem;">
                            {{ $event->notes ? substr($event->notes, 0, 50) . '...' : 'N/A' }}
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <a href="{{ route('cte.' . $event->event_type . '.reentry', ['event' => $event->id]) }}" 
                               class="btn btn-sm btn-primary" 
                               style="font-size: 0.75rem;">
                                {{ __('messages.reentry') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            {{ __('messages.no_voided_events_found') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($voidedEvents->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
            {{ $voidedEvents->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
