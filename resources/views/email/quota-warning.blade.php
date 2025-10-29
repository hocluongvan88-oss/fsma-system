@extends('email.layout')

@section('header-title', __('messages.quota_warning'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello') }} {{ $userName }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.quota_warning_message', ['percentage' => $percentage]) }}</p>
    </div>
    
    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0; color: #92400e;"><strong>{{ __('messages.warning') }}:</strong> {{ __('messages.quota_warning_limit_reached') }}</p>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <div style="font-weight: bold; color: #1f2937; margin-bottom: 8px;">{{ __('messages.quota_used') }}</div>
        <div style="font-size: 24px; font-weight: bold; color: #1e40af; margin-bottom: 5px;">{{ $percentage }}%</div>
        <div style="font-size: 12px; color: #6b7280;">{{ $usedQuota }} MB {{ __('messages.of') }} {{ $totalQuota }} MB</div>
        
        <div style="width: 100%; height: 20px; background-color: #e5e7eb; border-radius: 10px; overflow: hidden; margin-top: 10px;">
            <div style="height: 100%; width: {{ $percentage }}%; background: linear-gradient(90deg, #1e40af 0%, #1e3a8a 100%);"></div>
        </div>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.recommendations') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.delete_unnecessary_files') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.upgrade_to_higher_plan') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.contact_support_for_advice') }}
            </li>
        </ul>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $upgradeUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px;">{{ __('messages.upgrade_now') }}</a>
    </div>
@endsection
