@extends('email.layout')

@section('header-title', __('messages.upgrade_successful'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello') }} {{ $userName }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.congratulations_upgrade', ['plan' => $planName]) }}</p>
    </div>
    
    <div style="background-color: #dcfce7; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <div style="font-weight: bold; color: #15803d; margin-bottom: 8px;">{{ __('messages.new_quota') }}</div>
        <div style="font-size: 24px; font-weight: bold; color: #16a34a; margin-bottom: 5px;">{{ $newQuota }} MB</div>
        <div style="font-size: 12px; color: #15803d;">{{ __('messages.plan') }} {{ $planName }}</div>
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;"><strong>{{ __('messages.what_you_get') }}:</strong></p>
        <ul style="padding-left: 20px; margin-top: 10px; list-style: none;">
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #16a34a;">▸</span>
                {{ __('messages.storage_quota', ['quota' => $newQuota]) }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #16a34a;">▸</span>
                {{ __('messages.faster_uploads') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #16a34a;">▸</span>
                {{ __('messages.priority_support') }}
            </li>
            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                <span style="position: absolute; left: 0; color: #16a34a;">▸</span>
                {{ __('messages.premium_features') }}
            </li>
        </ul>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #16a34a; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px;">{{ __('messages.go_to_dashboard') }}</a>
    </div>
@endsection
