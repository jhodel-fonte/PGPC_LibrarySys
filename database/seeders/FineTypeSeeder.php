<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FineTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['id' => 1, 'name' => 'Overdue'],
            ['id' => 2, 'name' => 'Damaged Book'],
            ['id' => 3, 'name' => 'Lost Book'],
            ['id' => 4, 'name' => 'Handling Fee'],
        ];

        $data = array_map(function ($type) use ($now) {
            return array_merge($type, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $types);

        DB::table('fine_types')->insertOrIgnore($data);

        $this->command->info('Fine types seeded successfully!');
    }
}
