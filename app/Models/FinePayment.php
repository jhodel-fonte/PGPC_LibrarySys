<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fine_id',
        'received_by_id',
        'payment_date',
        'payment_amount',
        'note',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'payment_amount' => 'decimal:2',
    ];

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Librarian::class, 'received_by_id');
    }
}
