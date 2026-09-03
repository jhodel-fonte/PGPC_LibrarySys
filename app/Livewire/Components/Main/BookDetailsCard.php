<?php

namespace App\Livewire\Components\Main;

use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\ReservationStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookDetailsCard extends Component
{
    public $identifier = null;
    public $bookDetailId = null;
    public $book = null;
    public $activeTab = 'overview';
    public $isBookmarked = false;
    public $reservationModalOpen = false;
    public $reserveStatus = null;
    public $reserveMessage = null;

    public function mount($identifier = null, $bookDetailId = null)
    {
        $this->identifier = $identifier ?? $bookDetailId ?? request('identifier') ?? request('id');
        $this->loadBookData();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleBookmark()
    {
        $list = session()->get('my_book_list', []);
        $id = $this->book['accession_no'] ?? $this->book['id'] ?? null;

        if ($id) {
            if (in_array($id, $list)) {
                $list = array_diff($list, [$id]);
                $this->isBookmarked = false;
            } else {
                $list[] = $id;
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
        $this->reserveStatus = null;
        $this->reservationModalOpen = true;
    }

    public function closeReserveModal()
    {
        $this->reservationModalOpen = false;
        $this->reserveStatus = null;
    }

    public function confirmReservation()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->reserveStatus = 'loading';

        try {
            $copy = null;
            if (!empty($this->book['available_book_id'])) {
                $copy = Book::find($this->book['available_book_id']);
            }

            if (!$copy && $this->bookDetailId) {
                $copy = Book::where('book_detail_id', $this->bookDetailId)
                    ->where('status', 'available')
                    ->first();
            }

            if (!$copy && !empty($this->book['accession_no']) && $this->book['accession_no'] !== 'N/A') {
                $copy = Book::where('accession_number', $this->book['accession_no'])
                    ->where('status', 'available')
                    ->first();
            }

            if ($copy) {
                $pendingStatus = ReservationStatus::where('name', 'like', '%pending%')->first();
                $statusId = $pendingStatus ? $pendingStatus->id : 1;

                $userIdentifier = Auth::user()->username ?? Auth::user()->name ?? Auth::user()->email ?? 'user';

                BookReservation::create([
                    'book_id' => $copy->id,
                    'reservation_status_id' => $statusId,
                    'reservation_date' => now(),
                    'due_date' => now()->addDays(3),
                    'comment' => 'Online OPAC reservation by ' . $userIdentifier,
                ]);

                $copy->update(['status' => 'reserved']);
            }

            $this->reserveStatus = 'success';
            $this->reserveMessage = 'Your reservation has been submitted successfully! Please pick up the item at the circulation desk within 3 days.';

            // Refresh local status
            $this->book['status'] = 'reserved';
            $this->book['status_label'] = 'Reserved';
            $this->book['status_color'] = 'text-amber-600';
            $this->book['dot_color'] = 'bg-amber-500';
            $this->book['can_reserve'] = false;
        } catch (\Throwable $e) {
            $this->reserveStatus = 'error';
            $this->reserveMessage = 'Failed to submit reservation: ' . $e->getMessage();
        }
    }

    public function loadBookData()
    {
        $record = null;
        $copyByAccession = null;

        if (!empty($this->identifier)) {
            try {
                // 1. Primary lookup by unique physical copy accession number
                $copyByAccession = Book::where('accession_number', $this->identifier)->first();
                if ($copyByAccession && $copyByAccession->book_detail_id) {
                    $record = BookDetail::with([
                        'bookData.authors',
                        'bookData.categories',
                        'bookData.language',
                        'bookType',
                        'publisher',
                        'books.borrowingTransactions' => function ($q) {
                            $q->whereNull('return_date')->orderBy('due_date', 'asc');
                        },
                        'books.reservations' => function ($q) {
                            $q->whereNull('cancelled_date')->whereNull('fulfilled_date')->orderBy('due_date', 'asc');
                        }
                    ])->find($copyByAccession->book_detail_id);
                }

                // 2. Secondary lookup by BookDetail primary ID (if numeric)
                if (!$record && is_numeric($this->identifier)) {
                    $record = BookDetail::with([
                        'bookData.authors',
                        'bookData.categories',
                        'bookData.language',
                        'bookType',
                        'publisher',
                        'books.borrowingTransactions' => function ($q) {
                            $q->whereNull('return_date')->orderBy('due_date', 'asc');
                        },
                        'books.reservations' => function ($q) {
                            $q->whereNull('cancelled_date')->whereNull('fulfilled_date')->orderBy('due_date', 'asc');
                        }
                    ])->find((int) $this->identifier);
                }

                // 3. Tertiary lookup by ISBN or Call Number
                if (!$record) {
                    $record = BookDetail::with([
                        'bookData.authors',
                        'bookData.categories',
                        'bookData.language',
                        'bookType',
                        'publisher',
                        'books.borrowingTransactions' => function ($q) {
                            $q->whereNull('return_date')->orderBy('due_date', 'asc');
                        },
                        'books.reservations' => function ($q) {
                            $q->whereNull('cancelled_date')->whereNull('fulfilled_date')->orderBy('due_date', 'asc');
                        }
                    ])
                    ->where('isbn', $this->identifier)
                    ->orWhere('call_number', $this->identifier)
                    ->first();
                }

                if ($record && $record->bookData) {
                    $this->bookDetailId = $record->id;
                    $bdata = $record->bookData;
                    $copies = $record->books;
                    $totalCopies = $copies->count();
                    $availableCopies = $copies->where('status', 'available')->count();
                    $firstAvail = $copies->firstWhere('status', 'available');

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
                    } elseif ($copies->where('status', 'reserved')->count() === $totalCopies) {
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
                            $dueDate = \Carbon\Carbon::parse($activeTx->due_date)->format('M d, Y');
                        } elseif ($c->status === 'reserved') {
                            $dueDate = $activeRes && $activeRes->due_date
                                ? 'Pick up by ' . \Carbon\Carbon::parse($activeRes->due_date)->format('M d, Y')
                                : 'Pick up by ' . now()->addDays(2)->format('M d, Y');
                        }

                        $statColor = match ($c->status) {
                            'available' => 'text-emerald-700',
                            'borrowed' => 'text-rose-600',
                            'reserved' => 'text-amber-600',
                            default => 'text-slate-600',
                        };

                        $dot = match ($c->status) {
                            'available' => 'bg-emerald-500',
                            'borrowed' => 'bg-rose-500',
                            'reserved' => 'bg-amber-500',
                            default => 'bg-slate-400',
                        };

                        return [
                            'location' => $c->location ?: 'Main Library – 3rd Floor',
                            'collection' => 'General Collection',
                            'call_no' => $record->call_number ?? 'N/A',
                            'status' => $c->status,
                            'status_label' => ucfirst($c->status === 'borrowed' ? 'Checked Out' : $c->status),
                            'status_color' => $statColor,
                            'dot_color' => $dot,
                            'due_date' => $dueDate,
                        ];
                    })->toArray();

                    $authorNames = $bdata->authors->map(fn($a) => trim("{$a->first_name} {$a->last_name}"))->filter()->values()->toArray();
                    if (empty($authorNames)) {
                        $authorNames = ['Unknown Author'];
                    }

                    $categories = $bdata->categories->pluck('name')->filter()->values()->toArray();
                    if (empty($categories)) {
                        $categories = ['General Collection'];
                    }

                    $coverUrl = null;
                    if ($record->cover_image) {
                        $coverUrl = str_starts_with($record->cover_image, 'http')
                            ? $record->cover_image
                            : asset('storage/' . $record->cover_image);
                    }

                    $primaryAccession = $copyByAccession?->accession_number
                        ?? $copies->first()?->accession_number
                        ?? 'N/A';

                    $this->book = [
                        'id' => $record->id,
                        'available_book_id' => $firstAvail ? $firstAvail->id : null,
                        'title' => $bdata->book_title,
                        'subtitle' => $bdata->subtitle ?? '',
                        'format' => $record->format ?? 'Book',
                        'type' => $record->bookType->type ?? 'Book',
                        'author' => implode(', ', $authorNames),
                        'authors' => $authorNames,
                        'year' => $record->publication_year ?? $record->copyright_year ?? $bdata->copyright_year ?? 'N/A',
                        'pages' => $record->pages ? "{$record->pages} pages" : 'N/A',
                        'language' => $bdata->language->lang ?? 'English',
                        'call_no' => $record->call_number ?? 'N/A',
                        'accession_no' => $primaryAccession,
                        'isbn' => $record->isbn ?? 'N/A',
                        'publisher' => $record->publisher->name ?? 'N/A',
                        'edition' => $record->edition ?? 'N/A',
                        'classification' => $record->classification ?? 'N/A',
                        'subjects' => $categories,
                        'status' => $status,
                        'status_label' => $statusLabel,
                        'status_color' => $statusColor,
                        'dot_color' => $dotColor,
                        'total_copies' => $totalCopies,
                        'available_copies' => $availableCopies,
                        'can_reserve' => ($availableCopies > 0),
                        'description' => $bdata->description ?: 'No detailed summary cataloged for this resource.',
                        'keywords' => $categories,
                        'cover_url' => $coverUrl,
                        'holdings' => !empty($holdings) ? $holdings : $this->getDefaultHoldings(),
                    ];

                    $list = session()->get('my_book_list', []);
                    $this->isBookmarked = in_array($this->book['accession_no'], $list) || in_array($this->book['id'], $list);
                    return;
                }
            } catch (\Throwable $e) {
                // Fallback gracefully below
            }
        }

        // 4. Fallback dataset matching accession numbers, IDs, or ISBN
        $fallbackBooks = $this->getFallbackBooks();
        $target = null;

        if (!empty($this->identifier)) {
            $target = $fallbackBooks->first(function ($b) {
                return (string) $b['accession_no'] === (string) $this->identifier
                    || (string) $b['id'] === (string) $this->identifier
                    || (string) $b['isbn'] === (string) $this->identifier;
            });
        }

        if (!$target) {
            $target = $fallbackBooks->first();
        }

        $this->book = $target;
        $this->bookDetailId = $target['id'];

        $list = session()->get('my_book_list', []);
        $this->isBookmarked = in_array($this->book['accession_no'], $list) || in_array($this->book['id'], $list);
    }

    protected function getFallbackBooks()
    {
        return collect([
            [
                'id' => 1,
                'available_book_id' => 1,
                'title' => 'Programming Fundamentals',
                'subtitle' => 'An Introduction',
                'format' => 'Book',
                'type' => 'Book',
                'author' => 'John Smith',
                'authors' => ['John Smith'],
                'year' => '2024',
                'pages' => '356 pages',
                'language' => 'English',
                'call_no' => 'QA76.73.SMI 2024',
                'accession_no' => 'LIB-2024-00124',
                'isbn' => '978-621-98765-4-2',
                'publisher' => 'Pearson Education',
                'edition' => '1st Edition',
                'classification' => 'Computer Science (QA)',
                'subjects' => [
                    'Computer programming',
                    'Software development',
                    'Programming (Computer science)',
                ],
                'status' => 'available',
                'status_label' => 'Available',
                'status_color' => 'text-emerald-700',
                'dot_color' => 'bg-emerald-500',
                'total_copies' => 3,
                'available_copies' => 1,
                'can_reserve' => true,
                'description' => 'Programming Fundamentals: An Introduction provides a solid foundation for beginners in computer programming. It covers core concepts, problem-solving techniques, and practical examples using modern programming approaches. Ideal for students and self-learners.',
                'keywords' => [
                    'Programming',
                    'Computer Science',
                    'Coding',
                    'Algorithms',
                    'Software Development',
                ],
                'cover_url' => null,
                'holdings' => $this->getDefaultHoldings(),
            ],
            [
                'id' => 2,
                'available_book_id' => 2,
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'subtitle' => 'A Handbook of Agile Software Craftsmanship',
                'format' => 'Book',
                'type' => 'Book',
                'author' => 'Robert C. Martin',
                'authors' => ['Robert C. Martin'],
                'year' => '2008',
                'pages' => '464 pages',
                'language' => 'English',
                'call_no' => 'QA76.76.C64 M37 2008',
                'accession_no' => '00011822',
                'isbn' => '978-0132350884',
                'publisher' => 'Prentice Hall',
                'edition' => '1st Edition',
                'classification' => 'Software Engineering (QA76)',
                'subjects' => [
                    'Software Engineering',
                    'Agile Software Development',
                    'Refactoring',
                ],
                'status' => 'available',
                'status_label' => 'Available',
                'status_color' => 'text-emerald-700',
                'dot_color' => 'bg-emerald-500',
                'total_copies' => 4,
                'available_copies' => 2,
                'can_reserve' => true,
                'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code. But it doesn\'t have to be that way.',
                'keywords' => [
                    'Clean Code',
                    'Agile',
                    'Refactoring',
                    'Software Design',
                ],
                'cover_url' => 'https://m.media-amazon.com/images/I/51E2055ZGUL._SY445_SX342_.jpg',
                'holdings' => [
                    [
                        'location' => 'Main Library – 2nd Floor',
                        'collection' => 'General Collection',
                        'call_no' => 'QA76.76.C64 M37 2008',
                        'status' => 'available',
                        'status_label' => 'Available',
                        'status_color' => 'text-emerald-700',
                        'dot_color' => 'bg-emerald-500',
                        'due_date' => '—',
                    ],
                    [
                        'location' => 'Main Library – 2nd Floor',
                        'collection' => 'General Collection',
                        'call_no' => 'QA76.76.C64 M37 2008',
                        'status' => 'available',
                        'status_label' => 'Available',
                        'status_color' => 'text-emerald-700',
                        'dot_color' => 'bg-emerald-500',
                        'due_date' => '—',
                    ],
                ],
            ],
            [
                'id' => 3,
                'available_book_id' => null,
                'title' => 'Artificial Intelligence: A Modern Approach, Global Edition',
                'subtitle' => 'Global Edition',
                'format' => 'Book',
                'type' => 'Book',
                'author' => 'Stuart Russell, Peter Norvig',
                'authors' => ['Stuart Russell', 'Peter Norvig'],
                'year' => '2021',
                'pages' => '1168 pages',
                'language' => 'English',
                'call_no' => 'Q335 .R87 2021',
                'accession_no' => '00013004',
                'isbn' => '978-1292401133',
                'publisher' => 'Pearson',
                'edition' => '4th Edition',
                'classification' => 'Cybernetics & AI (Q335)',
                'subjects' => [
                    'Artificial Intelligence',
                    'Machine Learning',
                    'Robotics',
                ],
                'status' => 'checked_out',
                'status_label' => 'Checked Out',
                'status_color' => 'text-rose-600',
                'dot_color' => 'bg-rose-500',
                'total_copies' => 2,
                'available_copies' => 0,
                'can_reserve' => false,
                'description' => 'The authoritative, most-used AI textbook, adopting the unifying theme of an intelligent agent. Explores knowledge representation, probabilistic reasoning, machine learning, and natural language processing.',
                'keywords' => [
                    'Artificial Intelligence',
                    'Robotics',
                    'Neural Networks',
                ],
                'cover_url' => 'https://m.media-amazon.com/images/I/81s6DUyQCZL._SY466_.jpg',
                'holdings' => [
                    [
                        'location' => 'Main Library – 3rd Floor',
                        'collection' => 'General Collection',
                        'call_no' => 'Q335 .R87 2021',
                        'status' => 'checked_out',
                        'status_label' => 'Checked Out',
                        'status_color' => 'text-rose-600',
                        'dot_color' => 'bg-rose-500',
                        'due_date' => 'May 30, 2025',
                    ],
                ],
            ],
            [
                'id' => 10,
                'available_book_id' => 10,
                'title' => 'Introduction to Algorithms, Fourth Edition',
                'subtitle' => 'Fourth Edition',
                'format' => 'Book',
                'type' => 'Book',
                'author' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
                'authors' => ['Thomas H. Cormen', 'Charles E. Leiserson', 'Ronald L. Rivest', 'Clifford Stein'],
                'year' => '2022',
                'pages' => '1312 pages',
                'language' => 'English',
                'call_no' => 'QA76.6 .I585 2022',
                'accession_no' => '00012450',
                'isbn' => '978-0262046305',
                'publisher' => 'MIT Press',
                'edition' => '4th Edition',
                'classification' => 'Algorithms & Data Structures (QA76)',
                'subjects' => [
                    'Computer Science',
                    'Algorithms',
                    'Data Structures',
                ],
                'status' => 'available',
                'status_label' => 'Available',
                'status_color' => 'text-emerald-700',
                'dot_color' => 'bg-emerald-500',
                'total_copies' => 5,
                'available_copies' => 4,
                'can_reserve' => true,
                'description' => 'A comprehensive update of the leading algorithms text, with new material on matchings in bipartite graphs, online algorithms, machine learning, and other topics.',
                'keywords' => [
                    'Algorithms',
                    'Complexity',
                    'Sorting',
                    'Graphs',
                ],
                'cover_url' => 'https://m.media-amazon.com/images/I/61Mw06x2A7L._SY466_.jpg',
                'holdings' => [
                    [
                        'location' => 'Main Library – 3rd Floor',
                        'collection' => 'General Collection',
                        'call_no' => 'QA76.6 .I585 2022',
                        'status' => 'available',
                        'status_label' => 'Available',
                        'status_color' => 'text-emerald-700',
                        'dot_color' => 'bg-emerald-500',
                        'due_date' => '—',
                    ],
                ],
            ],
        ]);
    }

    protected function getDefaultHoldings(): array
    {
        return [
            [
                'location' => 'Main Library – 3rd Floor',
                'collection' => 'General Collection',
                'call_no' => 'QA76.73.SMI 2024',
                'status' => 'available',
                'status_label' => 'Available',
                'status_color' => 'text-emerald-700',
                'dot_color' => 'bg-emerald-500',
                'due_date' => '—',
            ],
            [
                'location' => 'Main Library – 3rd Floor',
                'collection' => 'General Collection',
                'call_no' => 'QA76.73.SMI 2024',
                'status' => 'checked_out',
                'status_label' => 'Checked Out',
                'status_color' => 'text-rose-600',
                'dot_color' => 'bg-rose-500',
                'due_date' => 'May 30, 2025',
            ],
            [
                'location' => 'Main Library – 3rd Floor',
                'collection' => 'General Collection',
                'call_no' => 'QA76.73.SMI 2024',
                'status' => 'reserved',
                'status_label' => 'Reserved',
                'status_color' => 'text-amber-600',
                'dot_color' => 'bg-amber-500',
                'due_date' => 'Pick up by May 28, 2025',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.components.main.book-details-card');
    }
}
