<?php

namespace Tests\Feature;

use App\Livewire\Components\ChangePassword;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ChangePasswordReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dismissal_keeps_the_reminder_for_the_next_login(): void
    {
        $user = Account::factory()->create(['password_change_reminder' => true]);
        $this->actingAs($user)->withSession(['password_reminder_account' => $user->id]);
        Livewire::test(ChangePassword::class)->assertSet('open', true)->call('close')->assertSet('open', false);
        $this->assertTrue($user->fresh()->password_change_reminder);
        Livewire::test(ChangePassword::class)->assertSet('open', false);
        session()->put('password_reminder_account', $user->id);
        Livewire::test(ChangePassword::class)->assertSet('open', true);
    }

    public function test_change_requires_current_password_and_clears_reminder(): void
    {
        $user = Account::factory()->create(['password_hash' => Hash::make('OldPassword1'), 'password_change_reminder' => true]);
        $component = Livewire::actingAs($user)->test(ChangePassword::class)->call('show')
            ->set('current_password', 'wrong')->set('password', 'NewPassword1')->set('password_confirmation', 'NewPassword1')
            ->call('save')->assertHasErrors(['current_password']);
        $this->assertTrue($user->fresh()->password_change_reminder);
        $component->set('current_password', 'OldPassword1')->call('save')->assertHasNoErrors()->assertSet('open', false);
        $this->assertFalse($user->fresh()->password_change_reminder);
        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password_hash));
    }

    public function test_unflagged_accounts_do_not_get_a_reminder(): void
    {
        $user = Account::factory()->create(['password_change_reminder' => false]);
        $this->actingAs($user)->withSession(['password_reminder_account' => $user->id]);
        Livewire::test(ChangePassword::class)->assertSet('open', false);
    }

    public function test_reminder_waits_for_required_email_setup_and_recovery_signs_out(): void
    {
        $user = Account::factory()->create(['password_change_reminder' => true]);
        $user->forceFill(['setup_email_required' => true])->save();
        $this->actingAs($user)->withSession(['password_reminder_account' => $user->id]);
        Livewire::test(ChangePassword::class)->assertSet('open', false);
        $user->forceFill(['setup_email_required' => false])->save();
        Livewire::actingAs($user->fresh())->test(ChangePassword::class)->assertSet('open', true);
        $this->post(route('password.change.recovery'))->assertRedirect(route('password.request'));
        $this->assertGuest();
    }
}
