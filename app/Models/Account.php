<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Account extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, Searchable;

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
        if ($this->hasRole('Admin')) {
            return true;
        }

        // 1. Direct account permission override check
        $accountPermission = $this->permissions()->where('permissions.name', $permissionName)->first();
        if ($accountPermission) {
            return (bool) $accountPermission->pivot->is_allowed;
        }

        // 2. Role-based check (all roles evaluate assigned role permissions)
        if ($this->role) {
            $hasRolePermission = $this->role->permissions()
                ->where('permissions.name', $permissionName)
                ->where(function ($query) {
                    $query->where('role_permissions.is_allowed', true)
                        ->orWhereNull('role_permissions.is_allowed');
                })
                ->exists();

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
     * Check if the account possesses any of the specified roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        $currentRole = strtolower(str_replace(' ', '', $this->role->name));
        $checkRoles = is_array($roles) ? $roles : [$roles];

        foreach ($checkRoles as $role) {
            if ($currentRole === strtolower(str_replace(' ', '', $role))) {
                return true;
            }
        }

        return false;
    }
}
