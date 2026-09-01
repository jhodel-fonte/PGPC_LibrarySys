<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get all book details
        $bookDetails = DB::table('book_details')->get();
        $books = [];
        $copyNum = 1;

        foreach ($bookDetails as $detail) {
            // Copy 1: New condition
            $books[] = [
                'id' => $copyNum,
                'book_detail_id' => $detail->id,
                'book_condition_id' => 1, // New (corresponds to BookConditionSeeder ID 1)
                'accession_number' => sprintf('ACC-%05d', $copyNum),
                'code' => sprintf('978%010d', rand(1000000000, 9999999999)),
                'location' => 'Shelf A-' . rand(1, 5),
                'status' => 'available',
                'date_acquired' => (clone $now)->subMonths(6)->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $copyNum++;

            // Copy 2: Good condition
            $books[] = [
                'id' => $copyNum,
                'book_detail_id' => $detail->id,
                'book_condition_id' => 2, // Good (corresponds to BookConditionSeeder ID 2)
                'accession_number' => sprintf('ACC-%05d', $copyNum),
                'code' => sprintf('978%010d', rand(1000000000, 9999999999)),
                'location' => 'Shelf B-' . rand(1, 5),
                'status' => 'available',
                'date_acquired' => (clone $now)->subMonths(12)->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $copyNum++;
        }

        DB::table('books')->insertOrIgnore($books);

        $this->command->info('Physical book copies seeded successfully!');
    }
}
