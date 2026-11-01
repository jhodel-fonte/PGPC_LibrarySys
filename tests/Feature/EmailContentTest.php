<?php

namespace Tests\Feature;

use App\Mail\AccountApprovalResultEmail;
use App\Mail\OtpEmail;
use App\Mail\ResetEmail;
use App\Mail\StudentImportInvitation;
use App\Mail\StudentSetupEmail;
use App\Models\Account;
use App\Models\Student;
use App\Notifications\Alerts;
use Tests\TestCase;

class EmailContentTest extends TestCase
{
    public function test_alert_content_and_date_labels_are_rendered(): void
    {
        $account = new Account(['username' => 'reader']);
        $account->setRelation('student', null);
        $alert = new Alerts(title: 'Reservation approved', message: 'Please collect your book.', bookTitle: 'Example book', dueDate: 'Nov 3', details: ['Reservation reference' => 'RES-00001', 'Pickup deadline' => 'Nov 3']);
        $html = (string) $alert->toMail($account)->render();

        foreach (['Please collect your book.', 'Example book', 'RES-00001', 'Pickup deadline', 'Nov 3'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('Due date:', $html);
        $this->assertSame(1, substr_count($html, 'Nov 3'));

        $loan = new Alerts(message: 'Return your book.', dueDate: 'Nov 4', details: ['Due date' => 'Nov 4']);
        $html = (string) $loan->toMail($account)->render();
        $this->assertStringContainsString('Due date', $html);
        $this->assertSame(1, substr_count($html, 'Nov 4'));
        $this->assertStringContainsString('Nov 5', (string) (new Alerts(dueDate: 'Nov 5'))->toMail($account)->render());
    }

    public function test_account_and_verification_emails_render_their_details(): void
    {
        $account = new Account(['username' => 'reader', 'email' => 'reader@example.com']);
        $account->setRelation('student', new Student(['first_name' => 'Alex', 'last_name' => 'Reader', 'school_id_number' => 'S123']));

        $otp = (new OtpEmail('001234', 10))->render();
        $this->assertStringContainsString('001234', $otp);
        $this->assertStringContainsString('10 minutes', $otp);

        $recovery = (new ResetEmail('https://example.com/reset', $account, '001234', \Carbon\Carbon::parse('2026-11-02 10:30', 'Asia/Manila')->timestamp))->render();
        foreach (['001234', 'https://example.com/reset', 'Reset your password', 'Nov 02, 2026 10:30 AM', '60 minutes'] as $text) {
            $this->assertStringContainsString($text, $recovery);
        }
        $this->assertStringContainsString('Hello User,', (new ResetEmail('https://example.com/reset', new Account(), '001234', now()->addMinutes(10)->timestamp))->render());

        foreach ([new StudentSetupEmail($account, 'https://example.com/verify'), new StudentImportInvitation($account, 'https://example.com/setup'), new ResetEmail('https://example.com/reset', $account)] as $mail) {
            $html = $mail->render();
            $this->assertStringContainsString('https://example.com/', $html);
            $this->assertStringContainsString('reader', $html);
        }
        $this->assertStringContainsString('S123', (new StudentSetupEmail($account, 'https://example.com/verify'))->render());

        foreach (['approved', 'blocked', 'rejected'] as $result) {
            $html = (new AccountApprovalResultEmail($account, $result, 'Reviewer note'))->render();
            $this->assertStringContainsString('Alex Reader', $html);
            $this->assertStringContainsString('S123', $html);
            $this->assertStringContainsString('pgpc.edu@gmail.com', $html);
            if ($result !== 'approved') $this->assertStringContainsString('Reviewer note', $html);
        }
    }
}
