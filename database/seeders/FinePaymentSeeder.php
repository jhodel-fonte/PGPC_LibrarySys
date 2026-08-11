<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $librarians = DB::table('librarians')->limit(1)->get();
        $fines = DB::table('fines')->where('fine_status', 'paid')->get();

        if ($librarians->count() >= 1 && $fines->count() >= 1) {
            DB::table('fine_payments')->insertOrIgnore([
                [
                    'id' => 1,
                    'fine_id' => $fines[0]->id,
                    'received_by_id' => $librarians[0]->id,
                    'payment_date' => (clone $now)->subHours(4),
                    'payment_amount' => 150.00,
                    'note' => 'Payment received at counter in full cash.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ]);
        }

        $this->command->info('Fine payments seeded successfully!');
    }
}
