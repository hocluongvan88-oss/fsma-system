@extends('layouts.app')

@section('title', __('messages.consumption_history'))

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">{{ __('messages.consumption_history') }}</h2>
        <a href="{{ route('reports.traceability') }}" class="btn btn-secondary">
            {{ __('messages.back_to_traceability') }}
        </a>
    </div>

    <div style="padding: 1.5rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.tlc_details') }}</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.tlc') }}</div>
                <div style="font-weight: 600;">{{ $traceRecord->tlc }}</div>
            </div>
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.product') }}</div>
                <div style="font-weight: 600;">{{ $traceRecord->product?->product_name ?? __('messages.product_deleted') }}</div>
            </div>
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.status') }}</div>
                <div>
                    <span class="badge badge-{{ $traceRecord->status === 'active' ? 'success' : 'info' }}">
                        {{ __('messages.' . $traceRecord->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem; border-left: 4px solid var(--accent-primary);">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.original_quantity') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-primary);">
                {{ $traceRecord->quantity }} {{ $traceRecord->unit }}
            </div>
        </div>
        
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem; border-left: 4px solid var(--success);">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.available_quantity') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">
                {{ $traceRecord->available_quantity }} {{ $traceRecord->unit }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                {{ number_format(($traceRecord->available_quantity / $traceRecord->quantity) * 100, 1) }}% {{ __('messages.remaining') }}
            </div>
        </div>
        
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem; border-left: 4px solid var(--warning);">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.consumed_quantity') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">
                {{ $traceRecord->consumed_quantity }} {{ $traceRecord->unit }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">
                {{ number_format(($traceRecord->consumed_quantity / $traceRecord->quantity) * 100, 1) }}% {{ __('messages.used') }}
            </div>
        </div>
    </div>

    <div style="margin-bottom: 1.5rem;">
        <div style="background: var(--bg-tertiary); border-radius: 0.5rem; height: 2rem; overflow: hidden; position: relative;">
            <div style="position: absolute; left: 0; top: 0; height: 100%; background: var(--success); width: {{ ($traceRecord->available_quantity / $traceRecord->quantity) * 100 }}%; transition: width 0.3s;"></div>
            <div style="position: absolute; left: {{ ($traceRecord->available_quantity / $traceRecord->quantity) * 100 }}%; top: 0; height: 100%; background: var(--warning); width: {{ ($traceRecord->consumed_quantity / $traceRecord->quantity) * 100 }}%; transition: width 0.3s;"></div>
            <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 600; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">
                {{ __('messages.available') }}: {{ number_format(($traceRecord->available_quantity / $traceRecord->quantity) * 100, 1) }}% | 
                {{ __('messages.consumed') }}: {{ number_format(($traceRecord->consumed_quantity / $traceRecord->quantity) * 100, 1) }}%
            </div>
        </div>
    </div>

    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.consumption_transactions') }}</h3>
    
    @if($transformationItems->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.transformation_event') }}</th>
                    <th>{{ __('messages.output_product') }}</th>
                    <th>{{ __('messages.quantity_used') }}</th>
                    <th>{{ __('messages.output_tlc') }}</th>
                    <th>{{ __('messages.location') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transformationItems as $item)
                <tr>
                    <td>{{ $item->transformation->event_date->format('Y-m-d H:i') }}</td>
                    <td>
                        <span class="badge badge-info">{{ __('messages.transformation') }}</span>
                    </td>
                    <td>{{ $item->transformation?->traceRecord?->product?->product_name ?? __('messages.product_deleted') }}</td>
                    <td style="font-weight: 600; color: var(--warning);">
                        {{ $item->quantity_used }} {{ $item->unit }}
                    </td>
                    <td><strong>{{ $item->transformation?->traceRecord?->tlc ?? 'N/A' }}</strong></td>
                    <td>{{ $item->transformation?->location?->location_name ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: 0.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 600;">{{ __('messages.total_consumed') }}:</span>
            <span style="font-size: 1.25rem; font-weight: 700; color: var(--warning);">
                {{ $transformationItems->sum('quantity_used') }} {{ $traceRecord->unit }}
            </span>
        </div>
    </div>
    @else
    <div style="text-align: center; color: var(--text-muted); padding: 3rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
        <div style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.no_consumption_yet') }}</div>
        <div style="font-size: 0.875rem;">{{ __('messages.this_tlc_has_not_been_used') }}</div>
    </div>
    @endif
</div>
@endsection
