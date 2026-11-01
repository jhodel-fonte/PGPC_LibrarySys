<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Number of attempts before the queued email is marked as failed.
     */
    public int $tries = 3;

    public string $url;
    public ?Account $user;

    /**
     * Create a new message instance.
     */
    public function __construct(string $url, ?Account $user = null, public ?string $otp = null, public ?int $otpExpiresAt = null)
    {
        $this->url = $url;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Padre Garcia Polytechnic College Library - Reset Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.template',
            with: [
                'title' => 'Reset your password',
                'lines' => [
                    'We received a request to reset the password for your PGPC Library account.',
                    $this->otp !== null ? 'Enter the code on the recovery page, or use the button below to reset your password.' : 'Use the button below to choose a new password.',
                ],
                'otp' => $this->otp,
                'formattedOtpExpiresAt' => $this->otpExpiresAt ? \Carbon\Carbon::createFromTimestamp($this->otpExpiresAt, 'Asia/Manila')->format('M d, Y g:i A') . ' (Philippine time)' : null,
                'actionUrl' => $this->url,
                'actionText' => 'Reset your password',
                'outroLines' => [
                    'The reset link expires '.(int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire', 60).' minutes after it was requested.',
                    'If you did not request a password reset, you can ignore this email.',
                ],
                'user' => $this->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
