<?php

namespace App\Policies;

use App\Models\AccountStatus;
use App\Models\Account;
use Illuminate\Auth\Access\Response;

class AccountStatusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Account $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Account $user, AccountStatus $accountStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Account $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Account $user, AccountStatus $accountStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, AccountStatus $accountStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, AccountStatus $accountStatus): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, AccountStatus $accountStatus): bool
    {
        return false;
    }
}
