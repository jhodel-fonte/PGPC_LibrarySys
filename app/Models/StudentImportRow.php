<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentImportRow extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['data' => 'array'];
}
