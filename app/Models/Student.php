<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Student extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * Get the indexable data array for Scout.
     */
    public function toSearchableArray(): array
    {
        return [
            'school_id_number' => $this->school_id_number,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'contact_num' => $this->contact_num,
            'program' => $this->program,
            'year_level' => $this->year_level,
            'enrollment_status' => $this->enrollment_status,
            'enrollment_year' => $this->enrollment_year,
        ];
    }

    protected $fillable = [
        'account_id',
        'school_id_number',
        'first_name',
        'middle_name',
        'last_name',
        'contact_num',
        'date_of_birth',
        'gender',
        'image_url',
        'library_status_id',
        'note',
        'program',
        'year_level',
        'enrollment_status',
        'enrollment_year',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function libraryStatus(): BelongsTo
    {
        return $this->belongsTo(LibraryStatus::class, 'library_status_id');
    }

    public function borrowingTransactions(): HasMany
    {
        return $this->hasMany(BorrowingTransaction::class, 'school_id');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Get the resolved public URL for the student's profile image.
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

        $baseDir = config('settings.profile_image_dir.student', '/storage/image/profile/student/');
        $filename = basename($value);

        return asset(trim($baseDir, '/') . '/' . $filename);
    }
}

