<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed: ' . $e->getMessage());

            return redirect()->route('login')->with('error', 'Google authentication failed or was cancelled. Please try again.');
        }

        if (! $googleUser || ! $googleUser->getEmail()) {
            return redirect()->route('login')->with('error', 'Unable to retrieve your email from Google.');
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();

        // 1. Look for existing account with matching Google provider ID
        $account = Account::where('provider', 'google')
            ->where('provider_id', $googleId)
            ->first();

        // 2. Or check by verified email match
        if (! $account) {
            $account = Account::where('email', $email)->first();
        }

        // If an existing account is found, log them in directly
        if ($account) {
            if ($account->status_id && $account->status_id !== 1) {
                return redirect()->route('login')->with('error', 'Your account is deactivated or suspended. Please contact the library administrator.');
            }

            // Update Google link and verify email if not already set
            $account->update([
                'provider' => 'google',
                'provider_id' => $googleId,
                'is_email_verified' => true,
                'email_verified_at' => $account->email_verified_at ?? now(),
                'last_login' => now(),
            ]);

            Auth::login($account, remember: true);
            $request->session()->regenerate();

            // Redirect based on role
            if ($account->role && in_array($account->role->name, ['Admin', 'Librarian', 'Staff'])) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(url('/'));
        }

        // 3. Brand-new user: Store Google details in session and redirect to complete registration
        $nameParts = explode(' ', trim((string) $googleUser->getName()), 2);
        $firstName = $googleUser->user['given_name'] ?? ($nameParts[0] ?? '');
        $lastName = $googleUser->user['family_name'] ?? ($nameParts[1] ?? '');

        session([
            'google_auth' => [
                'id' => $googleId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'avatar' => $googleUser->getAvatar(),
            ],
        ]);

        return redirect()->route('register')->with('status', 'Google account linked! Please complete your student details to finish registration.');
    }
}

