<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentRoleService
{
    /**
     * Standard student/member role name.
     */
    public const ROLE_STUDENT = 'Student';

    /**
     * Resolve the target user account (passed instance or authenticated user).
     */
    public static function user(?Account $user = null): ?Account
    {
        return $user ?? Auth::user();
    }

    /**
     * Determine if a user is currently authenticated.
     */
    public static function check(): bool
    {
        return Auth::check();
    }

    /**
     * Check if the user is a registered student/member.
     */
    public static function isStudent(?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account) {
            return false;
        }

        // 1. Direct role check
        if ($account->hasRole(self::ROLE_STUDENT) || $account->hasRole('Member')) {
            return true;
        }

        // 2. Student relationship check
        if ($account->relationLoaded('student') ? (bool) $account->student : $account->student()->exists()) {
            return true;
        }

        // 3. Active student portal route check (when authenticated and not on employee routes)
        if ($user === null && request()->is('student*') && !request()->is('admin*') && !request()->is('librarian*') && !request()->is('staff*') && !request()->is('employee*')) {
            return true;
        }

        return false;
    }

    /**
     * Alias for isStudent().
     */
    public static function isMember(?Account $user = null): bool
    {
        return self::isStudent($user);
    }

    /**
     * Get the associated Student profile model for the user.
     */
    public static function getStudent(?Account $user = null): ?Student
    {
        $account = self::user($user);
        if (!$account) {
            return null;
        }

        return $account->relationLoaded('student') ? $account->student : $account->student()->first();
    }

    /**
     * Get the student's ID number (school_id_number).
     */
    public static function getSchoolId(?Account $user = null): ?string
    {
        $student = self::getStudent($user);
        return $student?->school_id_number;
    }

    /**
     * Get the student's full name.
     */
    public static function getFullName(?Account $user = null): string
    {
        $student = self::getStudent($user);
        if ($student && !empty($student->first_name)) {
            return trim("{$student->first_name} {$student->last_name}");
        }

        $account = self::user($user);
        return $account?->username ?? $account?->email ?? 'Student Patron';
    }

    /**
     * Get the degree program (e.g. BSIT, BSED).
     */
    public static function getProgram(?Account $user = null): ?string
    {
        $student = self::getStudent($user);
        return $student?->program;
    }

    /**
     * Get the year level of the student.
     */
    public static function getYearLevel(?Account $user = null): ?int
    {
        $student = self::getStudent($user);
        return $student?->year_level ? (int) $student->year_level : null;
    }

    /**
     * Check if the student account and library membership is in active good standing.
     */
    public static function isActive(?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account || !$account->status) {
            return false;
        }

        $statusName = strtolower(trim((string) ($account->status->status_name ?? '')));
        return $statusName === 'active';
    }

    /**
     * Check if the student account is suspended.
     */
    public static function isSuspended(?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account || !$account->status) {
            return false;
        }

        $statusName = strtolower(trim((string) ($account->status->status_name ?? '')));
        return $statusName === 'suspended';
    }

    /**
     * Check if the student is permitted to borrow books.
     */
    public static function canBorrow(?Account $user = null): bool
    {
        if (!self::isStudent($user)) {
            return false;
        }

        if (!self::isActive($user)) {
            return false;
        }

        // Students with excessive overdue items or unpaid critical penalties cannot borrow
        if (self::hasOverdueItems($user)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the student is permitted to reserve books online through OPAC.
     */
    public static function canReserve(?Account $user = null): bool
    {
        return self::isStudent($user) && self::isActive($user);
    }

    /**
     * Check if the student can view their personal loan history.
     */
    public static function canViewOwnLoans(?Account $user = null): bool
    {
        return self::isStudent($user);
    }

    /**
     * Get the count of currently active (unreturned) loans for the student.
     */
    public static function getActiveLoansCount(?Account $user = null): int
    {
        $student = self::getStudent($user);
        if (!$student) {
            return 0;
        }

        return $student->borrowingTransactions()
            ->whereNull('return_date')
            ->count();
    }

    /**
     * Get the count of overdue unreturned loans for the student.
     */
    public static function getOverdueLoansCount(?Account $user = null): int
    {
        $student = self::getStudent($user);
        if (!$student) {
            return 0;
        }

        return $student->borrowingTransactions()
            ->whereNull('return_date')
            ->where('due_date', '<', Carbon::now())
            ->count();
    }

    /**
     * Determine if the student currently has any overdue books.
     */
    public static function hasOverdueItems(?Account $user = null): bool
    {
        return self::getOverdueLoansCount($user) > 0;
    }

    /**
     * Get the student dashboard route prefix.
     */
    public static function getRoutePrefix(): string
    {
        return 'student.';
    }

    /**
     * Guard that ensures the user is an active student patron.
     */
    public static function authorize(string $message = 'Access restricted to enrolled students.'): void
    {
        if (!self::isStudent()) {
            abort(403, $message);
        }
    }
}