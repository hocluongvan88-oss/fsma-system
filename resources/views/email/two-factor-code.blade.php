@extends('email.layout')

@section('content')
<div style="padding: 32px 0;">
    <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 24px; color: #374151;">
        Hello <strong>{{ $user->full_name }}</strong>,
    </p>

    <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 24px; color: #374151;">
        You requested a two-factor authentication code to access your Veximglobal account. Please use the code below to complete your login:
    </p>

    <div style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-radius: 12px; padding: 32px; text-align: center; margin: 32px 0;">
        <div style="font-size: 14px; color: rgba(255, 255, 255, 0.9); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px;">
            Your 2FA Code
        </div>
        <div style="font-size: 48px; font-weight: 700; color: #ffffff; letter-spacing: 8px; font-family: 'Courier New', monospace;">
            {{ $code }}
        </div>
        <div style="font-size: 14px; color: rgba(255, 255, 255, 0.8); margin-top: 12px;">
            Valid for {{ $expiryMinutes }} minutes
        </div>
    </div>

    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin: 24px 0;">
        <p style="margin: 0; font-size: 14px; line-height: 20px; color: #92400e;">
            <strong>Security Notice:</strong> This code will expire in {{ $expiryMinutes }} minutes. If you didn't request this code, please ignore this email and ensure your account is secure.
        </p>
    </div>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
        <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 20px; color: #6b7280;">
            <strong>Security Tips:</strong>
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 20px; color: #6b7280;">
            <li style="margin-bottom: 8px;">Never share your 2FA code with anyone</li>
            <li style="margin-bottom: 8px;">Veximglobal staff will never ask for your 2FA code</li>
            <li style="margin-bottom: 8px;">If you didn't request this code, change your password immediately</li>
        </ul>
    </div>

    <p style="margin: 32px 0 0 0; font-size: 14px; line-height: 20px; color: #6b7280;">
        If you have any questions or concerns, please contact our support team.
    </p>
</div>
@endsection
