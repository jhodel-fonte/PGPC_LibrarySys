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

        // Retrieve a larger pool of books, students, and librarians
        $books = DB::table('books')->limit(15)->get();
        $students = DB::table('students')->limit(10)->get();
        $librarians = DB::table('librarians')->limit(2)->get();

        if ($books->count() >= 12 && $students->count() >= 5 && $librarians->count() >= 1) {
            DB::table('borrowing_transactions')->insertOrIgnore([
                // Student 0: Has 3 active borrows (1 regular active, 2 active/due soon)
                [
                    'id' => 1,
                    'book_id' => $books[0]->id,
                    'school_id' => $students[0]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1, // Regular Check-out
                    'issued_condition_id' => 1, // New
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(2),
                    'due_date' => (clone $now)->addDays(5),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 5,
                    'book_id' => $books[4]->id,
                    'school_id' => $students[0]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2, // Good
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(4),
                    'due_date' => (clone $now)->addDays(3),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 6,
                    'book_id' => $books[5]->id,
                    'school_id' => $students[0]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(1),
                    'due_date' => (clone $now)->addDays(6),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // Student 1: Has 1 overdue borrow, and 1 returned book
                [
                    'id' => 2,
                    'book_id' => $books[1]->id,
                    'school_id' => $students[1]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2, // Good
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(10),
                    'due_date' => (clone $now)->subDays(3),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 7,
                    'book_id' => $books[6]->id,
                    'school_id' => $students[1]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => 2,
                    'issued_date' => (clone $now)->subDays(8),
                    'due_date' => (clone $now)->subDays(1),
                    'return_date' => (clone $now)->subDays(2),
                    'received_by_id' => $librarians[0]->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // Student 2: Has 2 completed returns, and 1 active borrow
                [
                    'id' => 3,
                    'book_id' => $books[2]->id,
                    'school_id' => $students[2]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => 3, // Fulfilled reservation
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => 2,
                    'issued_date' => (clone $now)->subDays(5),
                    'due_date' => (clone $now)->addDays(2),
                    'return_date' => (clone $now)->subDays(1),
                    'received_by_id' => $librarians[0]->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 4,
                    'book_id' => $books[3]->id,
                    'school_id' => $students[2]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => 2,
                    'issued_date' => (clone $now)->subDays(5),
                    'due_date' => (clone $now)->addDays(2),
                    'return_date' => (clone $now)->subDays(1),
                    'received_by_id' => $librarians[0]->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 8,
                    'book_id' => $books[7]->id,
                    'school_id' => $students[2]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 1,
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(3),
                    'due_date' => (clone $now)->addDays(4),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // Student 3: Has 1 active borrow, and 1 overdue borrow
                [
                    'id' => 9,
                    'book_id' => $books[8]->id,
                    'school_id' => $students[3]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(3),
                    'due_date' => (clone $now)->addDays(4),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 10,
                    'book_id' => $books[9]->id,
                    'school_id' => $students[3]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 3, // Fair
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(12),
                    'due_date' => (clone $now)->subDays(5),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // Student 4: Has 1 active borrow, and 1 returned book
                [
                    'id' => 11,
                    'book_id' => $books[10]->id,
                    'school_id' => $students[4]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => null,
                    'issued_date' => (clone $now)->subDays(1),
                    'due_date' => (clone $now)->addDays(6),
                    'return_date' => null,
                    'received_by_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 12,
                    'book_id' => $books[11]->id,
                    'school_id' => $students[4]->id,
                    'issued_by_id' => $librarians[0]->id,
                    'book_reservation_id' => null,
                    'borrow_type_id' => 1,
                    'issued_condition_id' => 2,
                    'return_condition_id' => 2,
                    'issued_date' => (clone $now)->subDays(6),
                    'due_date' => (clone $now)->addDays(1),
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
