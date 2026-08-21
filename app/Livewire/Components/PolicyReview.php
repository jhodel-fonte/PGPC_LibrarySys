<?php

namespace App\Components\Livewire;

use Livewire\Component;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PolicyReview extends Component
{
    public $policiesToReview = [];
    public $termsAcknowledged = false;
    public $privacyAcknowledged = false;
    public $cookieAcknowledged = false;

    public function mount(Request $request)
    {
        $user = Auth::user();
        $settings = SystemSetting::pluck('setting_value', 'setting_key')->toArray();

        // Terms
        $termsRequired = filter_var($settings['terms_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $termsVersion = (int)($settings['terms_version'] ?? 0);
        if ($termsRequired && (int)$user->terms_acknowledged_version < $termsVersion) {
            $this->policiesToReview['terms'] = [
                'title' => 'Terms and Conditions',
                'version' => $termsVersion,
                'updated_at' => $settings['terms_updated_at'] ?? now()->toDateTimeString(),
                'content' => $settings['terms_content'] ?? '',
            ];
        }

        // Privacy
        $privacyRequired = filter_var($settings['privacy_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $privacyVersion = (int)($settings['privacy_version'] ?? 0);
        if ($privacyRequired && (int)$user->privacy_acknowledged_version < $privacyVersion) {
            $this->policiesToReview['privacy'] = [
                'title' => 'Data Privacy Policy',
                'version' => $privacyVersion,
                'updated_at' => $settings['privacy_updated_at'] ?? now()->toDateTimeString(),
                'content' => $settings['privacy_content'] ?? '',
            ];
        }

        // Cookie
        $cookieRequired = filter_var($settings['cookie_require_acknowledgement'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $cookieVersion = (int)($settings['cookie_version'] ?? 0);
        if ($cookieRequired && (int)$user->cookie_acknowledged_version < $cookieVersion) {
            $this->policiesToReview['cookie'] = [
                'title' => 'Cookie Policy',
                'version' => $cookieVersion,
                'updated_at' => $settings['cookie_updated_at'] ?? now()->toDateTimeString(),
                'content' => $settings['cookie_content'] ?? '',
            ];
        }

        if (empty($this->policiesToReview)) {
            // Nothing to review, redirect back or home
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function acknowledge()
    {
        $user = Auth::user();

        if (isset($this->policiesToReview['terms']) && !$this->termsAcknowledged) {
            $this->addError('termsAcknowledged', 'You must acknowledge the Terms and Conditions.');
            return;
        }

        if (isset($this->policiesToReview['privacy']) && !$this->privacyAcknowledged) {
            $this->addError('privacyAcknowledged', 'You must acknowledge the Data Privacy Policy.');
            return;
        }

        if (isset($this->policiesToReview['cookie']) && !$this->cookieAcknowledged) {
            $this->addError('cookieAcknowledged', 'You must acknowledge the Cookie Policy.');
            return;
        }

        if (isset($this->policiesToReview['terms'])) {
            $user->terms_acknowledged_version = $this->policiesToReview['terms']['version'];
            $user->terms_acknowledged_at = now();
        }

        if (isset($this->policiesToReview['privacy'])) {
            $user->privacy_acknowledged_version = $this->policiesToReview['privacy']['version'];
            $user->privacy_acknowledged_at = now();
        }

        if (isset($this->policiesToReview['cookie'])) {
            $user->cookie_acknowledged_version = $this->policiesToReview['cookie']['version'];
            $user->cookie_acknowledged_at = now();
        }

        $user->save();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.components.policy-review')->layout('layouts.guest'); // Assuming guest layout for simple review page, or app layout.
    }
}
