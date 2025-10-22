@extends('layouts.app')

@section('title', __('messages.traceability_results'))

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">
            {{ __('messages.traceability_results_for') }}: <span style="color: var(--accent-primary);">{{ $results['query_tlc'] }}</span>
        </h2>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('reports.traceability') }}" class="btn btn-secondary">
                {{ __('messages.new_search') }}
            </a>
            <a href="{{ route('reports.traceability.export', ['tlc' => $results['query_tlc'], 'direction' => $results['direction']]) }}" class="btn btn-secondary">
                {{ __('messages.export_csv') }}
            </a>
            <a href="{{ route('reports.traceability.export-pdf', ['tlc' => $results['query_tlc'], 'direction' => $results['direction']]) }}" class="btn btn-primary">
                {{ __('messages.export_pdf') }}
            </a>
        </div>
    </div>

    <div style="padding: 1.5rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.current_record') }}</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.product') }}</div>
                <div style="font-weight: 600;">{{ $results['record']->product->product_name }}</div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">SKU: {{ $results['record']->product->sku }}</div>
            </div>
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.quantity') }}</div>
                <div style="font-weight: 600;">
                    {{ $results['record']->quantity }} {{ $results['record']->unit }}
                </div>
            </div>
            <div>
                <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.location') }}</div>
                <div style="font-weight: 600;">{{ $results['record']->location->location_name }}</div>
            </div>
        </div>

        @if(isset($results['gs1_data']))
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; color: var(--accent-primary);">
                {{ __('messages.gs1_standards') }}
            </h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.75rem;">{{ __('messages.gs1_digital_link') }}</div>
                    <input type="text" class="form-input" value="{{ $results['gs1_data']['digital_link'] }}" readonly style="font-size: 0.75rem;">
                </div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.75rem;">{{ __('messages.gs1_128_barcode') }}</div>
                    <input type="text" class="form-input" value="{{ $results['gs1_data']['gs1_128'] }}" readonly style="font-size: 0.75rem;">
                </div>
            </div>
        </div>
        @endif
    </div>

    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">
            <i class="bi bi-clock-history"></i> {{ __('messages.traceability_timeline') }}
        </h3>
        
        <div style="position: relative; padding-left: 30px;">
            <div style="position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: var(--border-color);"></div>
            
            @foreach($results['events'] as $event)
            <div style="position: relative; margin-bottom: 2rem;">
                <div style="position: absolute; left: -24px; top: 5px; width: 12px; height: 12px; border-radius: 50%; 
                    background: {{ $event->event_type === 'receiving' ? 'var(--success)' : ($event->event_type === 'shipping' ? 'var(--warning)' : 'var(--info)') }}; 
                    border: 3px solid var(--bg-primary); box-shadow: 0 0 0 2px {{ $event->event_type === 'receiving' ? 'var(--success)' : ($event->event_type === 'shipping' ? 'var(--warning)' : 'var(--info)') }};"></div>
                
                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h6 style="font-weight: 600; margin-bottom: 0.5rem;">
                                <span class="badge badge-{{ $event->event_type === 'receiving' ? 'success' : ($event->event_type === 'shipping' ? 'warning' : 'info') }}">
                                    {{ __('messages.' . $event->event_type) }}
                                </span>
                            </h6>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">
                                {{ $event->event_date->format('F d, Y H:i') }}
                            </p>
                            @if($event->location)
                            <p style="margin-bottom: 0.25rem;">
                                <i class="bi bi-geo-alt"></i> {{ $event->location->location_name }}
                            </p>
                            @endif
                            @if($event->partner)
                            <p style="margin-bottom: 0.25rem;">
                                <i class="bi bi-building"></i> {{ $event->partner->partner_name }}
                            </p>
                            @endif
                            @if($event->notes)
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.5rem;">{{ $event->notes }}</p>
                            @endif
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 600;">{{ $event->quantity }} {{ $event->unit }}</div>
                            <div style="color: var(--text-secondary); font-size: 0.75rem;">{{ __('messages.created_by') }}: {{ $event->creator->full_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if(in_array($results['direction'], ['backward', 'both']) && count($results['backward']) > 0)
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--success);">
            ← {{ __('messages.trace_backward') }} ({{ count($results['backward']) }} {{ __('messages.records') }})
        </h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.event_type') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.from') }}</th>
                        <th>{{ __('messages.to') }}</th>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.quantity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results['backward'] as $step)
                    <tr>
                        <td>
                            <span class="badge badge-info">{{ __('messages.' . $step['event']['type']) }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($step['event']['date'])->format('Y-m-d H:i') }}</td>
                        <td>{{ $step['from']['name'] ?? '-' }}</td>
                        <td>{{ $step['to']['name'] ?? '-' }}</td>
                        <td>{{ $step['record']['product']['name'] ?? '-' }}</td>
                        <td>{{ $step['event']['quantity'] }} {{ $step['event']['unit'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(in_array($results['direction'], ['forward', 'both']) && count($results['forward']) > 0)
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--warning);">
            → {{ __('messages.trace_forward') }} ({{ count($results['forward']) }} {{ __('messages.records') }})
        </h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.event_type') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.from') }}</th>
                        <th>{{ __('messages.to') }}</th>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.quantity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results['forward'] as $step)
                    <tr>
                        <td>
                            <span class="badge badge-warning">{{ __('messages.' . $step['event']['type']) }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($step['event']['date'])->format('Y-m-d H:i') }}</td>
                        <td>{{ $step['from']['name'] ?? '-' }}</td>
                        <td>{{ $step['to']['name'] ?? '-' }}</td>
                        <td>{{ $step['record']['product']['name'] ?? '-' }}</td>
                        <td>{{ $step['event']['quantity'] }} {{ $step['event']['unit'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
