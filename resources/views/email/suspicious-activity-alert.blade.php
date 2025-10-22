<h2>{{ __('messages.security_alert') }}</h2>

<p>{{ __('messages.dear_user', ['name' => $user->full_name]) }},</p>

<p>{{ __('messages.suspicious_activity_detected') }}</p>

<div style="background-color: #f3f4f6; padding: 1rem; border-radius: 4px; margin: 1rem 0;">
    <strong>{{ __('messages.action') }}:</strong> {{ ucfirst(str_replace('_', ' ', $action)) }}<br>
    <strong>{{ __('messages.time') }}:</strong> {{ now()->format('Y-m-d H:i:s') }}<br>
    <strong>{{ __('messages.lockout_duration') }}:</strong> {{ $lockoutMinutes }} {{ __('messages.minutes') }}
</div>

<p>{{ __('messages.account_temporarily_locked', ['minutes' => $lockoutMinutes]) }}</p>

<p><strong>{{ __('messages.if_not_you') }}:</strong></p>
<ul>
    <li>{{ __('messages.change_password_immediately') }}</li>
    <li>{{ __('messages.enable_two_factor_auth') }}</li>
    <li>{{ __('messages.contact_support_if_needed') }}</li>
</ul>

<p>{{ __('messages.best_regards') }},<br>
{{ __('messages.security_team') }}</p>
