<?php

namespace App\Console\Commands;

use App\Models\Account;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportDemoBorrowings extends Command
{
    protected $signature = 'demo:import-borrowings {csv : Path to the borrowing CSV}';

    protected $description = 'Assign historical loans to existing students as synthetic demo interactions.';

    public function handle(): int
    {
        $file = @fopen($this->argument('csv'), 'r');
        if (! $file) {
            $this->error('Cannot open the CSV file.');
            return self::FAILURE;
        }

        $header = fgetcsv($file);
        $header = $header ? array_map(fn ($value) => strtolower(trim($value, " \t\n\r\0\x0B\xEF\xBB\xBF")), $header) : [];
        $dateColumn = array_search('date_borrowed', $header, true);
        if ($dateColumn === false) {
            $dateColumn = array_search('date', $header, true);
        }
        $accessionColumn = array_search('accession_number', $header, true);
        if ($dateColumn === false || $accessionColumn === false) {
            fclose($file);
            $this->error('CSV must contain date (or date_borrowed) and accession_number columns.');
            return self::FAILURE;
        }

        $accounts = Account::query()->whereHas('student')->whereNotNull('ml_user_token')
            ->where('ml_user_token', '<>', '')->orderBy('id')->get(['id', 'ml_user_token']);
        if ($accounts->isEmpty()) {
            fclose($file);
            $this->error('No student accounts with ML tokens are available.');
            return self::FAILURE;
        }

        $inserted = 0;
        $skipped = 0;
        while (($row = fgetcsv($file)) !== false) {
            $accession = trim($row[$accessionColumn] ?? '');
            $date = trim($row[$dateColumn] ?? '');
            try {
                $borrowedAt = $date === '' ? null : CarbonImmutable::parse($date);
            } catch (Throwable $exception) {
                $borrowedAt = null;
            }
            $books = DB::table('books as b')
                ->join('book_details as bd', 'bd.id', '=', 'b.book_detail_id')
                ->whereNull('b.deleted_at')->whereNull('bd.deleted_at')
                ->select('bd.isbn', 'bd.call_number', 'bd.classification');
            $book = $accession === '' ? null : (clone $books)->where('b.accession_number', $accession)->first();
            if (! $book && ctype_digit($accession)) {
                $matches = (clone $books)->whereRaw("ltrim(b.accession_number, '0') = ?", [ltrim($accession, '0')])->limit(2)->get();
                $book = $matches->count() === 1 ? $matches->first() : null;
            }
            $isbn = trim($book->isbn ?? '');
            if (! $borrowedAt || $isbn === '') {
                $skipped++;
                continue;
            }

            // shortcut: call-number prefixes approximate subject similarity, use catalog subjects for finer groups.
            $classification = trim($book->classification ?: $book->call_number);
            preg_match('/^[A-Z]+|^\d{1,3}/i', $classification, $matches);
            $group = strtoupper($matches[0] ?? $isbn);
            $poolSize = min(5, $accounts->count());
            $start = (int) sprintf('%u', crc32($group)) % $accounts->count();
            $offset = (int) sprintf('%u', crc32($isbn.'|'.$accession.'|'.$borrowedAt->toDateString())) % $poolSize;
            $account = $accounts[($start + $offset) % $accounts->count()];
            $inserted += DB::table('user_histories')->insertOrIgnore([
                'user_id' => $account->id,
                'encrypter_id' => $account->ml_user_token,
                'isbn' => $isbn,
                'rating' => 2,
                'is_seeded' => true,
                'created_at' => $borrowedAt,
                'updated_at' => $borrowedAt,
            ]);
        }
        fclose($file);

        $this->info("Inserted {$inserted} synthetic interactions; skipped {$skipped} invalid or unmatched rows. Existing interactions were left unchanged.");
        return self::SUCCESS;
    }
}
