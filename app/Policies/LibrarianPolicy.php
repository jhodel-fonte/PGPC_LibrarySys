<?php

namespace App\Policies;

use App\Models\Librarian;
use App\Models\Account;

class LibrarianPolicy
{
    /**
     * Determine whether the user can view any librarians.
     */
    public function viewAny(Account $user): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can view the librarian (own record or has manage_users permission).
     */
    public function view(Account $user, Librarian $librarian): bool
    {
        return $user->id === $librarian->account_id || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can create librarians.
     */
    public function create(Account $user): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can update the librarian (own record or has manage_users permission).
     */
    public function update(Account $user, Librarian $librarian): bool
    {
        return $user->id === $librarian->account_id || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can delete the librarian.
     */
    public function delete(Account $user, Librarian $librarian): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can restore the librarian.
     */
    public function restore(Account $user, Librarian $librarian): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can permanently delete the librarian.
     */
    public function forceDelete(Account $user, Librarian $librarian): bool
    {
        return $user->hasPermission('manage_users');
    }
}
