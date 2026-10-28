<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Number of attempts before the queued email is marked as failed.
     */
    public int $tries = 3;

    public string|int|null $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(string|int|null $otp = null)
    {
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PGPC Library - Verification Code (OTP)',
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
                'title' => 'Account Verification Code',
                'otp' => $this->otp,
                'message' => 'Your one-time verification code (OTP) for your PGPC Library account is: ' . $this->otp,
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
