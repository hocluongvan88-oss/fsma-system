@extends('email.layout')

@section('header-title', __('messages.quota_reached'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello') }} {{ $userName }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.quota_reached_message') }}</p>
    </div>
    
    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0; color: #7f1d1d;"><strong>{{ __('messages.critical') }}:</strong> {{ __('messages.quota_reached_cannot_upload') }}</p>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <div style="font-weight: bold; color: #1f2937; margin-bottom: 8px;">{{ __('messages.current_usage') }}</div>
        <div style="font-size: 24px; font-weight: bold; color: #dc2626; margin-bottom: 5px;">{{ $usedQuota }} MB</div>
        <div style="font-size: 12px; color: #6b7280;">{{ __('messages.reached_limit') }} {{ $totalQuota }} MB</div>
        
        <div style="width: 100%; height: 20px; background-color: #e5e7eb; border-radius: 10px; overflow: hidden; margin-top: 10px;">
            <div style="height: 100%; width: 100%; background: linear-gradient(90deg, #dc2626 0%, #b91c1c 100%);"></div>
        </div>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.you_can') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.delete_old_files') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.upgrade_package') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #1e40af;">▸</span>
                {{ __('messages.contact_support') }}
            </li>
        </ul>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $upgradeUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px;">{{ __('messages.upgrade_now') }}</a>
    </div>
@endsection
