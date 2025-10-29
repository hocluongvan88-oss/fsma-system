@extends('email.layout')

@section('header-title', __('messages.security_alert'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello') }} {{ $user->name }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.suspicious_activity_detected') }}</p>
    </div>
    
    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0; color: #7f1d1d;"><strong>{{ __('messages.security_alert') }}:</strong> {{ __('messages.account_temporarily_locked', ['minutes' => $lockoutMinutes]) }}</p>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <div style="font-weight: bold; color: #1f2937; margin-bottom: 8px;">{{ __('messages.activity_details') }}</div>
        <p style="margin: 8px 0;"><strong>{{ __('messages.action') }}:</strong> {{ ucfirst(str_replace('_', ' ', $action)) }}</p>
        <p style="margin: 8px 0;"><strong>{{ __('messages.time') }}:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        <p style="margin: 8px 0;"><strong>{{ __('messages.lockout_duration') }}:</strong> {{ $lockoutMinutes }} {{ __('messages.minutes') }}</p>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.if_not_you') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #dc2626;">▸</span>
                {{ __('messages.change_password_immediately') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #dc2626;">▸</span>
                {{ __('messages.enable_two_factor_auth') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #dc2626;">▸</span>
                {{ __('messages.contact_support_if_needed') }}
            </li>
        </ul>
    </div>
@endsection
