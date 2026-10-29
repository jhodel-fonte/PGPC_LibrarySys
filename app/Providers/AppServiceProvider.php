<?php

namespace App\Providers;

use App\Services\EmployeeRoleService;
use App\Services\StudentRoleService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \App\Models\Book::updated(function (\App\Models\Book $book) {
            if (! $book->trashed() && $book->wasChanged('status') && $book->status === 'available' && $book->book_detail_id) {
                app(\App\Services\ReservationWaitlistService::class)->allocate($book->book_detail_id);
            }
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Synchronize password reset token expiration with settings configuration
        if ($expiration = config('settings.email.reset_link_expiration')) {
            config(['auth.passwords.users.expire' => (int) $expiration]);
        }

        // ---------------------------------------------------------------------
        // Global Gate Authorization Hooks
        // ---------------------------------------------------------------------

        // Admin role automatically bypasses all individual permission checks
        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole(EmployeeRoleService::ROLE_ADMIN)) {
                return true;
            }
            return null;
        });

        // Evaluate application permissions if no model policy handled the ability
        Gate::after(function ($user, $ability, $result, $arguments = []) {
            if ($result !== null) {
                return $result;
            }

            if ($user && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability);
            }

            return null;
        });

        // ---------------------------------------------------------------------
        // Custom Blade Directives for Roles & Permissions
        // ---------------------------------------------------------------------

        Blade::if('role', function (string|array $roles) {
            $user = auth()->user();
            return $user && method_exists($user, 'hasRole') && $user->hasRole($roles);
        });

        Blade::if('staff', function () {
            return EmployeeRoleService::isStaff();
        });

        Blade::if('student', function () {
            return StudentRoleService::isStudent();
        });

        Blade::if('admin', function () {
            return EmployeeRoleService::isAdmin();
        });

        Blade::if('librarian', function () {
            return EmployeeRoleService::isLibrarian();
        });

        Blade::if('headLibrarian', function () {
            return EmployeeRoleService::isHeadLibrarian();
        });

        Blade::if('permission', function (string|array $permissions) {
            return EmployeeRoleService::can($permissions);
        });
    }
}
