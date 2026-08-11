<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $categories = [
            ['id' => 1, 'name' => 'Computer Science & IT'],
            ['id' => 2, 'name' => 'Mathematics'],
            ['id' => 3, 'name' => 'Physics & Chemistry'],
            ['id' => 4, 'name' => 'Literature & Fiction'],
            ['id' => 5, 'name' => 'History & Geography'],
            ['id' => 6, 'name' => 'Social Sciences'],
            ['id' => 7, 'name' => 'Philosophy & Psychology'],
            ['id' => 8, 'name' => 'Arts & Recreation'],
            ['id' => 9, 'name' => 'Business & Economics'],
            ['id' => 10, 'name' => 'Engineering & Technology'],
        ];

        $data = array_map(function ($cat) use ($now) {
            return array_merge($cat, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $categories);

        DB::table('categories')->insertOrIgnore($data);

        $this->command->info('Categories seeded successfully!');
    }
}
