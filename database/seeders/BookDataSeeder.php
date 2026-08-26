<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $books = [
            [
                'id' => 1,
                'book_title' => 'Clean Code',
                'subtitle' => 'A Handbook of Agile Software Craftsmanship',
                'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees.',
                'series_title' => 'Robert C. Martin Series',
                'note' => 'Classic programming textbook.',
                'language_id' => 1, // English
                'copyright_year' => 2008,
                'marc_record' => '00000cam a22000001i 4500',
            ],
            [
                'id' => 2,
                'book_title' => 'Refactoring',
                'subtitle' => 'Improving the Design of Existing Code',
                'description' => 'Refactoring is a disciplined technique for restructuring an existing body of code, altering its internal structure without changing its external behavior.',
                'series_title' => 'Addison-Wesley Signature Series',
                'note' => 'Second Edition.',
                'language_id' => 1, // English
                'copyright_year' => 2018,
                'marc_record' => '00000cam a22000002i 4500',
            ],
            [
                'id' => 3,
                'book_title' => 'Laravel: Up and Running',
                'subtitle' => 'A Framework for Building Modern PHP Applications',
                'description' => 'What makes Laravel special? Compassion. In this book, Matt Stauffer explains how the framework helps you create PHP applications quickly and elegantly.',
                'series_title' => null,
                'note' => 'Essential Laravel reference guide.',
                'language_id' => 1, // English
                'copyright_year' => 2019,
                'marc_record' => '00000cam a22000003i 4500',
            ],
            [
                'id' => 4,
                'book_title' => 'The Art of Computer Programming',
                'subtitle' => 'Volume 1: Fundamental Algorithms',
                'description' => 'This volume begins with basic programming concepts and techniques, and then focuses more particularly on information structures.',
                'series_title' => 'The Art of Computer Programming Series',
                'note' => 'Third Edition.',
                'language_id' => 1, // English
                'copyright_year' => 1997,
                'marc_record' => '00000cam a22000004i 4500',
            ],
            [
                'id' => 5,
                'book_title' => 'Just for Fun',
                'subtitle' => 'The Story of an Accidental Revolutionary',
                'description' => 'The autobiography of Linus Torvalds, the creator of the Linux kernel.',
                'series_title' => null,
                'note' => 'Autobiographical work.',
                'language_id' => 1, // English
                'copyright_year' => 2001,
                'marc_record' => '00000cam a22000005i 4500',
            ],
            [
                'id' => 6,
                'book_title' => 'Harry Potter and the Philosopher\'s Stone',
                'subtitle' => null,
                'description' => 'Harry Potter has never even heard of Hogwarts when the letters start dropping on the doormat at number four, Privet Drive.',
                'series_title' => 'Harry Potter Series',
                'note' => 'Fantasy novel.',
                'language_id' => 1, // English
                'copyright_year' => 1997,
                'marc_record' => '00000cam a22000006i 4500',
            ],
            [
                'id' => 7,
                'book_title' => 'A Game of Thrones',
                'subtitle' => null,
                'description' => 'Here is the first volume in George R. R. Martin\'s magnificent cycle of novels that includes A Clash of Kings and A Storm of Swords.',
                'series_title' => 'A Song of Ice and Fire',
                'note' => 'Epic fantasy.',
                'language_id' => 1, // English
                'copyright_year' => 1996,
                'marc_record' => '00000cam a22000007i 4500',
            ],
            [
                'id' => 8,
                'book_title' => 'The Shining',
                'subtitle' => null,
                'description' => 'Jack Torrance\'s new job at the Overlook Hotel is the perfect chance for a fresh start.',
                'series_title' => null,
                'note' => 'Horror classic.',
                'language_id' => 1, // English
                'copyright_year' => 1977,
                'marc_record' => '00000cam a22000008i 4500',
            ],
            [
                'id' => 9,
                'book_title' => 'To Kill a Mockingbird',
                'subtitle' => null,
                'description' => 'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it.',
                'series_title' => null,
                'note' => 'Pulitzer Prize winner.',
                'language_id' => 1, // English
                'copyright_year' => 1960,
                'marc_record' => '00000cam a22000009i 4500',
            ],
            [
                'id' => 10,
                'book_title' => 'The Hobbit',
                'subtitle' => 'There and Back Again',
                'description' => 'Written for J.R.R. Tolkien\'s own children, The Hobbit met with instant critical acclaim when it was first published.',
                'series_title' => 'Middle-earth Legendarium',
                'note' => 'Classic children\'s fantasy.',
                'language_id' => 1, // English
                'copyright_year' => 1937,
                'marc_record' => '00000cam a22000010i 4500',
            ]
        ];

        foreach ($books as $index => $book) {
            // Check if this book data already exists by ID
            $existing = DB::table('book_datas')->where('id', $book['id'])->first();
            
            if (!$existing) {
                // Insert book data
                $bookDataId = DB::table('book_datas')->insertGetId(array_merge($book, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            } else {
                $bookDataId = $existing->id;
            }

            // Associate Author (using author index matching, offset by 1 for DB auto-increment ID)
            $authorId = $index + 1; 
            DB::table('book_data_author')->insertOrIgnore([
                'book_data_id' => $bookDataId,
                'author_id' => $authorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Associate Category
            // Computer Science & IT (ID 1) for index 0, 1, 2, 3
            // History & Geography (ID 5) for index 4
            // Literature & Fiction (ID 4) for index 5, 6, 7, 8, 9
            $categoryId = 4; // Default Fiction
            if ($index < 4) {
                $categoryId = 1; // Computer Science
            } elseif ($index == 4) {
                $categoryId = 5; // History & Bio
            }

            DB::table('book_data_category')->insertOrIgnore([
                'book_data_id' => $bookDataId,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Book metadata and associations seeded successfully!');
    }
}
