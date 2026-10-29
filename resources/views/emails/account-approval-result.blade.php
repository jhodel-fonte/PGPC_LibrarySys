<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ match ($result) {
        'approved' => 'Account Approved',
        'blocked' => 'Registration Temporarily Rejected',
        default => 'Document Resubmission Required',
    } }}</title>
</head>
<body style="margin: 0; padding: 24px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #111827; background: #ffffff;">
    <div>
        <p style="margin: 0; font-size: 16px; font-weight: bold;">{{ $collegeName }}</p>
        <p style="margin: 0 0 20px 0; color: #4b5563;">{{ $libraryName }}</p>

        <hr style="border: 0; border-top: 1px solid #d1d5db; margin: 0 0 20px 0;">

        <p style="margin: 0 0 16px 0;">Hello {{ $name }},</p>

        @if($result === 'approved')
            <p style="margin: 0 0 16px 0;">
                Your student account has been approved. You can now sign in and use the library system's student services.
            </p>
            <p style="margin: 0 0 16px 0;">
                Thank you,<br>
                {{ $libraryName }}
            </p>
        @elseif($result === 'blocked')
            <p style="margin: 0 0 16px 0;">
                Your registration application has been rejected and your email has been temporarily blocked from registering in the PGPC Library System.
            </p>
            @if($remarks)
                <p style="margin: 0 0 16px 0;">
                    {{ $remarks }}
                </p>
            @endif
            <p style="margin: 0 0 16px 0;">
                You may try registering again after the temporary restriction expires.
            </p>
        @else
            <p style="margin: 0 0 16px 0;">
                Your verification document needs to be resubmitted before your account can be approved.
            </p>
            @if($remarks)
                <p style="margin: 0 0 16px 0;">
                    Reviewer remarks: {{ $remarks }}
                </p>
            @endif
            <p style="margin: 0 0 16px 0;">
                Please sign in and upload a clearer or corrected document.
            </p>
        @endif

        <hr style="border: 0; border-top: 1px solid #d1d5db; margin: 24px 0 16px 0;">
                @php
            $resolvedLogoPath = $logoPath ?? public_path('images/logo.webp');
            $resolvedLogoSrc = (isset($message) && is_object($message) && method_exists($message, 'embed') && is_file($resolvedLogoPath))
                ? $message->embed($resolvedLogoPath)
                : ($logoUrl ?? asset('images/logo.webp'));
        @endphp
        <p style="margin: 0 0 12px 0;">
            <img src="{{ $resolvedLogoSrc }}" alt="{{ $collegeName }} logo" width="72" style="display: block; max-width: 72px; height: auto;">
        </p>

        <p style="margin: 0; font-size: 12px; color: #4b5563;">
            {{ $collegeName }}<br>
            {{ $libraryName }}<br>
            {{ $address }}<br>
            Contact: pgpc.edu@gmail.com
        </p>
        <p style="margin: 16px 0 0 0; font-size: 11px; color: #6b7280;">
            This is an automated notification. Please do not reply directly to this email.
        </p>
    </div>
</body>
</html>
