<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentIsVerified
{
    /**
     * Redirect unapproved student accounts to the verification upload or registration status page
     * when attempting to access student dashboard, profile, borrowing, or reservation pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isStudent') && $user->isStudent() && ! $user->isApprovedStudent()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your student account is awaiting librarian or administrator verification.',
                    'redirect' => $user->getVerificationRedirectRoute(),
                ], 403);
            }

            return redirect()->to($user->getVerificationRedirectRoute());
        }

        return $next($request);
    }
}

