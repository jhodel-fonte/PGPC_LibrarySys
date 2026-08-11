<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BorrowTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['id' => 1, 'name' => 'Regular Check-out'],
            ['id' => 2, 'name' => 'Room Use Only'],
            ['id' => 3, 'name' => 'Overnight Use'],
        ];

        $data = array_map(function ($type) use ($now) {
            return array_merge($type, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $types);

        DB::table('borrow_types')->insertOrIgnore($data);

        $this->command->info('Borrow types seeded successfully!');
    }
}
