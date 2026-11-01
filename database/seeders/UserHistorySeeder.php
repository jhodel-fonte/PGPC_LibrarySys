<?php

namespace Database\Seeders;

use App\Models\Account;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserHistorySeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::query()->whereHas('student')->orderBy('id')->get(['id']);
        $sheet = IOFactory::load(resource_path('files/seeder_book_dataset.xlsx'))->getActiveSheet();
        $pairs = [];
        for ($row = 2; $row <= min(1001, $sheet->getHighestDataRow()); $row++) {
            $accession = trim((string) $sheet->getCell("D{$row}")->getFormattedValue());
            $isbn = trim((string) $sheet->getCell("C{$row}")->getFormattedValue());
            if ($accession !== '' && $isbn !== '') {
                $pairs[$accession][$isbn] = true;
            }
        }
        $isbns = DB::table('book_details')->join('books', 'books.book_detail_id', '=', 'book_details.id')
            ->whereIn('books.accession_number', array_keys($pairs))->whereNull('books.deleted_at')
            ->whereNull('book_details.deleted_at')->select('books.accession_number', 'book_details.isbn')
            ->orderBy('book_details.id')->get()
            ->filter(fn ($book) => isset($pairs[$book->accession_number][trim((string) $book->isbn)]))
            ->map(fn ($book) => trim($book->isbn))->unique()->values();

        if ($accounts->isEmpty() || $isbns->isEmpty()) {
            $this->command->warn('User history seeder skipped: accounts or catalog ISBNs are unavailable.');
            return;
        }

        $target = min(1000, $accounts->count() * $isbns->count());
        $baseDate = CarbonImmutable::now()->startOfDay();
        $rows = [];

        for ($index = 0; $index < $target; $index++) {
            $account = $accounts[$index % $accounts->count()];
            $isbnIndex = ($index * 7 + intdiv($index, $accounts->count())) % $isbns->count();
            $timestamp = $baseDate->subDays(1 + (($index * 17) % 365))->addMinutes(($index * 43) % 1440);
            $rows[] = ['user_id' => $account->id, 'encrypter_id' => Account::encodeId($account->id), 'isbn' => $isbns[$isbnIndex], 'rating' => $index % 4 === 0 ? 1 : 2, 'is_seeded' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp];
        }

        $inserted = 0;
        foreach (array_chunk($rows, 250) as $chunk) {
            $inserted += DB::table('user_histories')->insertOrIgnore($chunk);
        }

        $this->command->info("Seeded {$inserted} demo user-history interactions (up to {$target} requested).");
    }
}
