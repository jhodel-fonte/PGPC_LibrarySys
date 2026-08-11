<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['id' => 1, 'type' => 'Books'],
            ['id' => 2, 'type' => 'Thesis/Dissertation'],
            ['id' => 3, 'type' => 'Periodicals'],
            ['id' => 4, 'type' => 'Reference'],
            ['id' => 5, 'type' => 'Audio/Visual'],
        ];

        $data = array_map(function ($type) use ($now) {
            return array_merge($type, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $types);

        DB::table('book_types')->insertOrIgnore($data);

        $this->command->info('Book types seeded successfully!');
    }
}
