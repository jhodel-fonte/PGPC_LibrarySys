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

class AccountApprovalResultEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Account $account,
        public string $result,
        public ?string $remarks = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->result) {
                'approved' => 'Your PGPC Library account has been approved',
                'blocked' => 'PGPC Library registration temporarily rejected',
                default => 'Action required: resubmit your PGPC Library document',
            },
        );
    }

    public function content(): Content
    {
        $student = $this->account->student;
        $name = $student
            ? trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name)
            : $this->account->username;

        $logoRelative = 'images/logo.webp';

        return new Content(
            view: 'emails.account-approval-result',
            with: [
                'name' => $name,
                'result' => $this->result,
                'remarks' => $this->remarks,
                'logoPath' => public_path($logoRelative),
                'logoUrl' => asset($logoRelative),
                'collegeName' => config('settings.college.name', 'Padre Garcia Polytechnic College'),
                'libraryName' => config('settings.college.tagline', 'Library System'),
                'address' => config('settings.college.address'),
                'contactEmail' => config('settings.email.from_address'),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
