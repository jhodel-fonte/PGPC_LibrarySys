<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class EmailTest extends TestCase
{
    /**
     * Test sending WelcomeEmail to a recipient.
     */
    public function test_welcome_email_can_be_sent(): void
    {
        Mail::fake();

        $recipient = 'jhcyrene@gmail.com';

        Mail::to($recipient)->send(new WelcomeEmail());

        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($recipient) {
            return $mail->hasTo($recipient);
        });
    }
}
