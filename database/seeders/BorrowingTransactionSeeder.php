<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BorrowingTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $books = DB::table('books')->limit(3)->get();
        $students = DB::table('students')->limit(3)->get();
        $librarians = DB::table('librarians')->limit(2)->get();
        $reservations = DB::table('book_reservations')->limit(3)->get();

        if ($books->count() >= 3 && $students->count() >= 3 && $librarians->count() >= 1) {
            DB::table('borrowing_transactions')->insertOrIgnore([
                // 1. Regular active borrow (not returned yet, not overdue)
                [
                    'id' => 1,
                    'book_id' => $books[0]->id,
                    'student_id' => $students[0]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1, // Regular Check-out
                    'issued_condition_id' => 1, // New (BookCondition ID 1)
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(2),
                    'due_date' => (clone $now)->addDays(5),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                // 2. Overdue borrow (due date in the past, not returned yet)
                [
                    'id' => 2,
                    'book_id' => $books[1]->id,
                    'student_id' => $students[1]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1, // Regular Check-out
                    'issued_condition_id' => 2, // Good (BookCondition ID 2)
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(10),
                    'due_date' => (clone $now)->subDays(3),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                // 3. Fully completed borrow (returned on time)
                [
                    'id' => 3,
                    'book_id' => $books[2]->id,
                    'student_id' => $students[2]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => 3, // Fulfilled reservation (BookReservation ID 3)
                    'borrow_type_id' => 1, // Regular Check-out
                    'issued_condition_id' => 2, // Good
                    'return_condition_id' => 2, // Good
                    'issued_date' => (clone $now)->subDays(5),
                    'due_date' => (clone $now)->addDays(2),
                    'return_date' => (clone $now)->subDays(1),
                    'received_by_id' => $librarians[0]->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ]);
        }

        $this->command->info('Borrowing transactions seeded successfully!');
    }
}
