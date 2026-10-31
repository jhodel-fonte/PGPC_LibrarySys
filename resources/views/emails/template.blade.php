<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? $title ?? $email_subject ?? 'PGPC Library Notification' }}</title>
</head>
<body style="margin: 0; padding: 24px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #000000; background-color: #ffffff;">

    <div>
        <p style="margin: 0 0 4px 0; font-size: 15px; font-weight: bold;">
            PGPC LIBRARY SYSTEM
        </p>
        <p style="margin: 0 0 16px 0; font-size: 13px;">
            Padre Garcia Polytechnic College
        </p>

        <hr style="border: 0; border-top: 1px solid #cccccc; margin: 16px 0;">

        {{-- Greeting --}}
        @if(!empty($greeting))
            <p style="margin: 0 0 16px 0;">{{ $greeting }}</p>
        @elseif(!empty($name))
            <p style="margin: 0 0 16px 0;">Hello {{ $name }},</p>
        @elseif(!empty($user))
            @php
                $userName = is_object($user)
                    ? ($user->first_name ?? $user->name ?? $user->username ?? 'User')
                    : $user;
            @endphp
            <p style="margin: 0 0 16px 0;">Hello {{ $userName }},</p>
        @else
            <p style="margin: 0 0 16px 0;">Hello,</p>
        @endif

        {{-- Title / Subject Header if provided --}}
        @if(!empty($title))
            <p style="margin: 0 0 16px 0; font-weight: bold; font-size: 15px;">
                {{ $title }}
            </p>
        @endif

        {{-- Main Message Body (safely checking string to avoid collision with Laravel $message object) --}}
        @php
            $displayMessage = null;
            if (isset($emailMessage) && is_string($emailMessage)) {
                $displayMessage = $emailMessage;
            } elseif (isset($content) && is_string($content)) {
                $displayMessage = $content;
            } elseif (isset($body) && is_string($body)) {
                $displayMessage = $body;
            } elseif (isset($message) && is_string($message)) {
                $displayMessage = $message;
            }
        @endphp

        @if($displayMessage)
            <p style="margin: 0 0 16px 0; white-space: pre-line;">{{ $displayMessage }}</p>
        @endif

        {{-- Related Book Details if present --}}
        @php
            $displayBookTitle = $bookTitle ?? $book_title ?? null;
            $displayDueDate = $dueDate ?? $due_date ?? null;
        @endphp
        @if($displayBookTitle)
            <p style="margin: 0 0 14px 0;">
                <strong>Book:</strong> {{ $displayBookTitle }}
                @if($displayDueDate)
                    <br><strong>Due Date:</strong> {{ $displayDueDate }}
                @endif
            </p>
        @endif

        @if(!empty($details) && is_iterable($details))
            <div style="margin: 0 0 18px 0; padding: 14px 16px; border: 1px solid #dbe3ef; border-radius: 8px; background: #f8fafc;">
                <p style="margin: 0 0 8px 0; color: #102b70; font-weight: bold;">Details</p>
                <table role="presentation" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    @foreach($details as $label => $value)
                        @if($value !== null && $value !== '')
                            <tr>
                                <td style="padding: 4px 12px 4px 0; color: #64748b; vertical-align: top;">{{ is_string($label) ? $label : 'Detail' }}</td>
                                <td style="padding: 4px 0; color: #0f172a; font-weight: 600; vertical-align: top;">{{ $value }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endif

        {{-- Multiple message lines if array passed --}}
        @if(!empty($lines) && is_iterable($lines))
            @foreach($lines as $line)
                <p style="margin: 0 0 12px 0;">{{ $line }}</p>
            @endforeach
        @endif

        @if(!empty($introLines) && is_iterable($introLines))
            @foreach($introLines as $line)
                <p style="margin: 0 0 12px 0;">{{ $line }}</p>
            @endforeach
        @endif

        {{-- Specific OTP display if passed --}}
        @if(!empty($otp))
            <p style="margin: 16px 0; font-size: 18px; font-weight: bold; letter-spacing: 2px;">
                OTP: {{ $otp }}
            </p>
            <p style="margin: 0 0 16px 0; font-size: 13px;">
                This one-time password is valid for {{ config('auth.otp.expiry', 10) }} minutes. Please do not share this code with anyone.
            </p>
        @endif

        {{-- Blade Slot content if used as a layout component --}}
        @if(!empty($slot) && trim($slot) !== '')
            <div style="margin: 0 0 16px 0;">
                {{ $slot }}
            </div>
        @endif

        {{-- Action Link / Button (plain text style) --}}
        @php
            $actionLink = $actionUrl ?? $url ?? $link ?? null;
            $actionLabel = $actionText ?? $actionLabel ?? 'Link';
        @endphp

        @if($actionLink)
            <p style="margin: 16px 0;">
                {{ $actionLabel }}:<br>
                <a href="{{ $actionLink }}" style="color: #0000ee; text-decoration: underline; word-break: break-all;">
                    {{ $actionLink }}
                </a>
            </p>
        @endif

        {{-- Outro Lines --}}
        @if(!empty($outroLines) && is_iterable($outroLines))
            @foreach($outroLines as $line)
                <p style="margin: 0 0 12px 0;">{{ $line }}</p>
            @endforeach
        @endif

        <hr style="border: 0; border-top: 1px solid #cccccc; margin: 24px 0 16px 0;">

        {{-- Sign-off and Footer --}}
        <p style="margin: 0 0 4px 0;">Thank you,</p>
        <p style="margin: 0 0 16px 0; font-weight: bold;">
            PGPC Library System<br>
            <span style="font-weight: normal; font-size: 13px;">Padre Garcia Polytechnic College</span>
        </p>

        <p style="margin: 16px 0 0 0; font-size: 11px; color: #555555;">
            This is an automated notification from the PGPC Library System. Please do not reply directly to this email.
        </p>
    </div>

</body>
</html>
