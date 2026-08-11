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
}
