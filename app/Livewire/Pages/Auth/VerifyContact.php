<?php

namespace App\Livewire\Pages\Auth;

use App\Services\ContactOtp;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.register-auth')]
class VerifyContact extends Component
{
    public string $emailCode = '';
    public string $smsCode = '';
    public ?string $notice = null;

    public function mount(): void
    {
        abort_unless(Auth::user()?->isStudent(), 403);
        abort_unless(Auth::user()->student, 403);
    }

    public function sendCode(string $channel): void
    {
        $this->notice = null;
        $this->resetErrorBag('code');
        abort_unless(in_array($channel, ['email', 'sms'], true), 404);
        $user = Auth::user();
        abort_unless($user?->student && ($user->student->contact_verification_required || $channel === 'sms'), 403);
        if (($channel === 'email' && $user->is_email_verified) || ($channel === 'sms' && $user->student->phone_verified_at)) return;
        if ($channel === 'sms' && ! app(\App\Services\SmsService::class)->formatPhoneNumber($user->student->contact_num ?? '')) {
            $this->addError('code', 'Ask the library to update your mobile number before verifying it.');
            return;
        }
        try {
            app(ContactOtp::class)->send('registration', $user->id, $channel, $channel === 'email' ? $user->email : $user->student->contact_num);
            $this->notice = 'Code sent. Check your '.($channel === 'email' ? 'email' : 'phone').'.';
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->addError('code', 'Could not send the code. Please try again.');
        }
    }

    public function verifyCode(string $channel): void
    {
        abort_unless(in_array($channel, ['email', 'sms'], true), 404);
        $user = Auth::user();
        abort_unless($user?->student && ($user->student->contact_verification_required || $channel === 'sms'), 403);
        $field = $channel === 'email' ? 'emailCode' : 'smsCode';
        $this->validate([$field => ['required', 'regex:/^\d{6}$/']]);
        $destination = $channel === 'email' ? $user->email : $user->student->contact_num;
        if (! app(ContactOtp::class)->verify('registration', $user->id, $channel, $destination, $this->$field)) {
            $this->addError($field, 'Invalid or expired code. Please try again.');
            return;
        }
        if ($channel === 'email') $user->forceFill(['is_email_verified' => true, 'email_verified_at' => now()])->save();
        else $user->student->forceFill(['phone_verified_at' => now()])->save();
        $this->reset($field);
        $user->refresh();
        if (! $user->student->contact_verification_required) {
            session()->flash('status', 'Your mobile number is verified for password recovery.');
            $this->redirectRoute('student.profile');
            return;
        }
        if ($user->is_email_verified && $user->student->phone_verified_at) {
            $user->student->forceFill(['contact_verification_required' => false])->save();
            $this->redirectRoute('verification.upload');
        }
    }

    public function render()
    {
        return view('livewire.pages.auth.verify-contact', ['user' => Auth::user()->fresh('student')]);
    }
}
