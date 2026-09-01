<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Padre Garcia Polytechnic College Library</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: 'Open Sans', Arial, sans-serif; color: #0F172A; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F8FAFC; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #102B70; padding: 32px 36px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.5px;">
                                Padre Garcia Polytechnic College Library
                            </h1>
                            <p style="color: #FCC719; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                                Padre Garcia Polytechnic College
                            </p>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 36px 36px 28px 36px;">
                            <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 700; color: #0F172A;">
                                Reset Your Password
                            </h2>
                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #334155;">
                                Hello,
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 24px; color: #334155;">
                                You recently requested to reset the password for your PGPC Library account. Click the button below to choose a new password:
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
                                    This password reset link will expire in <strong>60 minutes</strong>.
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
                    <!-- Footer -->
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

