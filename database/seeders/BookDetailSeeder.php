<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get all seeded book data
        $bookDatas = DB::table('book_datas')->get();

        $details = [
            [
                'isbn' => '9780132350884',
                'issn' => null,
                'publication_year' => 2008,
                'copyright_year' => 2008,
                'edition' => '1st Edition',
                'pages' => 464,
                'format' => 'Paperback',
                'book_type_id' => 1, // Books
                'call_number' => 'QA76.76.D47 M37 2008',
                'classification' => 'QA76.76.D47',
                'publisher_id' => 3, // Pearson Education
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/41xShTxONmL._SX396_BO1,204,203,200_.jpg',
                'url' => 'https://www.oreilly.com/library/view/clean-code-a/9780136083238/',
            ],
            [
                'isbn' => '9780134757599',
                'issn' => null,
                'publication_year' => 2018,
                'copyright_year' => 2018,
                'edition' => '2nd Edition',
                'pages' => 448,
                'format' => 'Hardcover',
                'book_type_id' => 1, // Books
                'call_number' => 'QA76.76.R42 F69 2018',
                'classification' => 'QA76.76.R42',
                'publisher_id' => 3, // Pearson Education
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/41yEd1lH4ML._SX398_BO1,204,203,200_.jpg',
                'url' => 'https://martinfowler.com/books/refactoring.html',
            ],
            [
                'isbn' => '9781492041214',
                'issn' => null,
                'publication_year' => 2019,
                'copyright_year' => 2019,
                'edition' => '2nd Edition',
                'pages' => 544,
                'format' => 'Paperback',
                'book_type_id' => 1, // Books
                'call_number' => 'QA76.73.P224 S73 2019',
                'classification' => 'QA76.73.P224',
                'publisher_id' => 1, // O'Reilly
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51wB7-O9N2L._SX379_BO1,204,203,200_.jpg',
                'url' => 'https://laravelupandrunning.com/',
            ],
            [
                'isbn' => '9780201896831',
                'issn' => null,
                'publication_year' => 1997,
                'copyright_year' => 1997,
                'edition' => '3rd Edition',
                'pages' => 672,
                'format' => 'Hardcover',
                'book_type_id' => 4, // Reference
                'call_number' => 'QA76.6 .K58 1997 v.1',
                'classification' => 'QA76.6',
                'publisher_id' => 3, // Pearson Education
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/41A3ZFX3WHL._SX331_BO1,204,203,200_.jpg',
                'url' => 'https://www-cs-faculty.stanford.edu/~knuth/taocp.html',
            ],
            [
                'isbn' => '9780066620732',
                'issn' => null,
                'publication_year' => 2001,
                'copyright_year' => 2001,
                'edition' => '1st Edition',
                'pages' => 320,
                'format' => 'Hardcover',
                'book_type_id' => 1, // Books
                'call_number' => 'QA76.2.T67 A3 2001',
                'classification' => 'QA76.2.T67',
                'publisher_id' => 6, // HarperCollins
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51F8W7K8G3L._SX322_BO1,204,203,200_.jpg',
                'url' => null,
            ],
            [
                'isbn' => '9780747532699',
                'issn' => null,
                'publication_year' => 1997,
                'copyright_year' => 1997,
                'edition' => '1st Edition',
                'pages' => 223,
                'format' => 'Paperback',
                'book_type_id' => 1, // Books
                'call_number' => 'PR6068.O93 H37 1997',
                'classification' => 'PR6068.O93',
                'publisher_id' => 5, // Penguin Books
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51uO1p5d1AL._SX307_BO1,204,203,200_.jpg',
                'url' => null,
            ],
            [
                'isbn' => '9780553103540',
                'issn' => null,
                'publication_year' => 1996,
                'copyright_year' => 1996,
                'edition' => 'Hardcover Edition',
                'pages' => 694,
                'format' => 'Hardcover',
                'book_type_id' => 1, // Books
                'call_number' => 'PS3563.A7239 G36 1996',
                'classification' => 'PS3563.A7239',
                'publisher_id' => 5, // Penguin Books
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51Q1-y-u9TL._SX324_BO1,204,203,200_.jpg',
                'url' => 'https://www.georgerrmartin.com/book-category/a-song-of-ice-and-fire/',
            ],
            [
                'isbn' => '9780385121682',
                'issn' => null,
                'publication_year' => 1977,
                'copyright_year' => 1977,
                'edition' => '1st Edition',
                'pages' => 447,
                'format' => 'Hardcover',
                'book_type_id' => 1, // Books
                'call_number' => 'PS3561.I483 S5 1977',
                'classification' => 'PS3561.I483',
                'publisher_id' => 5, // Penguin Books
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51V1B6H-08L._SX302_BO1,204,203,200_.jpg',
                'url' => 'https://www.stephenking.com/works/novel/shining.html',
            ],
            [
                'isbn' => '9780446310789',
                'issn' => null,
                'publication_year' => 1960,
                'copyright_year' => 1960,
                'edition' => 'Popular Edition',
                'pages' => 281,
                'format' => 'Paperback',
                'book_type_id' => 1, // Books
                'call_number' => 'PS3562.E353 T6 1960',
                'classification' => 'PS3562.E353',
                'publisher_id' => 6, // HarperCollins
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51IX4994-YL._SX307_BO1,204,203,200_.jpg',
                'url' => null,
            ],
            [
                'isbn' => '9780048231888',
                'issn' => null,
                'publication_year' => 1937,
                'copyright_year' => 1937,
                'edition' => 'Collector\'s Edition',
                'pages' => 310,
                'format' => 'Hardcover',
                'book_type_id' => 1, // Books
                'call_number' => 'PR6039.O32 H6 1937',
                'classification' => 'PR6039.O32',
                'publisher_id' => 8, // Oxford University Press
                'cover_image' => 'https://images-na.ssl-images-amazon.com/images/I/51S-p6K1YEL._SX326_BO1,204,203,200_.jpg',
                'url' => 'https://www.tolkienestate.com/writing/the-hobbit/',
            ]
        ];

        foreach ($bookDatas as $index => $bookData) {
            if (isset($details[$index])) {
                DB::table('book_details')->insertOrIgnore(array_merge($details[$index], [
                    'id' => $bookData->id,
                    'book_data_id' => $bookData->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        $this->command->info('Book edition details seeded successfully!');
    }
}
