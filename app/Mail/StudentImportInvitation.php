<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StudentImportInvitation extends Mailable
{
    public function __construct(public Account $user, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Set up your PGPC Library account');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.template', with: [
            'title' => 'Your library account is ready',
            'lines' => ['A library account has been created for you.', 'Your username is '.$this->user->username.'. Use the link below to set your password.'],
            'details' => array_filter([
                'Username' => $this->user->username,
                'Student ID' => $this->user->student?->school_id_number,
                'Program' => $this->user->student?->program,
                'Year level' => $this->user->student?->year_level,
            ], fn ($value) => $value !== null && $value !== ''),
            'actionUrl' => $this->url, 'actionText' => 'Set your password', 'user' => $this->user,
        ]);
    }
}
