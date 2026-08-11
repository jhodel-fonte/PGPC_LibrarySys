<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_transaction_id',
        'student_id',
        'fine_type_id',
        'fine_due_date',
        'amount',
        'note',
        'fine_status',
        'paid_at',
    ];

    protected $casts = [
        'fine_due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(BorrowingTransaction::class, 'borrowing_transaction_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fineType(): BelongsTo
    {
        return $this->belongsTo(FineType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinePayment::class);
    }
}
