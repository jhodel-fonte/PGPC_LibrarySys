<?php

namespace Tests;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class EmailTestHelper extends TestCase
{
    public function test_send_email()
    {
        $recipient = 'jhcyrene@gmail.com';

        try {
            Mail::fake();
            Mail::to($recipient)->send(new WelcomeEmail());
            Mail::assertSent(WelcomeEmail::class);
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail("Failed to send email. Error: " . $e->getMessage());
        }
    }
}
