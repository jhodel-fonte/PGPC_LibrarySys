<?php

namespace App\Livewire\Pages\Main;

use App\Models\Book as BookModel;
use App\Models\BookDetail as BookDetailModel;
use App\Models\BookReservation;
use App\Models\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Book extends Component
{
    public int|string|null $identifier = null;
    public ?int $bookDetailId = null;
    public ?array $book = null;
    public string $activeTab = 'overview';
    public bool $isBookmarked = false;
    public bool $reservationModalOpen = false;
    public ?string $reserveStatus = null;
    public ?string $reserveMessage = null;
    public bool $readyToLoad = false;

    public function mount($identifier = null)
    {
        $this->identifier = $identifier ?? request('identifier') ?? request('id');
        $this->loadBookData();
        $this->readyToLoad = true;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleBookmark(): void
    {
        $list = session()->get('my_book_list', []);
        $bookId = $this->book['accession_no'] ?? $this->book['id'] ?? null;

        if ($bookId) {
            if (in_array($bookId, $list)) {
                $list = array_diff($list, [$bookId]);
                $this->isBookmarked = false;
            } else {
                $list[] = $bookId;
                $this->isBookmarked = true;
            }
            session()->put('my_book_list', array_values($list));
        }
    }

    public function openReserveModal()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        if ($user && method_exists($user, 'isStudent') && $user->isStudent() && !$user->isApprovedStudent()) {
            return redirect()->to($user->getVerificationRedirectRoute());
        }
        $this->reserveStatus = null;
        $this->reservationModalOpen = true;
    }

    public function closeReserveModal(): void
    {
        $this->reservationModalOpen = false;
        $this->reserveStatus = null;
    }

    public function confirmReservation()
    {
        return redirect()->route('student.reservations');
    }

    public function loadBookData(): void
    {
        $record = null;
        $copyByAccession = null;

        $eagerRelations = [
            'bookData.authors',
            'bookData.categories',
            'bookData.language',
            'bookType',
            'publisher',
            'books' => function ($q) {
                $q->whereNull('deleted_at')
                  ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived']);
            },
            'books.condition',
            'books.borrowingTransactions' => function ($q) {
                $q->whereNull('return_date')->orderBy('due_date', 'asc');
            },
            'books.reservations' => function ($q) {
                $q->whereNull('cancelled_date')->whereNull('fulfilled_date')->orderBy('due_date', 'asc');
            }
        ];

        if (!empty($this->identifier)) {
            try {
                // 1. Lookup by physical copy accession number
                $copyByAccession = BookModel::where('accession_number', $this->identifier)
                    ->whereNull('deleted_at')
                    ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived'])
                    ->first();
                if ($copyByAccession && $copyByAccession->book_detail_id) {
                    $record = BookDetailModel::with($eagerRelations)
                        ->whereNull('deleted_at')
                        ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
                        ->find($copyByAccession->book_detail_id);
                }

                // 2. Lookup by numeric ID
                if (!$record && is_numeric($this->identifier)) {
                    $record = BookDetailModel::with($eagerRelations)
                        ->whereNull('deleted_at')
                        ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
                        ->find((int) $this->identifier);

                    if (!$record) {
                        $copyById = BookModel::where('id', (int) $this->identifier)
                            ->whereNull('deleted_at')
                            ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived'])
                            ->first();
                        if ($copyById && $copyById->book_detail_id) {
                            $record = BookDetailModel::with($eagerRelations)
                                ->whereNull('deleted_at')
                                ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
                                ->find($copyById->book_detail_id);
                            $copyByAccession = $copyById;
                        }
                    }
                }

                // 3. Lookup by ISBN or Call Number
                if (!$record) {
                    $record = BookDetailModel::with($eagerRelations)
                        ->whereNull('deleted_at')
                        ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
                        ->where(function ($q) {
                            $q->where('isbn', $this->identifier)
                              ->orWhere('call_number', $this->identifier);
                        })
                        ->first();
                }
            } catch (\Throwable $e) {
                // Fallback handled below
            }
        }

        // Abort 404: do not show soft-deleted or non-existent books
        if (!$record || $record->trashed() || !in_array($record->catalog_kind, ['book', 'magazine', 'journal'], true)) {
            abort(404, 'The requested book is unavailable or has been removed from the catalog.');
        }

        if ($record && $record->bookData) {
            $this->bookDetailId = $record->id;
            $bdata = $record->bookData;
            $copies = $record->books ? $record->books->filter(function ($c) {
                return !$c->trashed() && !in_array(strtolower($c->status ?? ''), ['disabled', 'archived']);
            }) : collect();

            if ($copies->isEmpty()) {
                abort(404, 'No active copies are currently available for this book.');
            }

            $totalCopies = $copies->count();
            $availableCopies = $copies->filter(fn($c) => strtolower($c->status ?? '') === 'available')->count();
            $firstAvail = $copies->first(fn($c) => strtolower($c->status ?? '') === 'available');

            $status = 'checked_out';
            $statusLabel = 'Checked Out';
            $statusColor = 'text-rose-600';
            $dotColor = 'bg-rose-500';

            if ($availableCopies > 0) {
                $status = 'available';
                $statusLabel = 'Available';
                $statusColor = 'text-emerald-700';
                $dotColor = 'bg-emerald-500';
            } elseif ($totalCopies === 0) {
                $status = 'reference_only';
                $statusLabel = 'Reference Only';
                $statusColor = 'text-blue-600';
                $dotColor = 'bg-blue-500';
            } elseif ($copies->filter(fn($c) => strtolower($c->status ?? '') === 'reserved')->count() === $totalCopies) {
                $status = 'reserved';
                $statusLabel = 'Reserved';
                $statusColor = 'text-amber-600';
                $dotColor = 'bg-amber-500';
            }

            $holdings = $copies->map(function ($c) use ($record) {
                $activeTx = $c->borrowingTransactions->first();
                $activeRes = $c->reservations->first();
                $dueDate = '—';

                if ($c->status === 'borrowed' && $activeTx && $activeTx->due_date) {
                    $dueDate = Carbon::parse($activeTx->due_date)->format('M d, Y');
                } elseif ($c->status === 'reserved') {
                    $dueDate = $activeRes && $activeRes->due_date
                        ? 'Pick up by ' . Carbon::parse($activeRes->due_date)->format('M d, Y')
                        : 'Pick up by ' . now()->addDays(2)->format('M d, Y');
                }

                $statColor = match (strtolower($c->status ?? '')) {
                    'available' => 'text-emerald-700',
                    'borrowed' => 'text-rose-600',
                    'reserved' => 'text-amber-600',
                    default => 'text-slate-600',
                };

                $dot = match (strtolower($c->status ?? '')) {
                    'available' => 'bg-emerald-500',
                    'borrowed' => 'bg-rose-500',
                    'reserved' => 'bg-amber-500',
                    default => 'bg-slate-400',
                };

                return [
                    'id' => $c->id,
                    'accession_no' => $c->accession_number ?: 'N/A',
                    'code' => $c->code ?: 'N/A',
                    'call_no' => $c->call_number ?: ($record->call_number ?: 'N/A'),
                    'location' => $c->location ?: 'Main Library',
                    'collection' => 'General Collection',
                    'condition' => $c->condition ? $c->condition->status : 'Good',
                    'status' => $c->status ?: 'available',
                    'status_label' => ucfirst($c->status ?: 'available'),
                    'status_color' => $statColor,
                    'dot_color' => $dot,
                    'due_date' => $dueDate,
                ];
            })->toArray();

            $authorNames = $bdata->authors->isNotEmpty()
                ? $bdata->authors->map(fn($a) => trim($a->first_name . ' ' . $a->last_name))->filter()->implode(', ')
                : 'Unknown Author';

            $subjects = $bdata->categories->isNotEmpty()
                ? $bdata->categories->pluck('name')->toArray()
                : ['General Collection'];

            $keywords = [];
            if (!empty($bdata->keywords)) {
                $keywords = is_array($bdata->keywords) ? $bdata->keywords : array_map('trim', explode(',', $bdata->keywords));
            }
            if (empty($keywords)) {
                $keywords = array_slice($subjects, 0, 5);
            }

            $primaryAccession = $copyByAccession?->accession_number
                ?: ($copies->first()?->accession_number ?: 'N/A');

            $this->book = [
                'id' => $record->id,
                'book_detail_id' => $record->id,
                'available_book_id' => $firstAvail?->id,
                'title' => $bdata->book_title ?: 'Untitled',
                'subtitle' => $bdata->subtitle ?: null,
                'author' => $authorNames,
                'year' => $record->publication_year ?: ($bdata->copyright_year ?: 'N/A'),
                'type' => $record->bookType ? $record->bookType->name : 'Book',
                'format' => $record->bookType ? $record->bookType->name : 'Physical Book',
                'pages' => $record->pagination ?: 'N/A',
                'language' => $bdata->language ? $bdata->language->name : 'English',
                'call_no' => $record->call_number ?: 'N/A',
                'accession_no' => $primaryAccession,
                'isbn' => $record->isbn ?: ($record->issn ?: 'N/A'),
                'subjects' => $subjects,
                'category' => $subjects[0] ?? null,
                'description' => $bdata->description ?: ($bdata->note ?: 'No detailed description cataloged for this resource.'),
                'keywords' => $keywords,
                'cover_url' => $record->cover_url ?: null,
                'resource_url' => $record->url,
                'issue' => $record->periodicalIssue?->toArray(),
                'other_issues' => in_array($record->catalog_kind, ['magazine', 'journal'], true)
                    ? BookDetailModel::publicCatalog()->where('book_data_id', $record->book_data_id)
                        ->where('catalog_kind', $record->catalog_kind)->whereKeyNot($record->id)
                        ->whereHas('books', fn ($q) => $q->whereNull('deleted_at')->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived']))
                        ->with('periodicalIssue')->orderByDesc('id')->limit(50)->get()
                        ->map(fn ($item) => ['id' => $item->id, 'label' => collect([
                            $item->periodicalIssue?->volume ? 'Volume '.$item->periodicalIssue->volume : null,
                            $item->periodicalIssue?->issue_number ? 'Issue '.$item->periodicalIssue->issue_number : null,
                            $item->periodicalIssue?->issue_label,
                            $item->periodicalIssue?->publication_date?->format('M j, Y'),
                        ])->filter()->join(' · ') ?: 'Issue #'.$item->id])->all()
                    : [],
                'has_custom_cover' => !empty($record->cover_image),
                'default_cover' => asset('images/' . config('settings.book_covers.default', config('settings.coverFile_default', 'book-cover.webp'))),
                'publisher' => $record->publisher ? $record->publisher->name : 'N/A',
                'edition' => $record->edition ?: 'N/A',
                'classification' => $record->ddc_classification ?: ($record->classification ?: 'N/A'),
                'total_copies' => $totalCopies,
                'available_copies' => $availableCopies,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'dot_color' => $dotColor,
                'can_reserve' => $availableCopies > 0 && ($record->periodicalIssue?->borrowable ?? true),
                'holdings' => $holdings,
            ];
        } else {
            $this->book = null;
        }

        // Check bookmark status
        $list = session()->get('my_book_list', []);
        $bookId = $this->book['accession_no'] ?? $this->book['id'] ?? null;
        $this->isBookmarked = $bookId && in_array($bookId, $list);

    }

    public function getSimilarBooks(): array
    {
        if (!$this->bookDetailId) {
            return [];
        }

        try {
            $current = BookDetailModel::with('bookData.categories')
                ->whereNull('deleted_at')
                ->find($this->bookDetailId);
            $catIds = $current?->bookData?->categories?->pluck('id')->filter()->toArray() ?? [];

            $activeBooksScope = function ($query) {
                $query->whereNull('deleted_at')
                    ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
                    ->whereHas('books', function ($q) {
                        $q->whereNull('deleted_at')
                          ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived']);
                    });
            };

            $copiesScope = function ($q) {
                $q->whereNull('deleted_at')
                  ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived']);
            };

            // 1. Primary Query: Books sharing the same category/categories
            $similar = collect();
            if (!empty($catIds)) {
                $similar = BookDetailModel::publicCatalog()->with(['bookData.authors', 'bookData.categories', 'bookType', 'books' => $copiesScope])
                    ->tap($activeBooksScope)
                    ->where('id', '!=', $this->bookDetailId)
                    ->whereHas('bookData.categories', function ($q) use ($catIds) {
                        $q->whereIn('categories.id', $catIds);
                    })
                    ->limit(10)
                    ->get();
            }

            // 2. Secondary fallback: If fewer than 6 books found by exact category, complement with same classification or book type
            if ($similar->count() < 6) {
                $excludedIds = $similar->pluck('id')->push($this->bookDetailId)->toArray();
                $additionalQuery = BookDetailModel::publicCatalog()->with(['bookData.authors', 'bookData.categories', 'bookType', 'books' => $copiesScope])
                    ->tap($activeBooksScope)
                    ->whereNotIn('id', $excludedIds);

                if (!empty($current?->classification)) {
                    $additionalQuery->where('classification', $current->classification);
                } elseif (!empty($current?->book_type_id)) {
                    $additionalQuery->where('book_type_id', $current->book_type_id);
                }

                $additional = $additionalQuery->limit(10 - $similar->count())->get();
                $similar = $similar->concat($additional);

                // 3. Fallback: Fill remaining slots if still fewer than 4 books
                if ($similar->count() < 4) {
                    $excludedIds = $similar->pluck('id')->push($this->bookDetailId)->toArray();
                    $fillers = BookDetailModel::publicCatalog()->with(['bookData.authors', 'bookData.categories', 'bookType', 'books' => $copiesScope])
                        ->tap($activeBooksScope)
                        ->whereNotIn('id', $excludedIds)
                        ->latest('id')
                        ->limit(10 - $similar->count())
                        ->get();
                    $similar = $similar->concat($fillers);
                }
            }

            if ($similar->isNotEmpty()) {
                $defaultCover = asset('images/' . config('settings.book_covers.default', config('settings.coverFile_default', 'book-cover.webp')));

                return $similar->map(function ($item) use ($defaultCover) {
                    $data = $item->bookData;
                    $copies = $item->books ?: collect();
                    $availableCopies = $copies->filter(fn($c) => strtolower($c->status ?? '') === 'available')->count();
                    $firstCopy = $copies->first();
                    $accNo = $firstCopy?->accession_number;
                    $primaryCat = $data?->categories?->first()?->name
                        ?: ($item->classification ?: ($item->bookType ? $item->bookType->name : 'General'));

                    return [
                        'id' => $item->id,
                        'accession_no' => $accNo,
                        'title' => $data ? $data->book_title : 'Untitled',
                        'author' => $data && $data->authors->isNotEmpty()
                            ? $data->authors->map(fn($a) => trim($a->first_name . ' ' . $a->last_name))->filter()->implode(', ')
                            : 'Unknown Author',
                        'category' => $primaryCat,
                        'categories' => $data?->categories?->pluck('name')->toArray() ?? [],
                        'cover_url' => $item->cover_url ?: null,
                        'has_custom_cover' => !empty($item->cover_image),
                        'default_cover' => $defaultCover,
                        'call_no' => $item->call_number ?: 'N/A',
                        'year' => $item->publication_year ?: ($data?->copyright_year ?: 'N/A'),
                        'type' => $item->bookType ? $item->bookType->name : 'Book',
                        'status' => $availableCopies > 0 ? 'available' : 'unavailable',
                        'status_label' => $availableCopies > 0 ? 'Available' : 'Unavailable',
                    ];
                })->toArray();
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return [];
    }

    public function render()
    {
        $title = $this->book['title'] ?? 'Book Details';

        return view('livewire.pages.main.book')->layout('components.layouts.home', [
            'title' => $title,
        ]);
    }
}
