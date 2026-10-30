<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\EmployeeRoleService;
use App\Services\StudentRoleService;
use Laravel\Scout\Searchable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, Searchable, HasApiTokens;

    /**
     * Get the indexable data array for Scout.
     */
    public function toSearchableArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
    protected $with = ['role'];
    protected $fillable = [
        'role_id',
        'status_id',
        'username',
        'password_hash',
        'email',
        'is_email_verified',
        'email_verified_at',
        'failed_attempts',
        'last_login',
        'password_changed_at',
        'provider',
        'provider_id',
        'terms_acknowledged_version',
        'terms_acknowledged_at',
        'privacy_acknowledged_version',
        'privacy_acknowledged_at',
        'cookie_acknowledged_version',
        'cookie_acknowledged_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
        'failed_attempts',
        'provider_id',
    ];

    protected $casts = [
        'reservations_blocked' => 'boolean',
        'setup_email_required' => 'boolean',
        'setup_password_prompt' => 'boolean',
        'is_email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'password_changed_at' => 'datetime',
        'password_hash' => 'hashed',
        'terms_acknowledged_at' => 'datetime',
        'privacy_acknowledged_at' => 'datetime',
        'cookie_acknowledged_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the encrypted route key for this account in URLs.
     */
    public function getRouteKey(): string
    {
        return $this->encrypted_id;
    }

    /**
     * Accessor for the encrypted, URL-safe ID.
     */
    public function getEncryptedIdAttribute(): string
    {
        return self::encodeId($this->id);
    }

    /**
     * Encode an integer ID into an encrypted URL-safe string.
     */
    public static function encodeId(int|string $id): string
    {
        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString((string) $id);
        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    /**
     * Decode an encrypted URL-safe string back to the integer ID.
     */
    public static function decodeId(mixed $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        try {
            $data = base64_decode(strtr((string) $value, '-_', '+/'));
            if ($data) {
                return (int) \Illuminate\Support\Facades\Crypt::decryptString($data);
            }
        } catch (\Throwable $e) {
            try {
                return (int) \Illuminate\Support\Facades\Crypt::decryptString((string) $value);
            } catch (\Throwable $e2) {
                return is_numeric($value) ? (int) $value : null;
            }
        }

        return is_numeric($value) ? (int) $value : null;
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Send the password reset notification using ResetEmail mailable.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));

        \Illuminate\Support\Facades\Mail::to($this->email)->send(new \App\Mail\ResetEmail($url, $this));
    }

    public function librarian()
    {
        return $this->hasOne(Librarian::class, 'account_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AccountStatus::class, 'status_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BookReservation::class, 'account_id');
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserHistory::class, 'user_id');
    }

    public function userHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserHistory::class, 'user_id');
    }

    public function recommendation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserRecommendation::class, 'user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'account_permissions')
            ->withPivot('is_allowed')
            ->withTimestamps();
    }

    public function getNameAttribute(): string
    {
        return $this->username;
    }

    /**
     * Check if the account has a specific permission.
     * Checks direct account permission overrides first, then role permissions.
     */
    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole(EmployeeRoleService::ROLE_ADMIN)) {
            return true;
        }

        // 1. Direct account permission override check (utilize eager-loaded collection if available)
        if ($this->relationLoaded('permissions')) {
            $accountPermission = $this->permissions->firstWhere('name', $permissionName);
        } else {
            $accountPermission = $this->permissions()->where('permissions.name', $permissionName)->first();
        }

        if ($accountPermission) {
            return (bool) ($accountPermission->pivot->is_allowed ?? true);
        }

        // 2. Role-based check (evaluate assigned role permissions)
        if ($this->role) {
            if ($this->role->relationLoaded('permissions')) {
                $hasRolePermission = $this->role->permissions->contains(function ($perm) use ($permissionName) {
                    return $perm->name === $permissionName && ($perm->pivot->is_allowed ?? true);
                });
            } else {
                $hasRolePermission = $this->role->permissions()
                    ->where('permissions.name', $permissionName)
                    ->where(function ($query) {
                        $query->where('role_permissions.is_allowed', true)
                            ->orWhereNull('role_permissions.is_allowed');
                    })
                    ->exists();
            }

            if ($hasRolePermission) {
                return true;
            }
        }

        // 3. Consolidated circulation fallback: manage_circulation covers loans, returns, and log viewing
        if (in_array($permissionName, ['view_circulation', 'borrow_book', 'return_book'], true)) {
            return $this->hasPermission('manage_circulation');
        }

        return false;
    }

    /**
     * Check if the account possesses at least one of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return EmployeeRoleService::hasAnyPermission($permissions, $this);
    }

    /**
     * Check if the account possesses all of the specified permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return EmployeeRoleService::hasAllPermissions($permissions, $this);
    }

    /**
     * Check if the account possesses any of the specified roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        $currentRole = strtolower(str_replace([' ', '_', '-'], '', $this->role->name));
        $checkRoles = is_array($roles) ? $roles : [$roles];

        foreach ($checkRoles as $role) {
            if ($currentRole === strtolower(str_replace([' ', '_', '-'], '', $role))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alias for hasRole().
     */
    public function hasAnyRole(string|array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Check if the account possesses all of the specified roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /* =========================================================================
     * ROLE & IDENTITY CONVENIENCE METHODS
     * ========================================================================= */

    public function isStaff(): bool
    {
        return EmployeeRoleService::isStaff($this);
    }

    public function isEmployee(): bool
    {
        return $this->isStaff();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(EmployeeRoleService::ROLE_ADMIN);
    }

    public function isHeadLibrarian(): bool
    {
        return $this->hasRole(EmployeeRoleService::ROLE_HEAD_LIBRARIAN);
    }

    public function isLibrarian(): bool
    {
        return $this->hasRole(EmployeeRoleService::ROLE_LIBRARIAN);
    }

    public function isStudent(): bool
    {
        return StudentRoleService::isStudent($this);
    }

    /**
     * Check whether a student account has been approved/activated by an admin or librarian.
     */
    public function isApprovedStudent(): bool
    {
        if (! $this->isStudent()) {
            return true;
        }

        if ((int) $this->status_id === 1) {
            return true;
        }

        $statusName = strtolower($this->status?->status_name ?? '');
        return $statusName === 'active';
    }

    /**
     * Check whether the student has already uploaded a verification document.
     */
    public function hasSubmittedVerificationDocument(): bool
    {
        return ! empty($this->student?->verification_document_path);
    }

    /**
     * Get the appropriate verification route for an unverified student account.
     */
    public function getVerificationRedirectRoute(): string
    {
        if ((int) $this->status_id === 5 || strtolower($this->status?->status_name ?? '') === 'not approved') {
            return route('verification.status');
        }

        if ($this->hasSubmittedVerificationDocument()) {
            return route('verification.status');
        }

        return route('verification.upload');
    }

    /* =========================================================================
     * DOMAIN PERMISSION CONVENIENCE METHODS
     * ========================================================================= */

    public function canManageCirculation(): bool
    {
        return EmployeeRoleService::canManageCirculation($this);
    }

    public function canBorrow(): bool
    {
        return EmployeeRoleService::canBorrow($this);
    }

    public function canReturn(): bool
    {
        return EmployeeRoleService::canReturn($this);
    }

    public function canManageCatalog(): bool
    {
        return EmployeeRoleService::canManageCatalog($this);
    }

    public function canManageStudents(): bool
    {
        return EmployeeRoleService::canManageStudents($this);
    }

    public function canCreateStudent(): bool
    {
        return EmployeeRoleService::canCreateStudent($this);
    }

    public function canEditStudent(): bool
    {
        return EmployeeRoleService::canEditStudent($this);
    }

    public function canDeleteStudent(): bool
    {
        return EmployeeRoleService::canDeleteStudent($this);
    }

    public function canViewStudents(): bool
    {
        return EmployeeRoleService::canViewStudents($this);
    }

    public function canManageLibrarians(): bool
    {
        return EmployeeRoleService::canManageLibrarians($this);
    }

    public function canViewLibrarians(): bool
    {
        return EmployeeRoleService::canViewLibrarians($this);
    }

    public function canManageSettings(): bool
    {
        return EmployeeRoleService::canManageSettings($this);
    }

    public function canViewReports(): bool
    {
        return EmployeeRoleService::canViewReports($this);
    }
}
