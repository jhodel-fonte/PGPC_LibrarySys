<?php

namespace App\Policies;

use App\Models\ThesisMetadata;
use App\Models\Account;
use Illuminate\Auth\Access\Response;

class ThesisMetadataPolicy
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
    public function view(Account $user, ThesisMetadata $thesisMetadata): bool
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
    public function update(Account $user, ThesisMetadata $thesisMetadata): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Account $user, ThesisMetadata $thesisMetadata): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Account $user, ThesisMetadata $thesisMetadata): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Account $user, ThesisMetadata $thesisMetadata): bool
    {
        return false;
    }
}
