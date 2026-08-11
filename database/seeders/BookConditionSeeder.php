<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $conditions = [
            ['id' => 1, 'status' => 'New'],
            ['id' => 2, 'status' => 'Good'],
            ['id' => 3, 'status' => 'Fair'],
            ['id' => 4, 'status' => 'Damaged'],
            ['id' => 5, 'status' => 'Lost'],
        ];

        $data = array_map(function ($condition) use ($now) {
            return array_merge($condition, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $conditions);

        DB::table('book_conditions')->insertOrIgnore($data);

        $this->command->info('Book conditions seeded successfully!');
    }
}
