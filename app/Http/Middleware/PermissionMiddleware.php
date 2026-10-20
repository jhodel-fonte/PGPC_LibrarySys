<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request and verify granular permissions.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->is('admin*') || $request->is('librarian*') || $request->is('staff*') || $request->is('employee*')) {
                return redirect()->guest(route('employee.login'));
            }
            return redirect()->guest(route('login'));
        }

        // Super Admin bypasses all individual permission requirements
        if (method_exists($user, 'hasRole') && $user->hasRole(\App\Services\EmployeeRoleService::ROLE_ADMIN)) {
            return $next($request);
        }

        // Parse permission list (handles both variadic params and comma-separated lists)
        $requiredPermissions = [];
        foreach ($permissions as $permission) {
            foreach (explode(',', $permission) as $p) {
                $trimmed = trim($p);
                if ($trimmed !== '') {
                    $requiredPermissions[] = $trimmed;
                }
            }
        }

        if (empty($requiredPermissions)) {
            return $next($request);
        }

        // Check if user has ANY of the permitted abilities
        if (\App\Services\EmployeeRoleService::hasAnyPermission($requiredPermissions, $user)) {
            return $next($request);
        }

        abort(403, 'You do not have the required permissions to access this page or resource.');
    }
}
