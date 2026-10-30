<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isStudent() && ($user->setup_email_required || $user->setup_password_prompt)) {
            if ($request->expectsJson()) return response()->json(['message' => 'Complete your account setup first.', 'redirect' => route('student.setup')], 403);
            return redirect()->route('student.setup');
        }
        return $next($request);
    }
}
