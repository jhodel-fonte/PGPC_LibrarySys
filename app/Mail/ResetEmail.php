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
    public function __construct(string $url, ?Account $user = null)
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
                'title' => 'Password Reset Request',
                'lines' => [
                    'We received a request to reset the password for your PGPC Library account.',
                    'Open the link below to choose a new password. If you did not request this, you can ignore this email.',
                ],
                'actionUrl' => $this->url,
                'actionText' => 'Reset your password',
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
