<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetBookSeeder extends Seeder
{
    public function run(): void
    {
        $sheet = IOFactory::load(resource_path('files/seeder_book_dataset.xlsx'))->getActiveSheet();
        $now = now();
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($sheet, $now, &$created, &$skipped) {
            for ($row = 2; $row <= min(1001, $sheet->getHighestDataRow()); $row++) {
                $title = trim((string) $sheet->getCell("A{$row}")->getFormattedValue());
                $isbn = trim((string) $sheet->getCell("C{$row}")->getFormattedValue());
                $accession = trim((string) $sheet->getCell("D{$row}")->getFormattedValue());
                if ($title === '' || $isbn === '' || $accession === '') {
                    $skipped++;
                    continue;
                }
                if (DB::table('books')->where('accession_number', $accession)->exists()) {
                    $skipped++;
                    continue;
                }

                $detail = DB::table('book_details')->where('isbn', $isbn)->first();
                if (! $detail) {
                    $publisher = trim((string) $sheet->getCell("J{$row}")->getFormattedValue()) ?: 'Unknown publisher';
                    $publisherId = DB::table('publishers')->where('name', $publisher)->value('id');
                    $publisherId ??= DB::table('publishers')->insertGetId(['name' => $publisher, 'created_at' => $now, 'updated_at' => $now]);
                    $year = trim((string) $sheet->getCell("L{$row}")->getFormattedValue());
                    $dataId = DB::table('book_datas')->insertGetId([
                        'book_title' => $title, 'language_id' => 1,
                        'copyright_year' => preg_match('/^(19|20)\d{2}$/', $year) ? (int) $year : null,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                    $author = trim((string) $sheet->getCell("B{$row}")->getFormattedValue());
                    if ($author !== '') {
                        $authorId = DB::table('authors')->where('first_name', '')->where('last_name', $author)->value('id');
                        $authorId ??= DB::table('authors')->insertGetId(['first_name' => '', 'last_name' => $author, 'created_at' => $now, 'updated_at' => $now]);
                        DB::table('book_data_author')->insert(['book_data_id' => $dataId, 'author_id' => $authorId, 'created_at' => $now, 'updated_at' => $now]);
                    }
                    $detailId = DB::table('book_details')->insertGetId([
                        'book_data_id' => $dataId, 'isbn' => $isbn, 'publisher_id' => $publisherId,
                        'publication_year' => preg_match('/^(19|20)\d{2}$/', $year) ? (int) $year : null,
                        'book_type_id' => 1, 'format' => 'Book', 'call_number' => trim((string) $sheet->getCell("I{$row}")->getFormattedValue()),
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                } else {
                    $detailId = $detail->id;
                }

                DB::table('books')->insert([
                    'book_detail_id' => $detailId, 'book_condition_id' => 1,
                    'accession_number' => $accession, 'location' => trim((string) $sheet->getCell("H{$row}")->getFormattedValue()) ?: null,
                    'status' => 'available', 'created_at' => $now, 'updated_at' => $now,
                ]);
                $created++;
            }
        });

        $this->command->info("Imported {$created} spreadsheet book copies; skipped {$skipped} rows with missing data or existing accession numbers (first 1,000 rows).");
    }
}
