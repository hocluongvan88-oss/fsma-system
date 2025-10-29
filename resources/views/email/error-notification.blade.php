@extends('email.layout')

@section('header-title', __('messages.critical_error_alert'))

@section('content')
    <div style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #1f2937;">
        {{ __('messages.hello') }} {{ __('messages.admin') }},
    </div>
    
    <div style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        <p style="margin: 10px 0;">{{ __('messages.error_detected_in_system') }}</p>
    </div>

    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #7f1d1d;">{{ __('messages.error_type') }}:</span></p>
        <p style="margin: 8px 0; color: #1f2937; word-break: break-all; font-family: 'Courier New', monospace; font-size: 12px;">{{ $errorLog->error_type }}</p>
    </div>

    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #7f1d1d;">{{ __('messages.message') }}:</span></p>
        <p style="margin: 8px 0; color: #1f2937; word-break: break-all;">{{ $errorLog->error_message }}</p>
    </div>

    <div style="background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #1f2937;">{{ __('messages.severity') }}:</span></p>
        <p style="margin: 8px 0; color: #1f2937;"><span style="display: inline-block; padding: 4px 8px; background-color: #dc2626; color: #ffffff; border-radius: 3px; font-size: 12px; font-weight: bold;">{{ strtoupper($errorLog->severity) }}</span></p>
    </div>

    <div style="background-color: #f3f4f6; border-left: 4px solid #6b7280; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #1f2937;">{{ __('messages.file') }}:</span></p>
        <p style="margin: 8px 0; color: #4b5563; word-break: break-all; font-family: 'Courier New', monospace; font-size: 12px;">{{ $errorLog->file_path }}:{{ $errorLog->line_number }}</p>
    </div>

    <div style="background-color: #f3f4f6; border-left: 4px solid #6b7280; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #1f2937;">{{ __('messages.url') }}:</span></p>
        <p style="margin: 8px 0; color: #4b5563; word-break: break-all; font-family: 'Courier New', monospace; font-size: 12px;">{{ $errorLog->url }}</p>
    </div>

    <div style="background-color: #f3f4f6; border-left: 4px solid #6b7280; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #1f2937;">{{ __('messages.user') }}:</span></p>
        <p style="margin: 8px 0; color: #4b5563;">{{ $errorLog->user?->email ?? __('messages.anonymous') }}</p>
    </div>

    <div style="background-color: #f3f4f6; border-left: 4px solid #6b7280; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #1f2937;">{{ __('messages.ip_address') }}:</span></p>
        <p style="margin: 8px 0; color: #4b5563;">{{ $errorLog->ip_address }}</p>
    </div>

    <div style="background-color: #f3f4f6; border-left: 4px solid #6b7280; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <p style="margin: 8px 0;"><span style="font-weight: bold; color: #1f2937;">{{ __('messages.occurred_at') }}:</span></p>
        <p style="margin: 8px 0; color: #4b5563;">{{ $errorLog->created_at->format('M d, Y H:i:s') }}</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px;">{{ __('messages.view_error_details') }}</a>
    </div>
@endsection
