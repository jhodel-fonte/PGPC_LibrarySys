<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogImportRow extends Model
{
    use HasFactory;

    protected $fillable = ['catalog_import_id', 'row_number', 'raw_data', 'status', 'errors', 'book_data_id', 'book_detail_id', 'book_id'];

    protected $casts = ['raw_data' => 'array', 'errors' => 'array'];

    public function catalogImport(): BelongsTo
    {
        return $this->belongsTo(CatalogImport::class);
    }
}
