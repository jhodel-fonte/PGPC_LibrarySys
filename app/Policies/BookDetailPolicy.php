<?php

namespace App\Policies;

use App\Models\BookDetail;
use App\Models\Account;
use Illuminate\Auth\Access\Response;

class BookDetailPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super Admin and Admin have unrestricted access.
     */
    public function before(Account $user, string $ability): ?bool
    {
        if ($user->role) {
            $roleName = strtolower(trim($user->role->name));
            if (in_array($roleName, ['admin', 'super admin', 'superadmin'])) {
                return true;
            }
        }

        return null;
    }

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
