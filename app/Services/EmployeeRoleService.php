<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Librarian;
use Illuminate\Support\Facades\Auth;

class EmployeeRoleService
{
    /**
     * Standard staff role names in the system.
     */
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_HEAD_LIBRARIAN = 'Head Librarian';
    public const ROLE_LIBRARIAN = 'Librarian';

    public const STAFF_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_HEAD_LIBRARIAN,
        self::ROLE_LIBRARIAN,
    ];

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
     * Check if the user is an authorized employee/staff member (Admin, Head Librarian, or Librarian).
     */
    public static function isStaff(?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account) {
            return false;
        }

        // 1. Direct role check via Account model
        if ($account->hasRole(self::STAFF_ROLES)) {
            return true;
        }

        // 2. Associated librarian profile check
        if ($account->relationLoaded('librarian') ? (bool) $account->librarian : $account->librarian()->exists()) {
            return true;
        }

        // 3. Fallback to UserService role identifier
        $roleName = $account->role?->name;
        if ($roleName && UserService::isLibrarianRole($roleName)) {
            return true;
        }

        // 4. Fallback route context only if checking the current authenticated session on an employee route
        if ($user === null && (request()->is('admin*') || request()->is('librarian*') || request()->is('staff*') || request()->is('employee*'))) {
            return true;
        }

        return false;
    }

    /**
     * Alias for isStaff().
     */
    public static function isEmployee(?Account $user = null): bool
    {
        return self::isStaff($user);
    }

    /**
     * Check if the user is a System Administrator.
     */
    public static function isAdmin(?Account $user = null): bool
    {
        $account = self::user($user);
        return $account ? $account->hasRole(self::ROLE_ADMIN) : false;
    }

    /**
     * Check if the user is a Head Librarian.
     */
    public static function isHeadLibrarian(?Account $user = null): bool
    {
        $account = self::user($user);
        return $account ? $account->hasRole(self::ROLE_HEAD_LIBRARIAN) : false;
    }

    /**
     * Check if the user is a standard Librarian.
     */
    public static function isLibrarian(?Account $user = null): bool
    {
        $account = self::user($user);
        return $account ? $account->hasRole(self::ROLE_LIBRARIAN) : false;
    }

    /**
     * Get the associated Librarian profile model for the employee.
     */
    public static function getLibrarian(?Account $user = null): ?Librarian
    {
        $account = self::user($user);
        if (!$account) {
            return null;
        }

        return $account->relationLoaded('librarian') ? $account->librarian : $account->librarian()->first();
    }

    /**
     * Get the display name or full name of the employee.
     */
    public static function getDisplayName(?Account $user = null): string
    {
        $account = self::user($user);
        if (!$account) {
            return 'Guest';
        }

        $librarian = self::getLibrarian($account);
        if ($librarian && !empty($librarian->first_name)) {
            return trim("{$librarian->first_name} {$librarian->last_name}");
        }

        return $account->username ?? $account->email ?? 'Staff Member';
    }

    /**
     * Get the appropriate dashboard route prefix ('admin.' or 'librarian.').
     */
    public static function getRoutePrefix(?Account $user = null): string
    {
        $account = self::user($user);
        if ($account && $account->hasRole(self::ROLE_ADMIN)) {
            return 'admin.';
        }

        if (request()->is('admin*')) {
            return 'admin.';
        }

        return 'librarian.';
    }

    /* =========================================================================
     * PERMISSION CHECKING
     * ========================================================================= */

    /**
     * Check if the user has a specific permission.
     * Admins automatically pass all permission checks.
     */
    public static function hasPermission(string $permission, ?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account) {
            return false;
        }

        return $account->hasPermission($permission);
    }

    /**
     * Shorthand for hasPermission() or hasAnyPermission().
     */
    public static function can(string|array $permission, ?Account $user = null): bool
    {
        if (is_array($permission)) {
            return self::hasAnyPermission($permission, $user);
        }

        return self::hasPermission($permission, $user);
    }

    /**
     * Inverse of can().
     */
    public static function cannot(string|array $permission, ?Account $user = null): bool
    {
        return !self::can($permission, $user);
    }

    /**
     * Check if the user has at least one of the specified permissions.
     */
    public static function hasAnyPermission(array $permissions, ?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account) {
            return false;
        }

        if ($account->hasRole(self::ROLE_ADMIN)) {
            return true;
        }

        foreach ($permissions as $perm) {
            if ($account->hasPermission($perm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all of the specified permissions.
     */
    public static function hasAllPermissions(array $permissions, ?Account $user = null): bool
    {
        $account = self::user($user);
        if (!$account) {
            return false;
        }

        if ($account->hasRole(self::ROLE_ADMIN)) {
            return true;
        }

        foreach ($permissions as $perm) {
            if (!$account->hasPermission($perm)) {
                return false;
            }
        }

        return true;
    }

    /* =========================================================================
     * DOMAIN-SPECIFIC REUSABLE HELPERS
     * ========================================================================= */

    // --- CIRCULATION DESK ---

    /**
     * Check if employee can access the circulation desk module.
     */
    public static function canManageCirculation(?Account $user = null): bool
    {
        return self::can(['manage_circulation', 'view_circulation'], $user);
    }

    /**
     * Check if employee can issue book borrowings (check out).
     */
    public static function canBorrow(?Account $user = null): bool
    {
        return self::can(['manage_circulation', 'borrow_book'], $user);
    }

    /**
     * Check if employee can process book check-ins (returns).
     */
    public static function canReturn(?Account $user = null): bool
    {
        return self::can(['manage_circulation', 'return_book'], $user);
    }

    /**
     * Check if employee can manage overdue fines.
     */
    public static function canManageFines(?Account $user = null): bool
    {
        return self::can(['manage_fines', 'manage_circulation'], $user);
    }

    // --- CATALOG & BOOKS ---

    /**
     * Check if employee can access catalog management.
     */
    public static function canManageCatalog(?Account $user = null): bool
    {
        return self::can(['manage_catalog', 'view_catalog'], $user);
    }

    /**
     * Check if employee can add/catalog new books.
     */
    public static function canCreateBook(?Account $user = null): bool
    {
        return self::can(['manage_catalog', 'create_book'], $user);
    }

    /**
     * Check if employee can edit book metadata and copy details.
     */
    public static function canEditBook(?Account $user = null): bool
    {
        return self::can(['manage_catalog', 'edit_book'], $user);
    }

    /**
     * Check if employee can delete/archive books or physical copies.
     */
    public static function canDeleteBook(?Account $user = null): bool
    {
        return self::can(['manage_catalog', 'delete_book'], $user);
    }

    // --- USER MANAGEMENT ---

    /**
     * Check if employee can view student patron profiles.
     */
    public static function canViewStudents(?Account $user = null): bool
    {
        return self::can(['view_students', 'manage_users'], $user);
    }

    /**
     * Check if employee can manage students/patrons.
     */
    public static function canManageStudents(?Account $user = null): bool
    {
        return self::can(['manage_users', 'view_students', 'create_students', 'edit_students', 'delete_students'], $user);
    }

    /**
     * Check if employee can register a new student patron account.
     */
    public static function canCreateStudent(?Account $user = null): bool
    {
        return self::can(['manage_users', 'create_students'], $user);
    }

    /**
     * Check if employee can edit student records.
     */
    public static function canEditStudent(?Account $user = null): bool
    {
        return self::can(['manage_users', 'edit_students'], $user);
    }

    /**
     * Check if employee can soft-delete or deactivate a student account.
     */
    public static function canDeleteStudent(?Account $user = null): bool
    {
        return self::can(['manage_users', 'delete_students'], $user);
    }

    /**
     * Check if employee can view librarian/staff profiles.
     */
    public static function canViewLibrarians(?Account $user = null): bool
    {
        return self::can(['view_librarians', 'manage_librarian', 'manage_librarians', 'manage_users'], $user);
    }

    /**
     * Check if employee can manage other librarian/staff accounts.
     */
    public static function canManageLibrarians(?Account $user = null): bool
    {
        return self::can(['manage_librarian', 'manage_librarians', 'manage_users'], $user);
    }

    /**
     * Check if employee can access any user management module (students or librarians).
     */
    public static function canManageUsers(?Account $user = null): bool
    {
        return self::canManageStudents($user) || self::canManageLibrarians($user);
    }

    // --- SYSTEM & SETTINGS ---

    /**
     * Check if employee can view and manage system settings.
     */
    public static function canManageSettings(?Account $user = null): bool
    {
        return self::can(['manage_system', 'manage_settings'], $user);
    }

    /**
     * Check if employee can view and export system analytics/reports.
     */
    public static function canViewReports(?Account $user = null): bool
    {
        return self::can(['view_reports', 'manage_system'], $user);
    }

    /* =========================================================================
     * AUTHORIZATION GUARDS (ABORT WITH 403 IF UNAUTHORIZED)
     * ========================================================================= */

    /**
     * Guard that ensures the user is a staff member.
     */
    public static function authorizeStaff(string $message = 'Access restricted to authorized library personnel.'): void
    {
        if (!self::isStaff()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has Admin privileges.
     */
    public static function authorizeAdmin(string $message = 'Unauthorized action. System Administrator privileges required.'): void
    {
        if (!self::isAdmin()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to process book borrowings.
     */
    public static function authorizeBorrow(string $message = 'Unauthorized action. You do not have permission to process book borrowings.'): void
    {
        if (!self::canBorrow()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to process book returns.
     */
    public static function authorizeReturn(string $message = 'Unauthorized action. You do not have permission to process book returns.'): void
    {
        if (!self::canReturn()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to manage circulation.
     */
    public static function authorizeCirculation(string $message = 'Unauthorized action. You do not have permission to access circulation desk.'): void
    {
        if (!self::canManageCirculation()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to manage the catalog.
     */
    public static function authorizeCatalog(string $message = 'Unauthorized action. You do not have permission to manage the catalog.'): void
    {
        if (!self::canManageCatalog()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to manage students.
     */
    public static function authorizeStudents(string $message = 'Unauthorized action. You do not have permission to manage student accounts.'): void
    {
        if (!self::canManageStudents()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to manage librarians.
     */
    public static function authorizeLibrarians(string $message = 'Unauthorized action. You do not have permission to manage library staff.'): void
    {
        if (!self::canManageLibrarians()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has permission to manage system settings.
     */
    public static function authorizeSettings(string $message = 'Unauthorized action. You do not have permission to modify system settings.'): void
    {
        if (!self::canManageSettings()) {
            abort(403, $message);
        }
    }

    /**
     * Guard that ensures the user has a required permission.
     */
    public static function authorize(string|array $permission, string $message = 'You do not have permission to perform this action.'): void
    {
        if (!self::can($permission)) {
            abort(403, $message);
        }
    }
}