<?php

namespace App\Policies;

use App\Models\Account;

class AccountPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(Account $user, string $ability): ?bool
    {
        if ($user->role?->name === 'Admin') {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any accounts.
     */
    public function viewAny(Account $user): bool
    {
        return $user->hasPermission('manage_users') || $user->hasPermission('manage_librarian') || $user->hasPermission('manage_librarians');
    }

    /**
     * Determine whether the user can view the account (own account or has manage permissions).
     */
    public function view(Account $user, Account $account): bool
    {
        return $user->id === $account->id 
            || $user->hasPermission('manage_users') 
            || $user->hasPermission('manage_librarian') 
            || $user->hasPermission('manage_librarians');
    }

    /**
     * Determine whether the user can create accounts.
     */
    public function create(Account $user): bool
    {
        return $user->hasPermission('manage_users') || $user->hasPermission('manage_librarian') || $user->hasPermission('manage_librarians');
    }

    /**
     * Determine whether the user can update the account (own account or has manage permissions).
     */
    public function update(Account $user, Account $account): bool
    {
        return $user->id === $account->id 
            || $user->hasPermission('manage_users') 
            || $user->hasPermission('manage_librarian') 
            || $user->hasPermission('manage_librarians');
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(Account $user, Account $account): bool
    {
        $canManage = $user->hasPermission('manage_users') 
            || $user->hasPermission('manage_librarian') 
            || $user->hasPermission('manage_librarians');

        return $canManage && $user->id !== $account->id;
    }

    /**
     * Determine whether the user can restore the account.
     */
    public function restore(Account $user, Account $account): bool
    {
        return $user->hasPermission('manage_users') 
            || $user->hasPermission('manage_librarian') 
            || $user->hasPermission('manage_librarians');
    }

    /**
     * Determine whether the user can permanently delete the account.
     */
    public function forceDelete(Account $user, Account $account): bool
    {
        $canManage = $user->hasPermission('manage_users') 
            || $user->hasPermission('manage_librarian') 
            || $user->hasPermission('manage_librarians');

        return $canManage && $user->id !== $account->id;
    }
}
