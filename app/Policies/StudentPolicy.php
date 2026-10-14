<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\Account;

class StudentPolicy
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
     * Determine whether the user can view any students.
     */
    public function viewAny(Account $user): bool
    {
        return $user->hasPermission('view_students') || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can view the student (own profile or has permission).
     */
    public function view(Account $user, Student $student): bool
    {
        return $user->id === $student->account_id 
            || $user->hasPermission('view_students') 
            || $user->hasPermission('manage_users');
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(Account $user): bool
    {
        return $user->hasPermission('create_students');
    }

    /**
     * Determine whether the user can update the student (own profile or has permission).
     */
    public function update(Account $user, Student $student): bool
    {
        return $user->id === $student->account_id 
            || $user->hasPermission('edit_students');
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(Account $user, Student $student): bool
    {
        return $user->hasPermission('delete_students');
    }

    /**
     * Determine whether the user can restore the student.
     */
    public function restore(Account $user, Student $student): bool
    {
        return $user->hasPermission('delete_students');
    }

    /**
     * Determine whether the user can permanently delete the student.
     */
    public function forceDelete(Account $user, Student $student): bool
    {
        return $user->hasPermission('delete_students');
    }
}
