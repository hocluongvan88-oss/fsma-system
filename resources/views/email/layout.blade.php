<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Veximglobal Notification' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Updated header with Veximglobal branding -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); padding: 30px; text-align: center;">
                            <div style="font-size: 24px; font-weight: bold; color: #ffffff; margin-bottom: 5px;">Veximglobal</div>
                            <div style="font-size: 12px; color: #e0e7ff; letter-spacing: 0.5px;">FSMA 204 System</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            @yield('content')
                        </td>
                    </tr>
                    <!-- Updated footer with Veximglobal branding -->
                    <tr>
                        <td style="background-color: #f3f4f6; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
                                <p style="margin: 0; font-size: 13px; color: #374151; font-weight: 500;">
                                    Veximglobal - FSMA 204 Compliance System
                                </p>
                                <p style="margin: 5px 0 0 0; font-size: 11px; color: #9ca3af;">
                                    Secure Document Management & Compliance
                                </p>
                            </div>
                            <p style="margin: 0 0 10px 0; font-size: 11px; color: #6b7280;">
                                &copy; {{ date('Y') }} Veximglobal. All rights reserved.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #6b7280;">
                                <a href="{{ $unsubscribeUrl ?? '#' }}" style="color: #1e40af; text-decoration: none; margin: 0 8px;">Unsubscribe</a> | 
                                <a href="{{ $preferencesUrl ?? '#' }}" style="color: #1e40af; text-decoration: none; margin: 0 8px;">Preferences</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
