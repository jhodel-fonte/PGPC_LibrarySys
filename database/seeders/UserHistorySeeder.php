<?php

namespace Database\Seeders;

use App\Models\Account;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserHistorySeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::query()->orderBy('id')->get(['id']);
        $isbns = DB::table('book_details')->whereNotNull('isbn')->whereRaw("TRIM(isbn) <> ''")
            ->orderBy('id')->pluck('isbn')->map(fn ($isbn) => trim((string) $isbn))->unique()->values();

        if ($accounts->isEmpty() || $isbns->isEmpty()) {
            $this->command->warn('User history seeder skipped: accounts or catalog ISBNs are unavailable.');
            return;
        }

        $target = min(max(200, $accounts->count() * min(12, $isbns->count())), $accounts->count() * $isbns->count());
        $baseDate = CarbonImmutable::now()->startOfDay();
        $rows = [];

        for ($index = 0; $index < $target; $index++) {
            $account = $accounts[$index % $accounts->count()];
            $isbnIndex = ($index * 7 + intdiv($index, $accounts->count())) % $isbns->count();
            $timestamp = $baseDate->subDays(1 + (($index * 17) % 365))->addMinutes(($index * 43) % 1440);
            $rows[] = ['user_id' => $account->id, 'encrypter_id' => Account::encodeId($account->id), 'isbn' => $isbns[$isbnIndex], 'rating' => $index % 4 === 0 ? 1 : 2, 'created_at' => $timestamp, 'updated_at' => $timestamp];
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('user_histories')->upsert($chunk, ['user_id', 'isbn'], ['encrypter_id', 'rating', 'updated_at']);
        }

        $this->command->info("Seeded {$target} user-history interactions dated within the past year.");
    }
}
