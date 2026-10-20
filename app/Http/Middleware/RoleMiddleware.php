<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->is('admin*') || $request->is('librarian*') || $request->is('staff*') || $request->is('employee*')) {
                return redirect()->route('employee.login');
            }
            return redirect()->route('login');
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (! method_exists($request->user(), 'hasRole') || ! $request->user()->hasRole($allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
