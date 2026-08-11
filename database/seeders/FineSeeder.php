<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $students = DB::table('students')->limit(2)->get();
        $transactions = DB::table('borrowing_transactions')->where('id', 2)->get(); // Overdue transaction

        if ($students->count() >= 2 && $transactions->count() >= 1) {
            DB::table('fines')->insertOrIgnore([
                // 1. Unpaid fine for late book return
                [
                    'id' => 1,
                    'borrowing_transaction_id' => 2, // Overdue transaction (ID 2)
                    'student_id' => $students[1]->id,
                    'fine_type_id' => 1, // Overdue fine type (FineType ID 1)
                    'fine_due_date' => (clone $now)->addDays(7),
                    'amount' => 50.00,
                    'note' => 'Overdue fine accumulated for 3 days.',
                    'fine_status' => 'unpaid',
                    'paid_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                // 2. Paid fine
                [
                    'id' => 2,
                    'borrowing_transaction_id' => 3, // Returned transaction (ID 3)
                    'student_id' => $students[0]->id,
                    'fine_type_id' => 2, // Damaged Book fine type (FineType ID 2)
                    'fine_due_date' => (clone $now)->subDays(1),
                    'amount' => 150.00,
                    'note' => 'Minor water damage on cover page.',
                    'fine_status' => 'paid',
                    'paid_at' => (clone $now)->subHours(4),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ]);
        }

        $this->command->info('Fines seeded successfully!');
    }
}
