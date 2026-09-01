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
    public $activeTab = 'All Copies'; // All Copies, Available, Borrowed, Damaged/Lost

    // Sorting
    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    // Edit modal form properties
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
            ['index' => 'accession_number', 'label' => 'Accession No.', 'sortable' => true],
            ['index' => 'code', 'label' => 'Unique Code', 'sortable' => true],
            ['index' => 'location', 'label' => 'Location', 'sortable' => true],
            ['index' => 'condition', 'label' => 'Condition', 'sortable' => false],
            ['index' => 'status', 'label' => 'Status', 'sortable' => true],
            ['index' => 'actions', 'label' => 'Actions', 'sortable' => false],
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

    // Modal Actions
    public function editCopy($bookId)
    {
        $this->clearMessages();
        $book = Book::find($bookId);

        if ($book) {
            $this->editingBookId = $book->id;
            $this->editAccessionNumber = $book->accession_number;
            $this->editCode = $book->code ?: 'N/A';
            $this->editLocation = $book->location;
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
            // Update book details
            $book->update([
                'location' => trim($this->editLocation) ?: null,
                'book_condition_id' => $this->editConditionId,
            ]);

            $this->successMessage = 'Book copy "' . $book->accession_number . '" updated successfully.';
            $this->showEditModal = false;
            $this->editingBookId = null;
        } else {
            $this->errorMessage = 'Failed to find book copy details.';
        }
    }

    public function deleteCopy($bookId)
    {
        $this->clearMessages();
        $book = Book::find($bookId);

        if ($book) {
            if ($book->status === 'borrowed') {
                $this->errorMessage = 'Cannot delete a book copy that is currently checked out / borrowed.';
                return;
            }

            $accNum = $book->accession_number;
            $book->delete();
            $this->successMessage = 'Book copy "' . $accNum . '" has been soft-deleted.';
        }
    }

    public function render()
    {
        // 1. Build Query with eager loading
        $query = Book::with(['bookDetail.bookData.authors', 'condition']);

        // 2. Filter by Search Query
        if (!empty($this->search)) {
            $searchVal = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchVal) {
                $q->where('accession_number', 'like', $searchVal)
                  ->orWhere('code', 'like', $searchVal)
                  ->orWhere('location', 'like', $searchVal)
                  ->orWhereHas('bookDetail.bookData', function ($bq) use ($searchVal) {
                      $bq->where('book_title', 'like', $searchVal)
                        ->orWhereHas('authors', function ($aq) use ($searchVal) {
                            $aq->where('first_name', 'like', $searchVal)
                              ->orWhere('last_name', 'like', $searchVal);
                        });
                  })
                  ->orWhereHas('bookDetail', function ($bdq) use ($searchVal) {
                      $bdq->where('isbn', 'like', $searchVal);
                  });
            });
        }

        // 3. Filter by Active Tab
        if ($this->activeTab === 'Available') {
            $query->where('status', 'available');
        } elseif ($this->activeTab === 'Borrowed') {
            $query->where('status', 'borrowed');
        } elseif ($this->activeTab === 'Damaged/Lost') {
            $query->whereIn('book_condition_id', [4, 5]); // 4 = Damaged, 5 = Lost
        }

        // 4. Apply Sorting
        $sortColumn = $this->sort['column'];
        $sortDirection = $this->sort['direction'];

        if ($sortColumn === 'book_title') {
            $query->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                  ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                  ->select('books.*')
                  ->orderBy('book_datas.book_title', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // 5. Fetch Paginated Records
        $books = $query->paginate(10);

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
            'books' => $books,
            'stats' => $stats,
            'conditions' => $conditions,
        ]);
    }
}
