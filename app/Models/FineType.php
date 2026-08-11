<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FineType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }
}
