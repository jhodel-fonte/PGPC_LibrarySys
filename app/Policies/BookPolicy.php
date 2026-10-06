<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Account;
use Illuminate\Auth\Access\Response;

class BookPolicy
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
     * Determine whether the user can view any books.
     */
    public function viewAny(Account $user): bool
    {
        return $user->hasPermission('view_catalog') || $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can view the book.
     */
    public function view(Account $user, ?Book $book = null): bool
    {
        return $user->hasPermission('view_catalog') || $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can add / create books.
     */
    public function create(Account $user): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can edit / update the book.
     */
    public function update(Account $user, ?Book $book = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can delete the book.
     */
    public function delete(Account $user, ?Book $book = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can restore the book.
     */
    public function restore(Account $user, ?Book $book = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }

    /**
     * Determine whether the user can permanently delete the book.
     */
    public function forceDelete(Account $user, ?Book $book = null): bool
    {
        return $user->hasPermission('manage_catalog');
    }
}
