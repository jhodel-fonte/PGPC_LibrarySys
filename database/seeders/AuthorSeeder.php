<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $authors = [
            ['id' => 1, 'first_name' => 'Robert', 'last_name' => 'Martin', 'initials' => 'C.', 'pseudonym' => 'Uncle Bob'],
            ['id' => 2, 'first_name' => 'Martin', 'last_name' => 'Fowler', 'initials' => null, 'pseudonym' => null],
            ['id' => 3, 'first_name' => 'Taylor', 'last_name' => 'Otwell', 'initials' => null, 'pseudonym' => null],
            ['id' => 4, 'first_name' => 'Donald', 'last_name' => 'Knuth', 'initials' => 'E.', 'pseudonym' => null],
            ['id' => 5, 'first_name' => 'Linus', 'last_name' => 'Torvalds', 'initials' => null, 'pseudonym' => null],
            ['id' => 6, 'first_name' => 'Joanne', 'last_name' => 'Rowling', 'initials' => 'K.', 'pseudonym' => 'J.K. Rowling'],
            ['id' => 7, 'first_name' => 'George', 'last_name' => 'Martin', 'initials' => 'R.R.', 'pseudonym' => 'George R.R. Martin'],
            ['id' => 8, 'first_name' => 'Stephen', 'last_name' => 'King', 'initials' => null, 'pseudonym' => 'Richard Bachman'],
            ['id' => 9, 'first_name' => 'Harper', 'last_name' => 'Lee', 'initials' => null, 'pseudonym' => null],
            ['id' => 10, 'first_name' => 'John', 'last_name' => 'Tolkien', 'initials' => 'R.R.', 'pseudonym' => 'J.R.R. Tolkien'],
        ];

        $data = array_map(function ($author) use ($now) {
            return array_merge($author, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $authors);

        DB::table('authors')->insertOrIgnore($data);

        $this->command->info('Authors seeded successfully!');
    }
}
