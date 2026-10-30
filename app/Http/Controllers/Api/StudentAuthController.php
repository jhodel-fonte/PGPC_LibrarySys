<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\TurnstileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
            'device_name' => ['required', 'string', 'max:100'],
            'turnstile_token' => [app()->environment('testing') ? 'nullable' : 'required', 'string', 'max:2048'],
        ]);
        $login = trim($input['login']);
        $key = 'mobile-login:'.Str::transliterate(Str::lower($login)).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many attempts. Please try again later.', 'retry_after' => RateLimiter::availableIn($key)], 429);
        }
        if (! TurnstileService::verify($input['turnstile_token'] ?? null, 'login', $request->ip())) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['turnstile_token' => 'Security verification failed.']);
        }
        $account = Account::with(['status', 'student'])->whereHas('role', fn ($query) => $query->where('name', 'Student'))
            ->where(function ($query) use ($login) {
                $query->where('email', $login)->orWhere('username', $login)
                    ->orWhereHas('student', fn ($student) => $student->where('school_id_number', $login));
            })->first();
        if (! $account || ! Hash::check($input['password'], $account->getAuthPassword())) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['login' => 'Invalid username or password.']);
        }
        RateLimiter::clear($key);
        if (! $account->student || in_array(strtolower($account->status?->status_name ?? ''), ['inactive', 'disabled', 'suspended', 'deactivated'], true)) {
            return response()->json(['message' => 'This account cannot access the student portal.'], 403);
        }
        if ($account->setup_email_required || $account->setup_password_prompt || ! $account->isApprovedStudent()) {
            return response()->json(['message' => 'Complete account setup or student verification on the website before using the app.', 'setup_url' => route('login')], 403);
        }
        $account->update(['last_login' => now(), 'failed_attempts' => 0]);
        $token = $account->createToken($input['device_name'], ['student-dashboard'], now()->addDays(30));
        return response()->json(['token' => $token->plainTextToken, 'token_type' => 'Bearer', 'expires_at' => $token->accessToken->expires_at->toIso8601String()]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        abort_unless($token instanceof \Laravel\Sanctum\PersonalAccessToken, 401);
        $token->delete();
        return response()->noContent();
    }
}
