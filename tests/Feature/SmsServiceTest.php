<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    public function test_sends_a_normalized_number_and_redacts_the_token_from_the_response(): void
    {
        Http::fake(['*' => Http::response([
            'status' => 200,
            'message_status_link' => 'https://example.com/status?api_token=secret-token',
        ])]);

        $result = (new SmsService('secret-token', 'https://example.com/sms', true))->send('0917 123 4567', 'Test');

        $this->assertTrue($result['success']);
        $this->assertStringNotContainsString('secret-token', json_encode($result));
        Http::assertSent(fn ($request) => $request['phone_number'] === '639171234567' && $request['api_token'] === 'secret-token');
    }

    public function test_rejects_invalid_numbers_and_gateway_errors(): void
    {
        Http::fake(['*' => Http::response(['status' => 400, 'message' => 'Invalid request'])]);
        $service = new SmsService('secret-token', 'https://example.com/sms', true);

        $this->assertFalse($service->send('12345', 'Test')['success']);
        Http::assertNothingSent();
        $this->assertFalse($service->send('09171234567', 'Test')['success']);
    }

    public function test_sms_command_prompts_for_a_number_and_reports_gateway_failure(): void
    {
        config(['settings.sms.api_token' => 'test-token', 'settings.sms.enabled' => true]);
        Http::fake(['*' => Http::response(['message' => 'Insufficient credits'], 400)]);

        $this->artisan('sms:send')
            ->expectsQuestion('Enter the test mobile number (e.g. 09171234567)', '09171234567')
            ->expectsOutputToContain('Insufficient credits')
            ->assertExitCode(1);

        Http::assertSent(fn ($request) => $request['phone_number'] === '639171234567');
    }

    public function test_sms_command_reports_a_missing_token_before_contacting_the_gateway(): void
    {
        config(['settings.sms.api_token' => '', 'settings.sms.enabled' => true]);
        Http::fake();

        $this->artisan('sms:send', ['phone' => '09171234567'])
            ->expectsOutputToContain('failed before receiving a gateway response')
            ->expectsOutputToContain('token is not configured')
            ->assertExitCode(1);

        Http::assertNothingSent();
    }
}
