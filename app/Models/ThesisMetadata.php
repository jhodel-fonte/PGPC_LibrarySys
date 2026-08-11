<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesisMetadata extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'thesis_metadatas';

    protected $fillable = [
        'book_data_id',
        'defense_month',
        'adviser_name',
        'dean',
        'program',
        'year_level',
        'project_cost',
        'remarks',
    ];

    protected $casts = [
        'project_cost' => 'decimal:2',
    ];

    public function bookData(): BelongsTo
    {
        return $this->belongsTo(BookData::class);
    }
}
