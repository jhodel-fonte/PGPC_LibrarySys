<?php

namespace App\Policies;

use App\Models\Account;

class AccountPolicy
{
    /**
     * Determine whether the user can view any accounts.
     */
    public function viewAny(Account $user): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can view the account (own account or has manage_users permission).
     */
    public function view(Account $user, Account $account): bool
    {
        return $user->id === $account->id || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can create accounts.
     */
    public function create(Account $user): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can update the account (own account or has manage_users permission).
     */
    public function update(Account $user, Account $account): bool
    {
        return $user->id === $account->id || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(Account $user, Account $account): bool
    {
        return $user->hasPermission('manage_users') && $user->id !== $account->id;
    }

    /**
     * Determine whether the user can restore the account.
     */
    public function restore(Account $user, Account $account): bool
    {
        return $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can permanently delete the account.
     */
    public function forceDelete(Account $user, Account $account): bool
    {
        return $user->hasPermission('manage_users');
    }
}
