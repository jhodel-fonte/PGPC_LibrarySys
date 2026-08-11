<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get some physical books
        $books = DB::table('books')->limit(3)->get();

        if ($books->count() >= 3) {
            DB::table('book_reservations')->insertOrIgnore([
                [
                    'id' => 1,
                    'book_id' => $books[0]->id,
                    'reservation_status_id' => 1, // Pending (ReservationStatusSeeder ID 1)
                    'reservation_date' => (clone $now)->subHours(2),
                    'due_date' => (clone $now)->addDays(2),
                    'approved_date' => null,
                    'fulfilled_date' => null,
                    'cancelled_date' => null,
                    'comment' => 'Requesting copy for research work.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 2,
                    'book_id' => $books[1]->id,
                    'reservation_status_id' => 2, // Approved (ReservationStatusSeeder ID 2)
                    'reservation_date' => (clone $now)->subDays(1),
                    'due_date' => (clone $now)->addDays(1),
                    'approved_date' => (clone $now)->subHours(18),
                    'fulfilled_date' => null,
                    'cancelled_date' => null,
                    'comment' => 'Need this for the weekend review.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 3,
                    'book_id' => $books[2]->id,
                    'reservation_status_id' => 3, // Fulfilled (ReservationStatusSeeder ID 3)
                    'reservation_date' => (clone $now)->subDays(3),
                    'due_date' => (clone $now)->subDays(1),
                    'approved_date' => (clone $now)->subDays(3)->addHours(2),
                    'fulfilled_date' => (clone $now)->subDays(2),
                    'cancelled_date' => null,
                    'comment' => 'Picked up by student.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ]);
        }

        $this->command->info('Book reservations seeded successfully!');
    }
}
