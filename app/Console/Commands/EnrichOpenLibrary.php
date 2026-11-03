<?php

namespace App\Console\Commands;

use App\Models\BookDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class EnrichOpenLibrary extends Command
{
    protected $signature = 'catalog:enrich-openlibrary {email : Contact email for Open Library} {--after=0 : Resume after this book detail ID} {--dry-run : Preview changes without saving}';
    protected $description = 'Fill missing catalog metadata using ISBN matches from Open Library';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! ctype_digit((string) $this->option('after'))) {
            $this->error('Provide a valid contact email and a non-negative --after ID.');
            return self::FAILURE;
        }

        $last = (int) $this->option('after');
        $this->warn('Open Library requests bulk imports use its data dumps or prior coordination. This command may make many requests.');
        $this->line('Only blank description, subtitle, pages, publication year and cover are filled; existing values are preserved.');
        try {
            BookDetail::query()->with('bookData')->where('id', '>', $last)->whereNotNull('isbn')
                ->whereHas('bookData')->orderBy('id')->chunkById(20, function ($books) use ($email, &$last) {
                    $numbers = $books->mapWithKeys(function ($book) {
                        $isbn = strtoupper(preg_replace('/[^0-9X]/i', '', $book->isbn));
                        return preg_match('/^(?:[0-9]{9}[0-9X]|[0-9]{13})$/', $isbn) ? [$book->id => $isbn] : [];
                    });
                    $results = [];
                    if ($numbers->isNotEmpty()) {
                        $keys = $numbers->unique()->map(fn ($isbn) => 'ISBN:'.$isbn)->implode(',');
                        $results = $this->fetch('https://openlibrary.org/api/books', $email, ['bibkeys' => $keys, 'format' => 'json', 'jscmd' => 'details']);
                    }
                    foreach ($books as $book) {
                        $isbn = $numbers[$book->id] ?? null;
                        $entry = $isbn ? ($results['ISBN:'.$isbn] ?? null) : null;
                        if (is_array($entry) && is_array($entry['details'] ?? null)) {
                            $details = $entry['details'];
                            $description = $this->text($details['description'] ?? null);
                            $work = data_get($details, 'works.0.key');
                            if (! $description && blank($book->bookData?->description) && is_string($work) && preg_match('~^/works/OL[0-9]+W$~', $work)) {
                                $description = $this->text($this->fetch('https://openlibrary.org'.$work.'.json', $email)['description'] ?? null);
                            }
                            $this->apply($book, $details, $entry, $description);
                        } else {
                            $this->line("ID {$book->id}: no ISBN match or invalid ISBN");
                        }
                        $last = $book->id;
                        $this->line("Resume after ID: {$last}");
                    }
                });
        } catch (Throwable $exception) {
            $this->error('Stopped: '.$exception->getMessage());
            $this->error("Resume with --after={$last}");
            return self::FAILURE;
        }
        $this->info('Finished. Records not found or fields unavailable from Open Library remain unchanged.');
        return self::SUCCESS;
    }

    private function fetch(string $url, string $email, array $query = []): array
    {
        static $lastRequest = 0.0;
        $wait = 1.1 - (microtime(true) - $lastRequest);
        if ($wait > 0) {
            usleep((int) ($wait * 1000000));
        }
        $lastRequest = microtime(true);
        $response = Http::withHeaders(['User-Agent' => "PGPC library catalog ({$email})"])->timeout(30)->get($url, $query);
        if (! $response->successful()) {
            throw new \RuntimeException('Open Library returned HTTP '.$response->status().'. Wait before resuming.');
        }
        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('Open Library returned an invalid response.');
        }
        return $json;
    }

    private function text(mixed $value): ?string
    {
        $value = is_array($value) ? ($value['value'] ?? null) : $value;
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function apply(BookDetail $book, array $details, array $entry, ?string $description): void
    {
        $subtitle = $this->text($details['subtitle'] ?? null);
        $pages = $details['number_of_pages'] ?? null;
        $date = $details['publish_date'] ?? '';
        $year = is_string($date) && preg_match('/\b(1[5-9][0-9]{2}|20[0-9]{2})\b/', $date, $match) ? (int) $match[1] : null;
        $cover = $entry['thumbnail_url'] ?? null;
        $cover = is_string($cover) && preg_match('~^https://covers\.openlibrary\.org/b/id/[0-9]+-[SML]\.jpg$~', $cover) ? $cover : null;
        DB::transaction(function () use ($book, $description, $subtitle, $pages, $year, $cover) {
            $current = BookDetail::whereKey($book->id)->lockForUpdate()->first();
            if (! $current || $current->isbn !== $book->isbn || $current->book_data_id !== $book->book_data_id) {
                return;
            }
            $data = $current->bookData()->lockForUpdate()->first();
            if (! $data) {
                return;
            }
            $changes = [];
            foreach (['description' => $description, 'subtitle' => $subtitle && mb_strlen($subtitle) <= 255 ? $subtitle : null] as $field => $value) {
                if (blank($data->$field) && $value !== null) {
                    $data->$field = $value;
                    $changes[] = $field;
                }
            }
            foreach (['pages' => is_int($pages) && $pages > 0 ? $pages : null, 'publication_year' => $year, 'cover_image' => $cover] as $field => $value) {
                if (blank($current->$field) && $value !== null) {
                    $current->$field = $value;
                    $changes[] = $field;
                }
            }
            if (! $this->option('dry-run')) {
                $data->save();
                $current->save();
            }
            $this->line('ID '.$book->id.': '.($changes ? implode(', ', $changes) : 'no missing fields available'));
        });
    }
}
