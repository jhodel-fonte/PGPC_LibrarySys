<?php

namespace App\Policies;

use App\Models\FineType;
use App\Models\Account;
use Illuminate\Auth\Access\Response;

class FineTypePolicy
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
    public function view(Account $user, FineType $fineType): bool
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
    public function update(Account $user, FineType $fineType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, FineType $fineType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, FineType $fineType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, FineType $fineType): bool
    {
        return false;
    }
}
