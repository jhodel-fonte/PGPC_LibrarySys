<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $languages = [
            ['id' => 1, 'lang' => 'English'],
            ['id' => 2, 'lang' => 'Filipino (Tagalog)'],
            ['id' => 3, 'lang' => 'Spanish'],
            ['id' => 4, 'lang' => 'Chinese'],
            ['id' => 5, 'lang' => 'Japanese'],
            ['id' => 6, 'lang' => 'German'],
            ['id' => 7, 'lang' => 'French'],
        ];

        $data = array_map(function ($lang) use ($now) {
            return array_merge($lang, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $languages);

        DB::table('languages')->insertOrIgnore($data);

        $this->command->info('Languages seeded successfully!');
    }
}
