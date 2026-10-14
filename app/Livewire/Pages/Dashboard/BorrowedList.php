<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\BorrowingTransaction;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'Active Borrowers'])]
class BorrowedList extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'tab')]
    public string $activeTab = 'active'; // 'active', 'overdue', 'returned', 'all'

    // Applied Filters
    public string $filterProgram = '';
    public string $filterYearLevel = '';

    // Staged Dropdown Filters
    public string $tempFilterProgram = '';
    public string $tempFilterYearLevel = '';

    public array $sort = [
        'column' => 'borrowed_date',
        'direction' => 'desc',
    ];

    public int $perPage = 10;

    public function getHeadersProperty(): array
    {
        return [
            ['index' => 'student', 'label' => 'Borrower / Student', 'sortable' => true],
            ['index' => 'book', 'label' => 'Book', 'sortable' => true],
            ['index' => 'borrowed_date', 'label' => 'Borrowed Date', 'sortable' => true],
            ['index' => 'due_date', 'label' => 'Due Date', 'sortable' => true],
            ['index' => 'status', 'label' => 'Status', 'sortable' => true],
            ['index' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ];
    }

    public function sortBy(string $column): void
    {
        if ($this->sort['column'] === $column) {
            $this->sort['direction'] = $this->sort['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort['column'] = $column;
            $this->sort['direction'] = 'asc';
        }
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->filterProgram = $this->tempFilterProgram;
        $this->filterYearLevel = $this->tempFilterYearLevel;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterProgram = '';
        $this->filterYearLevel = '';
        $this->tempFilterProgram = '';
        $this->tempFilterYearLevel = '';
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if (! empty($this->filterProgram)) $count++;
        if (! empty($this->filterYearLevel)) $count++;
        return $count;
    }

    public function render()
    {
        $query = BorrowingTransaction::query()
            ->with([
                'student.account',
                'book.bookDetail.bookData',
                'issuedBy',
                'receivedBy'
            ]);

        // Tab Filtering
        if ($this->activeTab === 'active') {
            $query->whereNull('borrowing_transactions.return_date');
        } elseif ($this->activeTab === 'overdue') {
            $query->whereNull('borrowing_transactions.return_date')
                ->where('borrowing_transactions.due_date', '<', Carbon::now());
        } elseif ($this->activeTab === 'returned') {
            $query->whereNotNull('borrowing_transactions.return_date');
        }

        // Dropdown Filters
        if (! empty($this->filterProgram)) {
            $query->whereHas('student', function ($sQ) {
                $sQ->where('program', $this->filterProgram);
            });
        }

        if (! empty($this->filterYearLevel)) {
            $query->whereHas('student', function ($sQ) {
                $sQ->where('year_level', $this->filterYearLevel);
            });
        }

        // Search Term (Book Title, Accession, Student Name, School ID)
        if (! empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('book', function ($bQ) use ($term) {
                    $bQ->where('accession_number', 'like', $term)
                        ->orWhereHas('bookDetail.bookData', function ($bdQ) use ($term) {
                            $bdQ->where('book_title', 'like', $term);
                        });
                })->orWhereHas('student', function ($sQ) use ($term) {
                    $sQ->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('school_id_number', 'like', $term);
                });
            });
        }

        // Sorting
        match ($this->sort['column']) {
            'student' => $query->join('students', 'borrowing_transactions.school_id', '=', 'students.id')
                ->orderBy('students.first_name', $this->sort['direction'])
                ->select('borrowing_transactions.*'),
            'book' => $query->join('books', 'borrowing_transactions.book_id', '=', 'books.id')
                ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                ->orderBy('book_datas.book_title', $this->sort['direction'])
                ->select('borrowing_transactions.*'),
            'borrowed_date' => $query->orderBy('borrowing_transactions.issued_date', $this->sort['direction']),
            'due_date' => $query->orderBy('borrowing_transactions.due_date', $this->sort['direction']),
            'status' => $query->orderBy('borrowing_transactions.return_date', $this->sort['direction']),
            default => $query->orderBy('borrowing_transactions.issued_date', $this->sort['direction']),
        };

        $borrowings = $query->paginate($this->perPage);

        // Counts for tabs
        $activeCount = BorrowingTransaction::whereNull('return_date')->count();
        $overdueCount = BorrowingTransaction::whereNull('return_date')->where('due_date', '<', Carbon::now())->count();
        $returnedCount = BorrowingTransaction::whereNotNull('return_date')->count();
        $totalHistoryCount = BorrowingTransaction::count();

        // Available programs for filter dropdown
        $programs = Student::distinct()->whereNotNull('program')->orderBy('program')->pluck('program')->toArray();

        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';

        return view('livewire.pages.dashboard.borrowed-list', [
            'borrowings' => $borrowings,
            'activeCount' => $activeCount,
            'overdueCount' => $overdueCount,
            'returnedCount' => $returnedCount,
            'totalHistoryCount' => $totalHistoryCount,
            'programs' => $programs,
            'routePrefix' => $routePrefix,
        ]);
    }
}
