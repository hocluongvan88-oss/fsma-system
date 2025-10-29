@extends('email.layout')

@section('content')
<div style="padding: 32px 0;">
    <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 24px; color: #374151;">
        Hello <strong>{{ $user->full_name }}</strong>,
    </p>

    <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 24px; color: #374151;">
        We're writing to inform you that your Veximglobal account has been updated. This email confirms the following changes:
    </p>

    <div style="background-color: #f3f4f6; border-radius: 12px; padding: 24px; margin: 24px 0;">
        <div style="margin-bottom: 16px;">
            <div style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">Update Type</div>
            <div style="font-size: 16px; font-weight: 600; color: #1f2937;">{{ ucfirst(str_replace('_', ' ', $updateType)) }}</div>
        </div>

        @if(!empty($changes))
        <div style="border-top: 1px solid #e5e7eb; padding-top: 16px; margin-top: 16px;">
            <div style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px;">Changes Made:</div>
            @foreach($changes as $field => $value)
            <div style="margin-bottom: 12px;">
                <div style="font-size: 13px; color: #6b7280;">{{ ucfirst(str_replace('_', ' ', $field)) }}</div>
                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                    @if($field === 'password')
                        ••••••••
                    @elseif($field === 'two_fa_enabled')
                        {{ $value ? 'Enabled' : 'Disabled' }}
                    @else
                        {{ $value }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div style="border-top: 1px solid #e5e7eb; padding-top: 16px; margin-top: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Date & Time</div>
            <div style="font-size: 14px; color: #1f2937;">{{ now()->format('F j, Y \a\t g:i A') }}</div>
        </div>
    </div>

    <div style="background-color: #dbeafe; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 8px; margin: 24px 0;">
        <p style="margin: 0; font-size: 14px; line-height: 20px; color: #1e40af;">
            <strong>Security Notice:</strong> If you didn't make these changes, please contact our support team immediately and change your password.
        </p>
    </div>

    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ route('settings.security') }}" style="display: inline-block; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 15px;">
            Review Account Settings
        </a>
    </div>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
        <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 20px; color: #6b7280;">
            <strong>Account Security Recommendations:</strong>
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 20px; color: #6b7280;">
            <li style="margin-bottom: 8px;">Use a strong, unique password for your account</li>
            <li style="margin-bottom: 8px;">Enable two-factor authentication for added security</li>
            <li style="margin-bottom: 8px;">Review your account activity regularly</li>
            <li style="margin-bottom: 8px;">Never share your login credentials with anyone</li>
        </ul>
    </div>

    <p style="margin: 32px 0 0 0; font-size: 14px; line-height: 20px; color: #6b7280;">
        Thank you for keeping your account secure.
    </p>
</div>
@endsection
