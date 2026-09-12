<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookDetail;
use App\Models\BookData;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management'])]
class BookManager extends Component
{
    use WithPagination;

    public $search = '';
    public $activeTab = 'All Books'; // All Books, In Stock, Borrowed, Damaged/Lost

    // Sorting
    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    // Manage Copies Modal
    public $viewingCopiesBookDetailId = null;
    public $selectedBookDetail = null;
    public $showCopiesModal = false;

    // Edit single copy form properties
    public $editingBookId = null;
    public $editAccessionNumber = '';
    public $editCode = '';
    public $editLocation = '';
    public $editConditionId = '';
    public $showEditModal = false;

    // Toast/Alert message properties
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'editLocation' => 'nullable|string|max:100',
        'editConditionId' => 'required|exists:book_conditions,id',
    ];

    public function getHeadersProperty()
    {
        return [
            ['index' => 'book_title', 'label' => 'Book details', 'sortable' => true],
            ['index' => 'isbn', 'label' => 'ISBN / Call No.', 'sortable' => true],
            ['index' => 'categories', 'label' => 'Category', 'sortable' => false],
            ['index' => 'copies', 'label' => 'Total Copies', 'sortable' => true],
            ['index' => 'availability', 'label' => 'Status / In Stock', 'sortable' => false],
            ['index' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ];
    }

    public function sortBy($column)
    {
        if ($this->sort['column'] === $column) {
            $this->sort['direction'] = $this->sort['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort['column'] = $column;
            $this->sort['direction'] = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->clearMessages();
    }

    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    // View All Copies for a Specific Book Detail
    public function openCopiesModal($bookDetailId)
    {
        $this->clearMessages();
        $this->viewingCopiesBookDetailId = $bookDetailId;
        $this->loadSelectedBookDetail();
        $this->showCopiesModal = true;
    }

    public function closeCopiesModal()
    {
        $this->showCopiesModal = false;
        $this->viewingCopiesBookDetailId = null;
        $this->selectedBookDetail = null;
    }

    protected function loadSelectedBookDetail()
    {
        if ($this->viewingCopiesBookDetailId) {
            $this->selectedBookDetail = BookDetail::with(['bookData.authors', 'books.condition', 'publisher'])
                ->find($this->viewingCopiesBookDetailId);
        }
    }

    // Edit Single Copy Modal Actions
    public function editCopy($bookId)
    {
        $this->clearMessages();
        $book = Book::find($bookId);

        if ($book) {
            $this->editingBookId = $book->id;
            $this->editAccessionNumber = $book->accession_number;
            $this->editCode = $book->code ?: 'N/A';
            $this->editLocation = $book->location ?? '';
            $this->editConditionId = $book->book_condition_id;
            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingBookId = null;
        $this->editAccessionNumber = '';
        $this->editCode = '';
        $this->editLocation = '';
        $this->editConditionId = '';
    }

    public function saveCopy()
    {
        $this->validate();

        $book = Book::find($this->editingBookId);
        if ($book) {
            $book->update([
                'location' => trim($this->editLocation) ?: null,
                'book_condition_id' => $this->editConditionId,
            ]);

            $this->dispatch('toast', message: 'Book copy "' . $book->accession_number . '" updated successfully.', type: 'success');
            $this->showEditModal = false;
            $this->editingBookId = null;
            $this->loadSelectedBookDetail();
        } else {
            $this->dispatch('toast', message: 'Failed to find book copy details.', type: 'error');
        }
    }

    public function deleteCopy($bookId)
    {
        $book = Book::find($bookId);

        if ($book) {
            if ($book->status === 'borrowed') {
                $this->dispatch('toast', message: 'Cannot delete a book copy that is currently checked out / borrowed.', type: 'error');
                return;
            }

            $accNum = $book->accession_number;
            $book->delete();
            $this->dispatch('toast', message: 'Book copy "' . $accNum . '" has been soft-deleted.', type: 'info');
            $this->loadSelectedBookDetail();
        }
    }

    public function render()
    {
        // 1. Build Query on BookDetail with eager loading & counts
        $query = BookDetail::with(['bookData.authors', 'bookData.categories', 'publisher'])
            ->withCount([
                'books as total_copies',
                'books as available_copies' => fn($q) => $q->where('status', 'available'),
                'books as borrowed_copies' => fn($q) => $q->where('status', 'borrowed'),
                'books as damaged_lost_copies' => fn($q) => $q->whereIn('book_condition_id', [4, 5]),
            ]);

        // 2. Filter by Search Query
        if (!empty($this->search)) {
            $searchVal = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchVal) {
                $q->where('isbn', 'like', $searchVal)
                  ->orWhere('call_number', 'like', $searchVal)
                  ->orWhere('classification', 'like', $searchVal)
                  ->orWhereHas('bookData', function ($bq) use ($searchVal) {
                      $bq->where('book_title', 'like', $searchVal)
                        ->orWhere('subtitle', 'like', $searchVal)
                        ->orWhereHas('authors', function ($aq) use ($searchVal) {
                            $aq->where('first_name', 'like', $searchVal)
                              ->orWhere('last_name', 'like', $searchVal);
                        })
                        ->orWhereHas('categories', function ($cq) use ($searchVal) {
                            $cq->where('name', 'like', $searchVal);
                        });
                  })
                  ->orWhereHas('books', function ($copyQ) use ($searchVal) {
                      $copyQ->where('accession_number', 'like', $searchVal)
                            ->orWhere('code', 'like', $searchVal)
                            ->orWhere('location', 'like', $searchVal);
                  });
            });
        }

        // 3. Filter by Active Tab
        if ($this->activeTab === 'In Stock') {
            $query->whereHas('books', fn($q) => $q->where('status', 'available'));
        } elseif ($this->activeTab === 'Borrowed') {
            $query->whereHas('books', fn($q) => $q->where('status', 'borrowed'));
        } elseif ($this->activeTab === 'Damaged/Lost') {
            $query->whereHas('books', fn($q) => $q->whereIn('book_condition_id', [4, 5]));
        }

        // 4. Apply Sorting
        $sortColumn = $this->sort['column'];
        $sortDirection = $this->sort['direction'];

        if ($sortColumn === 'book_title') {
            $query->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                  ->select('book_details.*')
                  ->orderBy('book_datas.book_title', $sortDirection);
        } elseif ($sortColumn === 'copies') {
            $query->orderBy('total_copies', $sortDirection);
        } elseif (in_array($sortColumn, ['isbn', 'call_number', 'created_at', 'id'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('id', $sortDirection);
        }

        // 5. Fetch Paginated Records
        $bookDetails = $query->paginate(10);

        // 6. Fetch Stat Counts
        $stats = [
            'total_titles' => BookData::count(),
            'total_copies' => Book::count(),
            'available' => Book::where('status', 'available')->count(),
            'borrowed' => Book::where('status', 'borrowed')->count(),
            'damaged_lost' => Book::whereIn('book_condition_id', [4, 5])->count(),
        ];

        // Fetch Conditions for dropdown
        $conditions = BookCondition::all();

        return view('livewire.pages.dashboard.book-manager', [
            'bookDetails' => $bookDetails,
            'stats' => $stats,
            'conditions' => $conditions,
        ]);
    }
}
