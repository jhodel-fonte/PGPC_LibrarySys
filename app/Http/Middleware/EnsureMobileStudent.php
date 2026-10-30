<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMobileStudent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user?->currentAccessToken() instanceof \Laravel\Sanctum\PersonalAccessToken) {
            return response()->json(['message' => 'A valid bearer token is required.'], 401);
        }
        if ($user->role?->name !== 'Student' || ! $user->student || ! $user->isApprovedStudent()
            || $user->setup_email_required || $user->setup_password_prompt
            || in_array(strtolower($user->status?->status_name ?? ''), ['inactive', 'disabled', 'suspended', 'deactivated'], true)
            || ! $user->tokenCan('student-dashboard')) {
            return response()->json(['message' => 'Student account access is unavailable.'], 403);
        }
        return $next($request);
    }
}
