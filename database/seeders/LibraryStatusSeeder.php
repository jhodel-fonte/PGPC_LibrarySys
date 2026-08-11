<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LibraryStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $statuses = [
            ['id' => 1, 'status' => 'Active'],
            ['id' => 2, 'status' => 'Suspended'],
            ['id' => 3, 'status' => 'Blocked'],
            ['id' => 4, 'status' => 'Probation'],
        ];

        $data = array_map(function ($status) use ($now) {
            return array_merge($status, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $statuses);

        DB::table('library_statuses')->insertOrIgnore($data);

        $this->command->info('Library statuses seeded successfully!');
    }
}
