<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


#[Fillable(['username', 'email', 'password_hash'])]
#[Hidden(['password_hash', 'remember_token'])]
class Account extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

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

    public function getAuthPassword()
    {
        return $this->password_hash;
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
}
