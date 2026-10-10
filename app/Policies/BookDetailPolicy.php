<?php

namespace App\Policies;

use App\Models\BookDetail;
use App\Models\Account;
use Illuminate\Auth\Access\Response;

class BookDetailPolicy
{


    /**
     * Determine whether the user can view any book details.
     */
    public function viewAny(Account $user): bool
    {
        return $user->hasPermission('view_catalog') || $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can view the book detail.
     */
    public function view(Account $user, ?BookDetail $bookDetail = null): bool
    {
        return $user->hasPermission('view_catalog') || $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can add / create book details.
     */
    public function create(Account $user): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can edit / update the book detail.
     */
    public function update(Account $user, ?BookDetail $bookDetail = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can delete the book detail.
     */
    public function delete(Account $user, ?BookDetail $bookDetail = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can restore the book detail.
     */
    public function restore(Account $user, ?BookDetail $bookDetail = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can permanently delete the book detail.
     */
    public function forceDelete(Account $user, ?BookDetail $bookDetail = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }
}
