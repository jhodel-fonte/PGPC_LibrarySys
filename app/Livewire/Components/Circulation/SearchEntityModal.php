<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use App\Models\Student;
use App\Models\Account;
use App\Models\Book;
use App\Models\OpacCatalogView;
use App\Models\BookReservation;
use Livewire\Attributes\On;

class SearchEntityModal extends Component
{
    public $isOpen = false;
    public $searchQuery = '';
    public $activeTab = 'all'; // all, members, books
    public $results = [];
    public $memberCount = 0;
    public $bookCount = 0;
    public $reservationCount = 0;
    public $perPage = 5;
    public $service = 'check-in'; // check-in or check-out

    public function mount($service = 'check-in')
    {
        $this->service = $service;
    }

    #[On('open-search-modal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->resetSearch();
        $this->performSearch();
        $this->dispatch('search-modal-opened');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetSearch();
        $this->dispatch('search-modal-closed');
    }

    public function resetSearch()
    {
        $this->searchQuery = '';
        $this->activeTab = 'all';
        $this->results = [];
        $this->memberCount = 0;
        $this->bookCount = 0;
        $this->reservationCount = 0;
        $this->perPage = 5;
        $this->dispatch('search-completed');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->perPage = 5; // Reset pagination when switching tabs
        $this->performSearch();
    }

    public function updatedSearchQuery()
    {
        $this->perPage = 5; // Reset pagination when query changes
        $this->performSearch();
    }

    public function loadMore()
    {
        $this->perPage += 5; // Load 5 more records
        $this->performSearch();
    }

    public function loadInitialData()
    {
        $isCheckIn = ($this->service === 'check-in');

        // Count Members
        if ($isCheckIn) {
            $this->memberCount = Student::whereHas('borrowingTransactions', function($q) {
                $q->whereNull('return_date');
            })->count();
            if ($this->memberCount === 0) {
                $this->memberCount = Student::count();
            }
        } else {
            $this->memberCount = Student::count();
        }

        // Count Books
        if ($isCheckIn) {
            $this->bookCount = Book::whereHas('borrowingTransactions', function($q) {
                $q->whereNull('return_date');
            })->count();
            if ($this->bookCount === 0) {
                $this->bookCount = Book::count();
            }
        } else {
            $this->bookCount = Book::count();
        }

        if ($this->service === 'check-out') {
            $this->reservationCount = BookReservation::where('reservation_status_id', 1)->count();
        }
        $results = [];

        // 1. Fetch Members ordered alphabetically by last_name, first_name
        if ($this->activeTab === 'all' || $this->activeTab === 'members') {
            if ($isCheckIn) {
                $students = Student::with(['libraryStatus', 'account'])
                    ->whereHas('borrowingTransactions', function($q) {
                        $q->whereNull('return_date');
                    })
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->limit($this->perPage)
                    ->get();

                if ($students->isEmpty()) {
                    $students = Student::with(['libraryStatus', 'account'])
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->limit($this->perPage)
                        ->get();
                }
            } else {
                $students = Student::with(['libraryStatus', 'account'])
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->limit($this->perPage)
                    ->get();
            }

            foreach ($students as $student) {
                $results[] = $this->formatStudentResult($student);
            }
        }

        // 2. Fetch pending reservations for checkout.
        if ($this->service === 'check-out' && ($this->activeTab === 'all' || $this->activeTab === 'reservations')) {
            $reservations = BookReservation::with([
                'student',
                'book.bookDetail.bookData.authors',
                'reservationStatus',
            ])
                ->where('reservation_status_id', 1)
                ->orderByDesc('reservation_date')
                ->limit($this->perPage)
                ->get();

            foreach ($reservations as $reservation) {
                $results[] = $this->formatReservationResult($reservation);
            }
        }
        // 2. Fetch Books ordered alphabetically by title
        if ($this->activeTab === 'all' || $this->activeTab === 'books') {
            if ($isCheckIn) {
                $books = Book::with(['bookDetail.bookData.authors'])
                    ->whereHas('bookDetail.bookData')
                    ->whereHas('borrowingTransactions', function($q) {
                        $q->whereNull('return_date');
                    })
                    ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                    ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                    ->orderBy('book_datas.book_title')
                    ->select('books.*')
                    ->limit($this->perPage)
                    ->get();

                if ($books->isEmpty()) {
                    $books = Book::with(['bookDetail.bookData.authors'])
                        ->whereHas('bookDetail.bookData')
                        ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                        ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                        ->orderBy('book_datas.book_title')
                        ->select('books.*')
                        ->limit($this->perPage)
                        ->get();
                }
            } else {
                $books = Book::with(['bookDetail.bookData.authors'])
                    ->whereHas('bookDetail.bookData')
                    ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                    ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                    ->orderBy('book_datas.book_title')
                    ->select('books.*')
                    ->limit($this->perPage)
                    ->get();
            }

            foreach ($books as $book) {
                $results[] = $this->formatBookResult($book);
            }
        }

        $this->results = $results;
        $this->dispatch('search-completed');
    }

    public function performSearch()
    {
        $rawQuery = trim($this->searchQuery);
        if (strlen($rawQuery) < 1) {
            $this->loadInitialData();
            return;
        }

        $searchTerm = $rawQuery;

        // 1. Search Members via Laravel Scout (Student model & Account model)
        $studentKeys = Student::search($searchTerm)->keys();
        $accountKeys = Account::search($searchTerm)->keys();

        $membersQuery = Student::where(function ($q) use ($studentKeys, $accountKeys, $searchTerm) {
            $hasCondition = false;

            if ($studentKeys->isNotEmpty()) {
                $q->whereIn('students.id', $studentKeys);
                $hasCondition = true;
            }

            if ($accountKeys->isNotEmpty()) {
                $hasCondition ? $q->orWhereIn('students.account_id', $accountKeys) : $q->whereIn('students.account_id', $accountKeys);
                $hasCondition = true;
            }

            // Fallback for multi-word full name search (e.g. "Juan Dela Cruz")
            if (str_contains($searchTerm, ' ')) {
                $clean = \App\Services\SanitizeInput::escapeLike($searchTerm);
                if ($hasCondition) {
                    $q->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$clean}%"])
                      ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) ILIKE ?", ["%{$clean}%"]);
                } else {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$clean}%"])
                      ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) ILIKE ?", ["%{$clean}%"]);
                    $hasCondition = true;
                }
            }

            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });

        // 2. Search Books via Laravel Scout (Book copy attributes & OpacCatalogView bibliographic attributes)
        $copyKeys = Book::search($searchTerm)->keys();
        $detailKeys = OpacCatalogView::search($searchTerm)->keys();

        $booksQuery = Book::where(function ($q) use ($copyKeys, $detailKeys) {
            if ($copyKeys->isNotEmpty() && $detailKeys->isNotEmpty()) {
                $q->whereIn('books.id', $copyKeys)
                  ->orWhereIn('books.book_detail_id', $detailKeys);
            } elseif ($copyKeys->isNotEmpty()) {
                $q->whereIn('books.id', $copyKeys);
            } elseif ($detailKeys->isNotEmpty()) {
                $q->whereIn('books.book_detail_id', $detailKeys);
            } else {
                $q->whereRaw('1 = 0');
            }
        });

        $reservationsQuery = null;
        if ($this->service === 'check-out') {
            $reservationTerm = preg_replace('/[^0-9]/', '', $searchTerm);
            $likeTerm = '%' . $searchTerm . '%';

            $reservationsQuery = BookReservation::query()
                ->where('reservation_status_id', 1)
                ->where(function ($q) use ($likeTerm, $reservationTerm) {
                    if ($reservationTerm !== '') {
                        $q->orWhere('id', 'like', '%' . $reservationTerm . '%');
                    }

                    $q->orWhereHas('student', function ($student) use ($likeTerm) {
                        $student->where('first_name', 'like', $likeTerm)
                            ->orWhere('last_name', 'like', $likeTerm)
                            ->orWhere('school_id_number', 'like', $likeTerm);
                    })->orWhereHas('book', function ($book) use ($likeTerm) {
                        $book->where('accession_number', 'like', $likeTerm)
                            ->orWhereHas('bookDetail.bookData', function ($bookData) use ($likeTerm) {
                                $bookData->where('book_title', 'like', $likeTerm);
                            });
                    });
                });
        }
        // Compute counts
        $this->memberCount = $membersQuery->count();
        $this->bookCount = $booksQuery->count();
        $this->reservationCount = $reservationsQuery ? $reservationsQuery->count() : 0;

        $results = [];

        // 1. Fetch Members if tab matches
        if ($this->activeTab === 'all' || $this->activeTab === 'members') {
            $students = $membersQuery->with(['libraryStatus', 'account'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit($this->perPage)
                ->get();

            foreach ($students as $student) {
                $results[] = $this->formatStudentResult($student);
            }
        }

        // 2. Fetch pending reservations if tab matches.
        if ($reservationsQuery && ($this->activeTab === 'all' || $this->activeTab === 'reservations')) {
            $reservations = $reservationsQuery->with([
                'student',
                'book.bookDetail.bookData.authors',
                'reservationStatus',
            ])->orderByDesc('reservation_date')->limit($this->perPage)->get();

            foreach ($reservations as $reservation) {
                $results[] = $this->formatReservationResult($reservation);
            }
        }
        // 2. Fetch Books if tab matches
        if ($this->activeTab === 'all' || $this->activeTab === 'books') {
            $books = $booksQuery->with(['bookDetail.bookData.authors'])
                ->limit($this->perPage)
                ->get();

            foreach ($books as $book) {
                $results[] = $this->formatBookResult($book);
            }
        }

        $this->results = $results;
        $this->dispatch('search-completed');
    }

    protected function formatStudentResult($student): array
    {
        return [
            'type' => 'member',
            'id' => $student->id,
            'code' => $student->school_id_number,
            'title' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'subtitle' => trim(($student->program ?? '') . ' - ' . ($student->year_level ?? '')),
            'email' => $student->account ? $student->account->email : '',
            'status' => $student->libraryStatus ? $student->libraryStatus->status : 'Active',
            'icon' => 'user'
        ];
    }

    protected function formatReservationResult(BookReservation $reservation): array
    {
        $bookData = $reservation->book?->bookDetail?->bookData;
        $studentName = trim(($reservation->student?->first_name ?? '') . ' ' . ($reservation->student?->last_name ?? '')) ?: 'Unknown student';

        return [
            'type' => 'reservation',
            'id' => $reservation->id,
            'code' => '#RES-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
            'title' => $studentName,
            'subtitle' => $bookData?->book_title ?? 'Unknown book',
            'student_id' => $reservation->student?->school_id_number ?? 'No student ID',
            'accession' => $reservation->book?->accession_number ?? 'No accession number',
            'status' => $reservation->reservationStatus?->status ?? 'Pending',
        ];
    }
    protected function formatBookResult($book): array
    {
        $detail = $book->bookDetail;
        $data = $detail ? $detail->bookData : null;
        $authorName = 'Unknown Author';
        if ($data && $data->authors->isNotEmpty()) {
            $authorName = $data->authors->map(function($a) {
                return trim($a->first_name . ' ' . $a->last_name);
            })->implode(', ');
        }

        $titleWords = explode(' ', $data ? $data->book_title : 'BOOK');
        $codeInitials = '';
        foreach ($titleWords as $word) {
            $codeInitials .= strtoupper(substr($word, 0, 1));
            if (strlen($codeInitials) >= 4) break;
        }

        return [
            'type' => 'book',
            'id' => $book->id,
            'code' => $book->accession_number ?: $book->code,
            'title' => $data ? $data->book_title : 'Unknown Book',
            'subtitle' => trim($authorName . ' · Accession No. ' . ($book->accession_number ?? 'N/A')),
            'barcode' => $book->code,
            'code_tag' => $codeInitials ?: 'BOOK',
            'cover_url' => $detail ? $detail->cover_url : null,
            'icon' => 'book-open'
        ];
    }

    public function selectEntity($type, $code)
    {
        $this->dispatch('set-search-value', code: $code);

        // Close the modal
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.components.circulation.search-entity-modal');
    }
}
