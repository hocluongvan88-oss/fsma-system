@extends('email.layout')

@section('header-title', __('messages.data_retention_completed'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello_admin') }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.automated_data_retention_cleanup_completed') }}</p>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <div style="font-weight: bold; color: #1f2937; margin-bottom: 8px;">{{ __('messages.cleanup_summary') }}</div>
        <div style="font-size: 24px; font-weight: bold; color: #1e40af; margin-bottom: 5px;">{{ $totalDeleted }}</div>
        <div style="font-size: 12px; color: #6b7280;">{{ __('messages.total_records_deleted') }}</div>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.records_deleted_by_type') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.trace_records') }}: <strong>{{ $stats['trace_records'] ?? 0 }}</strong>
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.cte_events') }}: <strong>{{ $stats['cte_events'] ?? 0 }}</strong>
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.audit_logs') }}: <strong>{{ $stats['audit_logs'] ?? 0 }}</strong>
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.e_signatures') }}: <strong>{{ $stats['e_signatures'] ?? 0 }}</strong>
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.error_logs') }}: <strong>{{ $stats['error_logs'] ?? 0 }}</strong>
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.notifications') }}: <strong>{{ $stats['notifications'] ?? 0 }}</strong>
            </li>
        </ul>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.retention_policy') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.traceability_data_27_months') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.compliance_data_27_months') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.error_logs_6_months') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.notifications_3_months') }}
            </li>
        </ul>
    </div>
@endsection
