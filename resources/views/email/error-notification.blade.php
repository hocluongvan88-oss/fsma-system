<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc2626; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .footer { background-color: #f3f4f6; padding: 15px; border-radius: 0 0 5px 5px; text-align: center; font-size: 12px; }
        .error-box { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #dc2626; }
        .label { font-weight: bold; color: #666; }
        .value { color: #333; word-break: break-all; }
        .button { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .severity-critical { color: #dc2626; }
        .severity-error { color: #ea580c; }
        .severity-warning { color: #ca8a04; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('messages.critical_error_alert_icon') }}</h1>
            <p>{{ __('messages.error_detected_in_system') }}</p>
        </div>

        <div class="content">
            <div class="error-box">
                <p><span class="label">{{ __('messages.error_type') }}:</span></p>
                <p class="value">{{ $errorLog->error_type }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.message') }}:</span></p>
                <p class="value">{{ $errorLog->error_message }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.severity') }}:</span></p>
                <p class="value severity-{{ $errorLog->severity }}">{{ strtoupper($errorLog->severity) }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.file') }}:</span></p>
                <p class="value">{{ $errorLog->file_path }}:{{ $errorLog->line_number }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.url') }}:</span></p>
                <p class="value">{{ $errorLog->url }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.user') }}:</span></p>
                <p class="value">{{ $errorLog->user?->email ?? __('messages.anonymous') }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.ip_address') }}:</span></p>
                <p class="value">{{ $errorLog->ip_address }}</p>
            </div>

            <div class="error-box">
                <p><span class="label">{{ __('messages.occurred_at') }}:</span></p>
                <p class="value">{{ $errorLog->created_at->format('M d, Y H:i:s') }}</p>
            </div>

            <p style="margin-top: 20px;">
                <a href="{{ $dashboardUrl }}" class="button">{{ __('messages.view_error_details') }}</a>
            </p>

            <p style="margin-top: 20px; font-size: 12px; color: #666;">
                {{ __('messages.automated_error_notification_do_not_reply') }}
            </p>
        </div>

        <div class="footer">
            <p>{{ __('messages.fsma_204_error_tracking') }}</p>
            <p>{{ __('messages.error_id') }}: {{ $errorLog->id }}</p>
        </div>
    </div>
</body>
</html>
