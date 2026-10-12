<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Librarian extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'librarians';

    protected $fillable = [
        'account_id',
        'school_id_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'contact_num',
        'image_url',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function issuedTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'issued_by_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'received_by_id');
    }

    public function receivedPayments(): HasMany
    {
        return $this->hasMany(FinePayment::class, 'received_by_id');
    }

    /**
     * Get the resolved public URL for the librarian's profile image.
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (empty($value)) {
            $isFemale = strtolower($this->gender ?? '') === 'female';
            $defaultKey = $isFemale ? 'female' : 'male';
            $defaultPath = config("settings.profile_image_dir.default.{$defaultKey}", "images/default-{$defaultKey}.webp");
            return asset($defaultPath);
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/') || str_starts_with($value, 'storage/')) {
            return asset(ltrim($value, '/'));
        }

        $baseDir = config('settings.profile_image_dir.librarian', '/storage/image/profile/librarian/');
        $filename = basename($value);

        return asset(trim($baseDir, '/') . '/' . $filename);
    }
}

