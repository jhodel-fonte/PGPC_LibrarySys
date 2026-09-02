@php
    $logoSrc = isset($message) && method_exists($message, 'embed') && file_exists(public_path('logo.webp'))
        ? $message->embed(public_path('logo.webp'))
        : asset('logo.webp');

    $schoolImgSrc = isset($message) && method_exists($message, 'embed') && file_exists(public_path('images/school-img.webp'))
        ? $message->embed(public_path('images/school-img.webp'))
        : asset('images/school-img.webp');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Padre Garcia Polytechnic College Library</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0F172A; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; padding: 36px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">

                    <!-- Header Banner with School Image Background -->
                    <tr>
                        <td style="padding: 0; background-color: #071943;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #071943; background-image: url('{{ $schoolImgSrc }}'); background-position: right center; background-repeat: no-repeat; background-size: cover;">
                                <tr>
                                    <!-- Deep Navy Gradient Overlay -->
                                    <td style="background: linear-gradient(90deg, #071943 0%, rgba(7, 25, 67, 0.94) 52%, rgba(16, 43, 112, 0.78) 100%); padding: 26px 28px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" width="100%">
                                            <tr>
                                                <!-- PGPC Crest Logo -->
                                                <td width="60" valign="middle" style="vertical-align: middle; padding-right: 14px;">
                                                    <img src="{{ $logoSrc }}" alt="PGPC Logo" width="55" height="55" style="display: block; width: 52px; height: 52px; object-fit: contain; border-radius: 50%;">
                                                </td>
                                                <!-- Institution & System Typography -->
                                                <td valign="middle" style="vertical-align: middle;">
                                                    <div style="color: #ffffff; font-size: 17px; font-weight: 700; line-height: 22px; letter-spacing: -0.2px;">
                                                        Padre Garcia Polytechnic College
                                                    </div>
                                                    <div style="color: #FCC719; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 3px;">
                                                        Library System
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Email Content Body -->
                    <tr>
                        <td style="padding: 36px 36px 28px 36px;">
                            <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 700; color: #0F172A;">
                                Reset Your Password
                            </h2>
                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #334155;">
                                Hello,
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 24px; color: #334155;">
                                You recently requested to reset the password for your {{ config('app.name') }} account. Click the button below to choose a new password:
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 28px 0 32px 0;">
                                <a href="{{ $url }}" target="_blank" style="display: inline-block; background-color: #102B70; color: #ffffff; text-decoration: none; padding: 14px 32px; font-size: 15px; font-weight: 600; border-radius: 12px; box-shadow: 0 4px 12px rgba(16, 43, 112, 0.25);">
                                    Reset Password
                                </a>
                            </div>

                            <!-- Expiry notice -->
                            <div style="background-color: #EFF6FF; border-left: 4px solid #102B70; padding: 14px 16px; border-radius: 6px; margin-bottom: 24px;">
                                <p style="margin: 0; font-size: 13px; line-height: 20px; color: #102B70;">
                                    This password reset link will expire in <strong>{{ config('pgpc.email.reset_link_expiration', 30) }} minutes</strong>.
                                </p>
                            </div>

                            <p style="margin: 0 0 16px 0; font-size: 13px; line-height: 20px; color: #64748B;">
                                If you did not request a password reset, no further action is required; your account remains secure.
                            </p>

                            <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 28px 0;">

                            <p style="margin: 0; font-size: 12px; line-height: 18px; color: #94A3B8; word-break: break-all;">
                                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                                <a href="{{ $url }}" style="color: #102B70; text-decoration: underline;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Institutional Footer -->
                    <tr>
                        <td style="background-color: #F8FAFC; padding: 20px 36px; border-top: 1px solid #E2E8F0; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #64748B;">
                                &copy; {{ date('Y') }} Padre Garcia Polytechnic College Library System.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
