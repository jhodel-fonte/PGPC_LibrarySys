<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'lang',
    ];

    public function bookDatas(): HasMany
    {
        return $this->hasMany(BookData::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'language_books', 'language_id', 'book_id')
            ->withTimestamps();
    }

    public function languageBooks(): HasMany
    {
        return $this->hasMany(Language_Book::class, 'language_id');
    }
}
