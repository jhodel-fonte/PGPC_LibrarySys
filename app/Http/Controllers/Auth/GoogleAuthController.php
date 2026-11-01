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
        session()->forget('google_verify_account');
        return Socialite::driver('google')->redirect();
    }

    public function verifyEmail(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();
        abort_unless($user?->isStudent() && $user->student?->contact_verification_required && ! $user->is_email_verified, 403);
        $request->session()->put('google_verify_account', $user->id);
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        $verifyAccountId = $request->session()->pull('google_verify_account');
        $failureRoute = $verifyAccountId ? 'student.contact.verify' : 'login';
        if ($verifyAccountId && (! $request->user() || (string) $request->user()->id !== (string) $verifyAccountId)) {
            return redirect()->route('login')->with('error', 'Your session changed. Please sign in and try again.');
        }
        if (! $verifyAccountId && $request->user()) {
            return redirect()->route('student.contact.verify')->with('error', 'Please start email verification again.');
        }
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed: ' . $e->getMessage());

            return redirect()->route($failureRoute)->with('error', 'Google authentication failed or was cancelled. Please try again.');
        }

        if (! $googleUser || ! $googleUser->getEmail() || ! $googleUser->getId() || ($googleUser->user['email_verified'] ?? false) !== true) {
            return redirect()->route($failureRoute)->with('error', 'Google did not provide a verified email address. Please use the email code or try another account.');
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();

        if ($verifyAccountId) {
            $user = $request->user()->fresh('student');
            if (! $user?->isStudent() || ! $user->student?->contact_verification_required || ! hash_equals(strtolower(trim((string) $user->email)), $email)) {
                return redirect()->route('student.contact.verify')->with('error', 'The verified Google email must match your registered email address.');
            }
            $user->forceFill(['is_email_verified' => true, 'email_verified_at' => $user->email_verified_at ?? now()])->save();
            if ($user->student->phone_verified_at) {
                $user->student->forceFill(['contact_verification_required' => false])->save();
                return redirect()->route('verification.upload');
            }
            return redirect()->route('student.contact.verify')->with('status', 'Email verified with Google. Please verify your mobile number.');
        }

        // 1. Look for existing account with matching Google provider ID (including soft-deleted accounts)
        $account = Account::withTrashed()
            ->where('provider', 'google')
            ->where('provider_id', $googleId)
            ->first();

        // 2. Or check by verified email match
        if (! $account) {
            $account = Account::withTrashed()->where('email', $email)->first();
        }

        // If an existing account is found, check if deactivated or disabled
        if ($account) {
            $isDeactivated = $account->trashed()
                || in_array((int) $account->status_id, [2, 3], true)
                || ($account->status && in_array(strtolower($account->status->status_name), ['inactive', 'disabled', 'suspended', 'deactivated']))
                || ($account->student()->withTrashed()->whereNotNull('deleted_at')->exists())
                || ($account->librarian()->withTrashed()->whereNotNull('deleted_at')->exists());

            if ($isDeactivated) {
                return redirect()->route('account.deactivated');
            }

            if (! hash_equals(strtolower(trim((string) $account->email)), $email) || ($account->provider === 'google' && $account->provider_id && ! hash_equals((string) $account->provider_id, $googleId))) {
                return redirect()->route('login')->with('error', 'This Google account does not match the linked library account.');
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
            $request->session()->forget('url.intended');

            // Redirect based on role directly to appropriate dashboard
            $roleName = strtolower(str_replace(' ', '', $account->role?->name ?? ''));
            if ($roleName === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif (in_array($roleName, ['librarian', 'headlibrarian', 'staff'])) {
                return redirect()->route('librarian.dashboard');
            }

            if (! $account->isApprovedStudent()) {
                return redirect()->to($account->getVerificationRedirectRoute());
            }

            return redirect()->route('student.dashboard');
        }

        // 3. Brand-new user: Store Google details in session and redirect to complete registration
        $nameParts = explode(' ', trim((string) $googleUser->getName()), 2);
        $firstName = $googleUser->user['given_name'] ?? ($nameParts[0] ?? '');
        $lastName = $googleUser->user['family_name'] ?? ($nameParts[1] ?? '');

        session([
            'google_auth' => [
                'id' => $googleId,
                'email' => $email,
                'email_verified' => true,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'avatar' => $googleUser->getAvatar(),
            ],
        ]);

        return redirect()->route('register')->with('status', 'Google account linked! Please complete your student details to finish registration.');
    }
}

