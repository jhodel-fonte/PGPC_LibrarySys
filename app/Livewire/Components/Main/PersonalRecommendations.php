<?php

namespace App\Livewire\Components\Main;

use App\Models\BookDetail;
use App\Models\UserHistory;
use App\Services\RecommendationService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class PersonalRecommendations extends Component
{
    #[Locked]
    public ?int $bookDetailId = null;

    #[Locked]
    public bool $catalogList = false;

    public bool $loaded = false;
    public array $books = [];

    public function mount(?int $bookDetailId = null, bool $catalogList = false): void
    {
        $this->bookDetailId = $bookDetailId;
        $this->catalogList = $catalogList;
    }

    public function loadRecommendations(RecommendationService $service): void
    {
        $account = auth()->user();
        if (! $account || ! $account->isStudent()) {
            $this->loaded = true;
            return;
        }

        $failed = false;
        $outcome = null;
        $matchedCount = 0;
        $unmatched = [];
        try {
            $stored = $account->recommendation;
            if ($this->bookDetailId) {
                $book = BookDetail::publicCatalog()->whereHas('bookData', fn ($query) => $query->whereNull('deleted_at'))->findOrFail($this->bookDetailId);
                $isbn = trim((string) ($book->isbn ?: $book->issn));
                if ($isbn !== '') {
                    UserHistory::upsertHighestRating((int) $account->id, $isbn, UserHistory::RATING_VIEW);
                    $stored = $service->refreshFor($account, $isbn, UserHistory::RATING_VIEW);
                } else {
                    $outcome = 'missing_book_identifier';
                }
            } elseif ((! $stored || ! $stored->generated_at || $stored->generated_at->lt(now()->subHours(6))) && $account->userHistories()->exists()) {
                $stored = $service->refreshFor($account);
            }

            $matchedBooks = $stored?->getActiveBooks();
            if ($stored) {
                $normalize = fn ($isbn) => strtoupper(str_replace(['-', ' '], '', trim((string) $isbn)));
                $matchedIsbns = $matchedBooks->map(fn ($book) => $normalize($book->isbn))->all();
                $unmatched = array_values(array_filter($stored->isbns ?? [], fn ($isbn) => ! in_array($normalize($isbn), $matchedIsbns, true)));
                $matchedCount = $matchedBooks->count();
                $outcome = empty($stored->isbns) ? 'empty_recommendations' : ($matchedCount === 0 ? 'no_catalog_matches' : ($unmatched ? 'partial_catalog_matches' : 'matched'));
            } elseif (! $outcome) {
                $outcome = 'no_recommendation_history';
            }

            $this->books = $matchedBooks
                ?->where('id', '!=', $this->bookDetailId)
                ->take(10)
                ->map(function ($item) {
                    $data = $item->bookData;
                    $copies = $item->books;
                    $available = $copies->contains(fn ($copy) => strtolower($copy->status ?? '') === 'available');
                    return [
                        'id' => $item->id,
                        'accession_no' => $copies->first()?->accession_number,
                        'title' => $data?->book_title ?: 'Untitled',
                        'author' => $data?->authors->map(fn ($author) => trim($author->first_name.' '.$author->last_name))->filter()->implode(', ') ?: 'Unknown Author',
                        'category' => $data?->categories?->first()?->name ?: ($item->classification ?: 'General'),
                        'cover_url' => $item->cover_url,
                        'cover' => $item->cover_url,
                        'has_custom_cover' => ! empty($item->cover_image),
                        'call_no' => $item->call_number ?: 'N/A',
                        'year' => $item->publication_year ?: ($data?->copyright_year ?: 'N/A'),
                        'type' => $item->bookType?->type ?: 'Book',
                        'format' => $item->format ?: 'Book',
                        'pages' => $item->pages ? $item->pages.' p.' : 'N/A',
                        'location' => $copies->first()?->location ?: 'Main Library',
                        'status' => $available ? 'available' : 'unavailable',
                        'status_label' => $available ? 'Available' : 'Unavailable',
                        'status_color' => $available ? 'text-emerald-700' : 'text-rose-700',
                        'dot_color' => $available ? 'bg-emerald-500' : 'bg-rose-500',
                    ];
                })->values()->all() ?? [];
            if ($outcome === 'matched' && ! $this->books) {
                $outcome = 'no_other_books';
            }
        } catch (Throwable $exception) {
            report($exception);
            $failed = true;
        }
        $debug = $service->getLastRecommendDebug();
        if ($debug || $failed || $outcome) {
            $result = $failed ? ($debug['reason'] ?? 'processing_error') : $outcome;
            $this->dispatch('ml-recommendation-response', response: [
                'status' => $debug['status'] ?? null,
                'body' => $debug['raw'] ?? null,
                'error' => $failed || ($debug && ! ($debug['available'] ?? false)) ? 'Recommendations are temporarily unavailable.' : null,
                'result' => $result,
                'message' => match ($result) {
                    'no_catalog_matches' => 'None of the recommended ISBNs match an active catalog book with a visible copy.',
                    'partial_catalog_matches' => 'Some recommended ISBNs have no active catalog match.',
                    'empty_recommendations' => 'The recommendation service returned no books.',
                    'invalid_response' => 'The recommendation service returned an invalid response or no valid ISBNs.',
                    'http_error' => 'The recommendation service returned an HTTP error.',
                    'connection_error' => 'The recommendation service could not be reached.',
                    'missing_user_identifier' => 'No encrypted user identifier is available.',
                    'missing_book_identifier' => 'The viewed book has no ISBN or ISSN.',
                    'no_recommendation_history' => 'No recommendation history is available yet.',
                    'no_other_books' => 'The only matching recommendation is the book currently being viewed.',
                    'processing_error' => 'Recommendations could not be processed; check the server log.',
                    default => 'Recommendations matched to catalog books.',
                },
                'matched_count' => $matchedCount,
                'displayed_count' => count($this->books),
                'unmatched_isbns' => $unmatched,
                'invalid_count' => $debug['invalid_count'] ?? 0,
            ]);
        }
        $this->loaded = true;
    }

    public function render()
    {
        return view('livewire.components.main.personal-recommendations');
    }
}
