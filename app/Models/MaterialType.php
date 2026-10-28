<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'is_borrowable', 'default_loan_days'];

    protected $casts = ['is_borrowable' => 'boolean', 'default_loan_days' => 'integer'];

    public function bookDetails(): HasMany
    {
        return $this->hasMany(BookDetail::class);
    }
}
