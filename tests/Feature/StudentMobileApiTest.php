<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMobileApiTest extends TestCase
{
    use RefreshDatabase;

    private function student(): Account
    {
        $account = Account::factory()->create();
        Student::create([
            'account_id' => $account->id, 'school_id_number' => '2026-'.str_pad((string) $account->id, 5, '0', STR_PAD_LEFT),
            'first_name' => 'Mobile', 'last_name' => 'Student', 'library_status_id' => 1,
        ]);
        return $account;
    }

    public function test_student_can_log_in_view_own_dashboard_and_revoke_token(): void
    {
        $student = $this->student();
        $other = $this->student();
        $login = $this->postJson('/api/v1/student/login', ['login' => $student->student->school_id_number, 'password' => 'password', 'device_name' => 'Test phone']);
        $login->assertOk()->assertJsonStructure(['token', 'token_type', 'expires_at']);
        $token = $login->json('token');
        $this->withToken($token)->getJson('/api/v1/student/dashboard')
            ->assertOk()->assertJsonPath('student.id', $student->student->id)
            ->assertJsonMissing(['school_id_number' => $other->student->school_id_number]);
        $this->withToken($token)->postJson('/api/v1/student/logout')->assertNoContent();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => (int) explode('|', $token, 2)[0]]);
        // Each HTTP request resolves its own guard; reset it between requests in this test.
        app('auth')->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/student/dashboard')->assertUnauthorized();
    }

    public function test_dashboard_rejects_accounts_requiring_setup_even_with_an_existing_token(): void
    {
        $account = $this->student();
        $token = $account->createToken('Test', ['student-dashboard'])->plainTextToken;
        $account->forceFill(['setup_email_required' => true])->save();
        $this->withToken($token)->getJson('/api/v1/student/dashboard')->assertForbidden();
        $this->postJson('/api/v1/student/login', ['login' => $account->username, 'password' => 'password', 'device_name' => 'Test'])
            ->assertForbidden()->assertJsonMissingPath('token');
    }

    public function test_invalid_credentials_and_unauthenticated_requests_are_rejected(): void
    {
        $account = $this->student();
        $this->postJson('/api/v1/student/login', ['login' => $account->username, 'password' => 'wrong', 'device_name' => 'Test'])->assertUnprocessable();
        $this->getJson('/api/v1/student/dashboard')->assertUnauthorized();
    }
}
