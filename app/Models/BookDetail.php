<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_data_id',
        'isbn',
        'issn',
        'publication_year',
        'copyright_year',
        'edition',
        'pages',
        'format',
        'book_type_id',
        'call_number',
        'classification',
        'publisher_id',
        'cover_image',
        'url',
        'total_copies',
        'available_copies',
        'borrowed_copies',
        'damaged_lost_copies',
    ];

    protected $casts = [
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'borrowed_copies' => 'integer',
        'damaged_lost_copies' => 'integer',
    ];

    public function bookData(): BelongsTo
    {
        return $this->belongsTo(BookData::class);
    }

    public function bookType(): BelongsTo
    {
        return $this->belongsTo(BookType::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Resolves the full URL for the book cover based on configuration settings.
     */
    public function getCoverUrlAttribute(): string
    {
        $defaultImg = config('settings.coverFile_default', 'book-cover.webp');
        $defaultUrl = asset('images/' . ltrim($defaultImg, '/'));

        if (empty($this->cover_image)) {
            return $defaultUrl;
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        $baseUrl = config('settings.coverFile_url', '/storage/book_cover/');
        $filename = basename($this->cover_image);

        if (str_starts_with($baseUrl, 'http://') || str_starts_with($baseUrl, 'https://')) {
            return rtrim($baseUrl, '/') . '/' . $filename;
        }

        return asset(trim($baseUrl, '/') . '/' . $filename);
    }
}
