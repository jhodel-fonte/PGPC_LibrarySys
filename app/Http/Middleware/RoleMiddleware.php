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

        if (! $request->user()->role) {
            abort(403, 'Unauthorized action.');
        }

        $userRole = strtolower(str_replace(' ', '', $request->user()->role->name));

        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $cleaned = strtolower(str_replace(' ', '', trim($r)));
                if ($cleaned !== '') {
                    $allowedRoles[] = $cleaned;
                }
            }
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
