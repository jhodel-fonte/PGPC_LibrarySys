<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodicalIssue extends Model
{
    protected $fillable = ['book_detail_id', 'volume', 'issue_number', 'issue_label', 'publication_date', 'borrowable', 'loan_days'];

    protected $casts = ['publication_date' => 'date', 'borrowable' => 'boolean', 'loan_days' => 'integer'];

    public function bookDetail(): BelongsTo
    {
        return $this->belongsTo(BookDetail::class);
    }
}
