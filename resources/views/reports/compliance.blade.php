@extends('layouts.app')

@section('title', __('messages.compliance_report'))

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.compliance_score') }}</div>
        <div style="font-size: 3rem; font-weight: 700; color: {{ $complianceScore >= 80 ? 'var(--success)' : ($complianceScore >= 60 ? 'var(--warning)' : 'var(--error)') }};">
            {{ $complianceScore }}%
        </div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.ftl_products') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_products'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_cte_events') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_cte_events'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.audit_logs_30d') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['recent_audit_logs'] }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.cte_events_breakdown') }}</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.receiving_events') }}</span>
                <span class="badge badge-success">{{ $stats['receiving_events'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.transformation_events') }}</span>
                <span class="badge badge-info">{{ $stats['transformation_events'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.shipping_events') }}</span>
                <span class="badge badge-warning">{{ $stats['shipping_events'] }}</span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.trace_records_status') }}</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.total_records') }}</span>
                <span class="badge badge-info">{{ $stats['total_trace_records'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.active_records') }}</span>
                <span class="badge badge-success">{{ $stats['active_records'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.processed_shipped') }}</span>
                <span class="badge badge-warning">{{ $stats['total_trace_records'] - $stats['active_records'] }}</span>
            </div>
        </div>
    </div>
</div>

@if(count($inactiveProducts) > 0)
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">
        {{ __('messages.products_without_recent_activity') }}
    </h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.category') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inactiveProducts as $product)
                <tr>
                    <td><strong>{{ $product->sku }}</strong></td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
