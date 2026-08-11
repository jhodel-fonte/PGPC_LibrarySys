<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'library_status_id');
    }
}
