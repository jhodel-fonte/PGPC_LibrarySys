<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportHistoricalBorrowingLogs extends Command
{
    protected $signature = 'history:import-borrowings {csv : Path to the historical borrowing CSV}';

    protected $description = 'Import historical borrowing log entries without inventing borrowers or return dates.';

    public function handle(): int
    {
        $path = $this->argument('csv');
        $file = @fopen($path, 'r');
        if (! $file) {
            $this->error('Cannot open the CSV file.');
            return self::FAILURE;
        }

        $header = fgetcsv($file);
        $header = $header ? array_map(fn ($value) => strtolower(trim($value, " \t\n\r\0\x0B\xEF\xBB\xBF")), $header) : [];
        if (! in_array('date', $header, true) || ! in_array('accession_number', $header, true)) {
            fclose($file);
            $this->error('CSV must contain date and accession_number columns.');
            return self::FAILURE;
        }

        $inserted = 0;
        $rowNumber = 1;
        DB::transaction(function () use ($file, $header, $path, &$inserted, &$rowNumber) {
            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;
                $data = [];
                foreach ($header as $index => $column) {
                    $data[$column] = trim($row[$index] ?? '');
                }
                $inserted += DB::table('historical_borrowing_logs')->insertOrIgnore([
                    'source_file' => basename($path), 'source_row' => $rowNumber,
                    'borrowed_date' => $data['date'] ?: null,
                    'accession_number' => $data['accession_number'] ?: null,
                    'source_image' => ($data['source_image'] ?? '') ?: null,
                    'page' => ($data['page'] ?? '') ?: null,
                    'review_note' => ($data['review_note'] ?? '') ?: null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
        fclose($file);

        $this->info("Imported {$inserted} historical log entries; ".($rowNumber - 1 - $inserted).' already existed.');
        return self::SUCCESS;
    }
}
