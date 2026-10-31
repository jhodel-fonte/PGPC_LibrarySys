<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StudentSetupEmail extends Mailable
{
    public function __construct(public Account $user, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your PGPC Library email');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.template', with: [
            'title' => 'Verify your email address',
            'lines' => ['Please confirm this email address to complete your library account setup.'],
            'details' => array_filter([
                'Username' => $this->user->username,
                'Email address' => $this->user->email,
                'Student ID' => $this->user->student?->school_id_number,
            ], fn ($value) => $value !== null && $value !== ''),
            'actionUrl' => $this->url, 'actionText' => 'Verify email', 'user' => $this->user,
        ]);
    }
}
