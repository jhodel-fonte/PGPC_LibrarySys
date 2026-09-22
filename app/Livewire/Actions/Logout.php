<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application and return the appropriate redirect URL.
     */
    public function __invoke(): string
    {
        $user = Auth::user();
        $roleName = strtolower(str_replace(' ', '', $user?->role?->name ?? ''));
        $isEmployee = in_array($roleName, ['admin', 'headlibrarian', 'librarian'], true);

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return $isEmployee ? route('employee.login') : route('login');
    }
}
