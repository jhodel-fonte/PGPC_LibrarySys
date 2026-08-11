<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $publishers = [
            ['id' => 1, 'name' => "O'Reilly Media"],
            ['id' => 2, 'name' => 'Packt Publishing'],
            ['id' => 3, 'name' => 'Pearson Education'],
            ['id' => 4, 'name' => 'McGraw-Hill Education'],
            ['id' => 5, 'name' => 'Penguin Books'],
            ['id' => 6, 'name' => 'HarperCollins'],
            ['id' => 7, 'name' => 'Cambridge University Press'],
            ['id' => 8, 'name' => 'Oxford University Press'],
            ['id' => 9, 'name' => 'Springer Science+Business Media'],
            ['id' => 10, 'name' => 'John Wiley & Sons'],
        ];

        $data = array_map(function ($pub) use ($now) {
            return array_merge($pub, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $publishers);

        DB::table('publishers')->insertOrIgnore($data);

        $this->command->info('Publishers seeded successfully!');
    }
}
