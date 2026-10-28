<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogImport extends Model
{
    use HasFactory;

    protected $fillable = ['uploaded_by', 'source_file_name', 'source_file_path', 'source_type', 'status', 'total_rows', 'imported_rows', 'failed_rows', 'summary'];

    protected $casts = ['summary' => 'array'];

    public function rows(): HasMany
    {
        return $this->hasMany(CatalogImportRow::class);
    }
}
