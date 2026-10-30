<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin*') || $request->is('librarian*') || $request->is('staff*') || $request->is('employee*')) {
                return route('employee.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = auth()->user();
            if ($user) {
                $roleName = strtolower(str_replace(' ', '', $user->role?->name ?? ''));
                if ($roleName === 'admin') {
                    return route('admin.dashboard');
                } elseif (in_array($roleName, ['librarian', 'headlibrarian'])) {
                    return route('librarian.dashboard');
                }
                if (method_exists($user, 'isApprovedStudent') && ! $user->isApprovedStudent()) {
                    return $user->getVerificationRedirectRoute();
                }
                return route('student.dashboard');
            }
            return '/';
        });

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'check_policy' => \App\Http\Middleware\CheckPolicyAcknowledgements::class,
            'unauthenticated' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'student.verified' => \App\Http\Middleware\EnsureStudentIsVerified::class,
            'student.setup' => \App\Http\Middleware\EnsureStudentSetup::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\App\Exceptions\AccountDeactivatedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'type' => $e->errorType,
                        'code' => $e->errorCode,
                        'message' => $e->getMessage(),
                    ],
                ], 401);
            }

            return response()->view('errors.401', [
                'code' => 401,
                'errorCode' => $e->errorCode,
                'errorType' => $e->errorType,
                'title' => 'Account Deactivated',
                'bodyTitle' => 'Account Deactivated',
                'message' => $e->getMessage(),
            ], 401);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
