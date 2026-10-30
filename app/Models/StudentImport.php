<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentImport extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['headers' => 'array', 'mapping' => 'array'];

    public function rows(): HasMany
    {
        return $this->hasMany(StudentImportRow::class);
    }
}
