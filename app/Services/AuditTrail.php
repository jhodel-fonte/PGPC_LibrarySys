<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditTrail
{
    private static ?string $groupId = null;
    private static ?string $groupAction = null;

    private const FIELDS = [
        \App\Models\Account::class => ['username', 'role_id', 'status_id', 'reservations_blocked', 'deleted_at'],
        \App\Models\BookData::class => ['book_title', 'subtitle', 'series_title', 'copyright_year', 'deleted_at'],
        \App\Models\BookDetail::class => ['book_data_id', 'isbn', 'issn', 'edition', 'publisher_id', 'call_number', 'daily_fine', 'deleted_at'],
        \App\Models\Book::class => ['book_detail_id', 'accession_number', 'code', 'location', 'status', 'book_condition_id', 'deleted_at'],
        \App\Models\BookReservation::class => ['book_id', 'student_id', 'account_id', 'reservation_status_id', 'reservation_date', 'due_date', 'approved_date', 'fulfilled_date', 'cancelled_date', 'comment', 'deleted_at'],
        \App\Models\BorrowingTransaction::class => ['book_id', 'school_id', 'book_reservation_id', 'issued_by_id', 'issued_date', 'due_date', 'return_date', 'received_by_id', 'borrow_type_id'],
        \App\Models\Fine::class => ['student_id', 'borrowing_transaction_id', 'fine_type_id', 'amount', 'fine_status', 'paid_at'],
        \App\Models\FinePayment::class => ['fine_id', 'received_by_id', 'payment_date', 'payment_amount', 'receipt_number'],
        \App\Models\SystemSetting::class => ['setting_key'],
    ];

    public static function register(): void
    {
        foreach (self::FIELDS as $model => $fields) {
            $model::created(fn (Model $record) => self::record($record, 'created', [], Arr::only($record->getAttributes(), $fields)));
            $model::updated(function (Model $record) use ($fields) {
                $changes = Arr::only($record->getChanges(), $fields);
                if ($changes !== [] && ! array_key_exists('deleted_at', $changes)) {
                    self::record($record, 'updated', Arr::only($record->getRawOriginal(), array_keys($changes)), $changes);
                }
            });
            $model::deleted(fn (Model $record) => self::record($record, 'deleted', Arr::only($record->getRawOriginal(), $fields), []));
            if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model), true)) {
                $model::restored(fn (Model $record) => self::record($record, 'restored', [], Arr::only($record->getAttributes(), $fields)));
            }
        }
    }

    public static function record(Model $record, string $event, array $old, array $new): void
    {
        $actor = auth()->user();
        $profile = $actor?->getAuthIdentifier() ? ($actor->librarian()->first() ?? $actor->student()->first()) : null;
        $name = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''));
        $context = self::context($record);
        if ($actor?->username) $context['Actor username'] = $actor->username;
        DB::table('audit_logs')->insert([
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_name' => $name ?: ($actor?->username ?? 'System'),
            'group_id' => self::$groupId,
            'group_action' => self::$groupAction,
            'context' => $context ? json_encode($context, JSON_THROW_ON_ERROR) : null,
            'action' => Str::snake(class_basename($record)) . '.' . $event,
            'subject_type' => class_basename($record),
            'subject_id' => (string) $record->getKey(),
            'old_values' => $old ? json_encode($old, JSON_THROW_ON_ERROR) : null,
            'new_values' => $new ? json_encode($new, JSON_THROW_ON_ERROR) : null,
            'created_at' => now('UTC'),
        ]);
    }

    public static function transaction(string $action, callable $callback): mixed
    {
        $previousId = self::$groupId;
        $previousAction = self::$groupAction;
        self::$groupId ??= (string) Str::uuid();
        self::$groupAction ??= $action;
        try {
            return DB::transaction($callback);
        } finally {
            self::$groupId = $previousId;
            self::$groupAction = $previousAction;
        }
    }

    private static function context(Model $record): array
    {
        $fine = $record instanceof \App\Models\FinePayment && $record->fine_id ? \App\Models\Fine::find($record->fine_id) : ($record instanceof \App\Models\Fine ? $record : null);
        $loan = $fine?->borrowing_transaction_id ? \App\Models\BorrowingTransaction::find($fine->borrowing_transaction_id) : null;
        $book = match (true) {
            $record instanceof \App\Models\Book => $record,
            $record instanceof \App\Models\BookReservation, $record instanceof \App\Models\BorrowingTransaction => $record->book_id ? \App\Models\Book::withTrashed()->find($record->book_id) : null,
            $loan !== null => $loan->book_id ? \App\Models\Book::withTrashed()->find($loan->book_id) : null,
            default => null,
        };
        $detail = $record instanceof \App\Models\BookDetail ? $record : ($book?->book_detail_id ? \App\Models\BookDetail::withTrashed()->find($book->book_detail_id) : null);
        $data = $record instanceof \App\Models\BookData ? $record : ($detail?->book_data_id ? \App\Models\BookData::withTrashed()->find($detail->book_data_id) : null);
        $studentId = match (true) {
            $record instanceof \App\Models\BookReservation, $record instanceof \App\Models\Fine => $record->student_id,
            $record instanceof \App\Models\BorrowingTransaction => $record->school_id,
            $fine !== null => $fine->student_id,
            default => null,
        };
        $student = $studentId ? \App\Models\Student::withTrashed()->find($studentId) : null;
        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

        return array_filter([
            'Book' => $data?->book_title,
            'Accession number' => $book?->accession_number,
            'Book ID' => $book?->getKey(),
            'Student' => $studentName ?: null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public static function syncRelationship(Model $record, string $name, array $ids, bool $detach = true): void
    {
        DB::transaction(function () use ($record, $name, $ids, $detach) {
            $relation = $record->{$name}();
            $before = $relation->allRelatedIds()->sort()->values()->all();
            $relation->sync($ids, $detach);
            $after = $relation->allRelatedIds()->sort()->values()->all();
            if ($before != $after) {
                self::record($record, $name . '_changed', [$name => $before], [$name => $after]);
            }
        });
    }
}
