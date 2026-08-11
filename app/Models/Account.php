<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Account extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AccountStatus::class, 'status_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function librarian(): HasOne
    {
        return $this->hasOne(Librarian::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'account_permissions')
            ->withPivot('is_allowed')
            ->withTimestamps();
    }
}
