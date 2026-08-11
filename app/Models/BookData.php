<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BookData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'book_datas';

    protected $fillable = [
        'book_title',
        'subtitle',
        'description',
        'series_title',
        'note',
        'language_id',
        'copyright_year',
        'marc_record',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function bookDetails(): HasMany
    {
        return $this->hasMany(BookDetail::class);
    }

    public function thesisMetadata(): HasOne
    {
        return $this->hasOne(ThesisMetadata::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_data_author');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_data_category');
    }
}
