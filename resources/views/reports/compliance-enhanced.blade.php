@extends('layouts.app')

@section('title', __('messages.compliance_report'))

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('messages.compliance_report') }}</h1>
    <p style="color: var(--text-secondary);">{{ __('messages.fsma_204_compliance_status') }}</p>
</div>

<!-- Compliance Score -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.overall_compliance_score') }}</h2>
        
        <div style="text-align: center; padding: 2rem;">
            <div style="font-size: 4rem; font-weight: 700; color: {{ $complianceScore >= 80 ? 'var(--success)' : ($complianceScore >= 60 ? 'var(--warning)' : 'var(--danger)') }};">
                {{ $complianceScore }}%
            </div>
            <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-secondary);">
                {{ $complianceScore >= 80 ? __('messages.fully_compliant') : ($complianceScore >= 60 ? __('messages.partially_compliant') : __('messages.non_compliant')) }}
            </div>
        </div>

        <!-- Compliance Breakdown -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.compliance_breakdown') }}</h3>
            
            @foreach($complianceBreakdown as $item)
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.875rem;">{{ $item['name'] }}</span>
                    <span style="font-weight: 600;">{{ $item['score'] }}%</span>
                </div>
                <div style="height: 0.5rem; background: var(--bg-tertiary); border-radius: 0.25rem; overflow: hidden;">
                    <div style="height: 100%; background: {{ $item['score'] >= 80 ? 'var(--success)' : ($item['score'] >= 60 ? 'var(--warning)' : 'var(--danger)') }}; width: {{ $item['score'] }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Compliance Summary Cards -->
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <div class="card">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_cte_events') }}</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_cte_events'] }}</div>
        </div>
        
        <div class="card">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.compliant_events') }}</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--success);">{{ $stats['compliant_events'] }}</div>
        </div>
        
        <div class="card">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.non_compliant_events') }}</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--danger);">{{ $stats['non_compliant_events'] }}</div>
        </div>
        
        <div class="card">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.last_audit') }}</div>
            <div style="font-size: 1rem; font-weight: 600;">{{ $lastAudit->format('Y-m-d H:i') ?? 'N/A' }}</div>
        </div>
    </div>
</div>

<!-- CTE Events Breakdown -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.cte_events_breakdown') }}</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.receiving_events') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700;">{{ $stats['receiving_events'] }}</div>
        </div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.transformation_events') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700;">{{ $stats['transformation_events'] }}</div>
        </div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
            <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.shipping_events') }}</div>
            <div style="font-size: 1.5rem; font-weight: 700;">{{ $stats['shipping_events'] }}</div>
        </div>
    </div>
</div>

<!-- Non-Compliant Events -->
@if($nonCompliantEvents->count() > 0)
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--danger);">{{ __('messages.non_compliant_events') }}</h2>
    
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        @foreach($nonCompliantEvents as $event)
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--danger);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <strong>{{ $event->traceRecord->tlc }}</strong>
                <span class="badge badge-danger">{{ __('messages.non_compliant') }}</span>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                {{ $event->traceRecord->product->product_name }} | {{ $event->event_date->format('Y-m-d H:i') }}
            </div>
            <div style="font-size: 0.8rem; color: var(--danger);">
                {{ __('messages.issues') }}: {{ $event->compliance_issues }}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
