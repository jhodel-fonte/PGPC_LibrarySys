<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialItemMetadata extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'asset_tag', 'serial_number', 'condition_notes', 'replacement_cost', 'metadata'];

    protected $casts = ['metadata' => 'array', 'replacement_cost' => 'decimal:2'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
