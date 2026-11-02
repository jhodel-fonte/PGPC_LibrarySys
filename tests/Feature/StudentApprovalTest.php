<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\UserApprovals;
use App\Models\Account;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class StudentApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_reviews_are_read_only_and_stale_actions_do_not_change_them(): void
    {
        Mail::fake();
        $admin = Account::factory()->create(['role_id' => 1]);
        $account = Account::factory()->create(['status_id' => 1]);
        Student::create(['account_id' => $account->id, 'school_id_number' => 'TEST-123', 'first_name' => 'Test', 'last_name' => 'Student', 'library_status_id' => 1, 'verification_document_path' => 'test.jpg']);

        Livewire::actingAs($admin)->test(UserApprovals::class)
            ->call('openReview', $account->id)
            ->assertSee('Status: Approved')
            ->assertDontSee('Approve &amp; Activate Account', false)
            ->assertDontSee('Request Resubmission')
            ->call('approveApplicant', $account->id)
            ->call('rejectApplicant', $account->id)
            ->set('selectedIds', [$account->id])
            ->call('bulkReject');

        $this->assertSame(1, (int) $account->fresh()->status_id);
        Mail::assertNothingOutgoing();
    }

    public function test_pending_reviews_offer_actions_and_bulk_approval_skips_missing_documents(): void
    {
        Mail::fake();
        $admin = Account::factory()->create(['role_id' => 1]);
        $account = Account::factory()->create(['status_id' => 4]);
        Student::create(['account_id' => $account->id, 'school_id_number' => 'TEST-124', 'first_name' => 'Test', 'last_name' => 'Student', 'library_status_id' => 1]);

        Livewire::actingAs($admin)->test(UserApprovals::class)
            ->call('openReview', $account->id)
            ->assertSee('Request Resubmission')
            ->assertSee('disabled')
            ->set('selectedIds', [$account->id])
            ->call('bulkApprove')
            ->assertDispatched('toast');

        $this->assertSame(4, (int) $account->fresh()->status_id);
        Mail::assertNothingOutgoing();
    }
}
