<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $statuses = [
            ['id' => 1, 'status' => 'Pending'],
            ['id' => 2, 'status' => 'Approved'],
            ['id' => 3, 'status' => 'Fulfilled'],
            ['id' => 4, 'status' => 'Cancelled'],
            ['id' => 5, 'status' => 'Expired'],
        ];

        $data = array_map(function ($status) use ($now) {
            return array_merge($status, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $statuses);

        DB::table('reservation_statuses')->insertOrIgnore($data);

        $this->command->info('Reservation statuses seeded successfully!');
    }
}
