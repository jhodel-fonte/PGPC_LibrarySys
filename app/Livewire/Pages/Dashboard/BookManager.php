<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management'])]
class BookManager extends Component
{
    use WithPagination;

    // Search & Tabs
    public $search = '';
    public $inventoryView = 'Copies'; // Titles, Copies
    public $activeTab = 'All Copies'; // All Copies, Available, Borrowed, Damaged/Lost
    public $perPage = 10;

    // Sorting
    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    // Multi-Selection (Bulk Actions)
    public array $selectedCopies = [];
    public bool $selectAll = false;

    // Filter Options
    public $filterLocation = '';
    public $filterCondition = '';
    public $filterStatus = '';
    public $filterAuthor = '';
    public $filterYear = '';
    public bool $showFilterDropdown = false;

    // Edit single copy form properties
    public $editingBookId = null;
    public $editAccessionNumber = '';
    public $editCode = '';
    public $editLocation = '';
    public $editConditionId = '';
    public $editStatus = 'available';
    public $showEditModal = false;

    // Bulk Modals
    public $showBulkLocationModal = false;
    public $bulkLocation = '';
    public $showBulkConditionModal = false;
    public $bulkConditionId = '';

    // View Details & History Modal
    public $showDetailsModal = false;
    public $viewingBookId = null;
    public $showHistoryModal = false;
    public $historyBookId = null;

    protected $rules = [
        'editLocation' => 'nullable|string|max:100',
        'editConditionId' => 'required|exists:book_conditions,id',
        'editStatus' => 'required|string|in:available,borrowed,reserved,damaged,lost,maintenance',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->clearSelection();
    }

    public function setInventoryView($view)
    {
        if (!in_array($view, ['Titles', 'Copies'], true)) {
            return;
        }

        $this->inventoryView = $view;
        $this->sort = [
            'column' => $view === 'Titles' ? 'book_title' : 'id',
            'direction' => $view === 'Titles' ? 'asc' : 'desc',
        ];
        $this->resetPage();
        $this->clearSelection();
    }

    public function getHeadersProperty()
    {
        return [
            ['index' => 'details', 'label' => 'Book Details', 'sortable' => true],
            ['index' => 'accession', 'label' => 'Accession No.', 'sortable' => true],
            ['index' => 'code', 'label' => 'Unique Code', 'sortable' => true],
            ['index' => 'location', 'label' => 'Location', 'sortable' => true],
            ['index' => 'condition', 'label' => 'Condition', 'sortable' => true],
            ['index' => 'status', 'label' => 'Status', 'sortable' => true],
            ['index' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ];
    }

    public function sortBy($column)
    {
        $resolvedCol = match($column) {
            'details' => 'book_title',
            'accession' => 'accession_number',
            default => $column
        };

        if ($this->sort['column'] === $resolvedCol) {
            $this->sort['direction'] = $this->sort['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort['column'] = $resolvedCol;
            $this->sort['direction'] = 'asc';
        }
    }

    // Selection Handling
    public function updatedSelectAll($value)
    {
        if ($value) {
            $currentPageIds = $this->getCurrentPageBookIds();
            $this->selectedCopies = array_map('strval', $currentPageIds);
        } else {
            $this->selectedCopies = [];
        }
    }

    public function clearSelection()
    {
        $this->selectedCopies = [];
        $this->selectAll = false;
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if (!empty($this->filterLocation)) $count++;
        if (!empty($this->filterCondition)) $count++;
        if (!empty($this->filterStatus)) $count++;
        if (!empty($this->filterAuthor)) $count++;
        if (!empty($this->filterYear)) $count++;
        return $count;
    }

    public function clearFilters()
    {
        $this->filterLocation = '';
        $this->filterCondition = '';
        $this->filterStatus = '';
        $this->filterAuthor = '';
        $this->filterYear = '';
        $this->resetPage();
        $this->clearSelection();
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    // Single Edit Modal
    public function editCopy($bookId)
    {
        $book = Book::with('condition')->find($bookId);
        if ($book) {
            $this->editingBookId = $book->id;
            $this->editAccessionNumber = $book->accession_number;
            $this->editCode = $book->code ?: 'N/A';
            $this->editLocation = $book->location ?? '';
            $this->editConditionId = $book->book_condition_id;
            $this->editStatus = $book->status;
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
        $this->editStatus = 'available';
    }

    public function saveCopy()
    {
        $this->validate();

        $book = Book::find($this->editingBookId);
        if ($book) {
            $book->update([
                'location' => trim($this->editLocation) ?: null,
                'book_condition_id' => $this->editConditionId,
                'status' => $this->editStatus,
            ]);

            $this->dispatch('toast', message: 'Book copy "' . $book->accession_number . '" updated successfully.', type: 'success');
            $this->closeEditModal();
        }
    }

    public function markCondition($bookId, $conditionId)
    {
        $book = Book::find($bookId);
        if ($book) {
            $condition = BookCondition::find($conditionId);
            $condName = $condition ? $condition->status : 'Condition';

            $newStatus = $book->status;
            if ($conditionId == 4) {
                $newStatus = 'damaged';
            } elseif ($conditionId == 5) {
                $newStatus = 'lost';
            }

            $book->update([
                'book_condition_id' => $conditionId,
                'status' => $newStatus,
            ]);

            $this->dispatch('toast', message: 'Copy "' . $book->accession_number . '" marked as ' . $condName . '.', type: 'info');
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
            $this->dispatch('toast', message: 'Book copy "' . $accNum . '" has been deleted.', type: 'info');
            $this->clearSelection();
        }
    }

    // View Details Modal
    public function viewDetails($bookId)
    {
        $book = Book::find($bookId);
        $this->dispatch('open-book-details', id: $book?->book_detail_id ?? $bookId, bookId: $bookId);
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->viewingBookId = null;
    }

    // View History Modal
    public function viewHistory($bookId)
    {
        $this->historyBookId = $bookId;
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->historyBookId = null;
    }

    // Bulk Operations
    public function openBulkLocationModal()
    {
        if (empty($this->selectedCopies)) return;
        $this->bulkLocation = '';
        $this->showBulkLocationModal = true;
    }

    public function saveBulkLocation()
    {
        if (empty($this->selectedCopies)) return;

        $count = count($this->selectedCopies);
        Book::whereIn('id', $this->selectedCopies)->update([
            'location' => trim($this->bulkLocation) ?: null,
        ]);

        $this->dispatch('toast', message: "Updated location for {$count} copies.", type: 'success');
        $this->showBulkLocationModal = false;
        $this->clearSelection();
    }

    public function openBulkConditionModal()
    {
        if (empty($this->selectedCopies)) return;
        $this->bulkConditionId = '';
        $this->showBulkConditionModal = true;
    }

    public function saveBulkCondition()
    {
        if (empty($this->selectedCopies) || empty($this->bulkConditionId)) return;

        $count = count($this->selectedCopies);
        $newStatus = match((int)$this->bulkConditionId) {
            4 => 'damaged',
            5 => 'lost',
            default => 'available'
        };

        // Do not change status for currently borrowed copies
        Book::whereIn('id', $this->selectedCopies)
            ->where('status', '!=', 'borrowed')
            ->update([
                'book_condition_id' => $this->bulkConditionId,
                'status' => $newStatus,
            ]);

        Book::whereIn('id', $this->selectedCopies)
            ->where('status', 'borrowed')
            ->update([
                'book_condition_id' => $this->bulkConditionId,
            ]);

        $this->dispatch('toast', message: "Updated condition for {$count} copies.", type: 'success');
        $this->showBulkConditionModal = false;
        $this->clearSelection();
    }

    public function bulkDelete()
    {
        if (empty($this->selectedCopies)) return;

        $deletable = Book::whereIn('id', $this->selectedCopies)
            ->where('status', '!=', 'borrowed')
            ->get();

        $deletedCount = 0;
        foreach ($deletable as $b) {
            $b->delete();
            $deletedCount++;
        }

        $skipped = count($this->selectedCopies) - $deletedCount;
        if ($deletedCount > 0) {
            $msg = "Successfully deleted {$deletedCount} copies.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} borrowed copies were skipped)";
            }
            $this->dispatch('toast', message: $msg, type: 'info');
        } else {
            $this->dispatch('toast', message: 'No selected copies could be deleted (they might be checked out).', type: 'error');
        }

        $this->clearSelection();
    }

    protected function getCurrentPageBookIds(): array
    {
        return $this->buildQuery()->paginate($this->perPage)->pluck('id')->toArray();
    }

    protected function buildQuery()
    {
        $relations = [
            'condition',
            'bookDetail.bookData.authors',
            'bookDetail.bookData.categories',
            'bookDetail.publisher',
        ];

        if ($this->inventoryView === 'Titles') {
            $relations[] = 'bookDetail.books';
        }

        $query = Book::with($relations)->select('books.*');

        if ($this->inventoryView === 'Titles') {
            $representativeCopies = Book::selectRaw('MIN(id) as id')
                ->groupBy('book_detail_id');

            $query->joinSub($representativeCopies, 'primary_books', function ($join) {
                $join->on('books.id', '=', 'primary_books.id');
            });
        }

        // Search Query
        if (!empty($this->search)) {
            $searchVal = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchVal) {
                $q->where('books.accession_number', 'like', $searchVal)
                  ->orWhere('books.code', 'like', $searchVal)
                  ->orWhere('books.location', 'like', $searchVal)
                  ->orWhere('books.status', 'like', $searchVal)
                  ->orWhereHas('condition', function ($cq) use ($searchVal) {
                      $cq->where('status', 'like', $searchVal);
                  })
                  ->orWhereHas('bookDetail', function ($dq) use ($searchVal) {
                      $dq->where('isbn', 'like', $searchVal)
                        ->orWhere('call_number', 'like', $searchVal)
                        ->orWhere('classification', 'like', $searchVal)
                        ->orWhereHas('bookData', function ($bq) use ($searchVal) {
                            $bq->where('book_title', 'like', $searchVal)
                              ->orWhere('subtitle', 'like', $searchVal)
                              ->orWhereHas('authors', function ($aq) use ($searchVal) {
                                  $aq->where('first_name', 'like', $searchVal)
                                    ->orWhere('last_name', 'like', $searchVal);
                              })
                              ->orWhereHas('categories', function ($catq) use ($searchVal) {
                                  $catq->where('name', 'like', $searchVal);
                              });
                        });
                  });
            });
        }

        // Active Tab Filter
        if ($this->activeTab === 'Available') {
            $this->inventoryView === 'Titles'
                ? $query->whereHas('bookDetail.books', fn ($q) => $q->where('status', 'available'))
                : $query->where('books.status', 'available');
        } elseif ($this->activeTab === 'Borrowed') {
            $this->inventoryView === 'Titles'
                ? $query->whereHas('bookDetail.books', fn ($q) => $q->where('status', 'borrowed'))
                : $query->where('books.status', 'borrowed');
        } elseif ($this->activeTab === 'Damaged/Lost') {
            if ($this->inventoryView === 'Titles') {
                $query->whereHas('bookDetail.books', function ($q) {
                    $q->whereIn('book_condition_id', [4, 5])
                        ->orWhereIn('status', ['damaged', 'lost']);
                });
            } else {
                $query->where(function ($dq) {
                    $dq->whereIn('books.book_condition_id', [4, 5])
                       ->orWhereIn('books.status', ['damaged', 'lost']);
                });
            }
        }

        // Dropdown Filters
        if (!empty($this->filterLocation)) {
            $query->where('books.location', $this->filterLocation);
        }
        if (!empty($this->filterCondition)) {
            $query->where('books.book_condition_id', $this->filterCondition);
        }
        if (!empty($this->filterStatus)) {
            $query->where('books.status', $this->filterStatus);
        }
        if (!empty($this->filterAuthor)) {
            $authorVal = '%' . trim($this->filterAuthor) . '%';
            $query->whereHas('bookDetail.bookData.authors', function ($aq) use ($authorVal) {
                $aq->where('first_name', 'like', $authorVal)
                   ->orWhere('last_name', 'like', $authorVal);
            });
        }
        if (!empty($this->filterYear)) {
            $query->whereHas('bookDetail', function ($dq) {
                $dq->where('publication_year', $this->filterYear);
            });
        }

        // Sorting
        $sortColumn = $this->sort['column'];
        $sortDirection = $this->sort['direction'];

        if ($sortColumn === 'book_title') {
            $query->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                  ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
                  ->select('books.*')
                  ->orderBy('book_datas.book_title', $sortDirection);
        } elseif ($sortColumn === 'isbn') {
            $query->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
                  ->select('books.*')
                  ->orderBy('book_details.isbn', $sortDirection);
        } elseif ($sortColumn === 'condition') {
            $query->join('book_conditions', 'books.book_condition_id', '=', 'book_conditions.id')
                  ->select('books.*')
                  ->orderBy('book_conditions.status', $sortDirection);
        } elseif (in_array($sortColumn, ['accession_number', 'code', 'location', 'status', 'created_at', 'id'])) {
            $query->orderBy("books.{$sortColumn}", $sortDirection);
        } else {
            $query->orderBy('books.id', $sortDirection);
        }

        return $query;
    }

    public function render()
    {
        $books = $this->buildQuery()->paginate($this->perPage);

        // Fetch counts for current filter state
        $totalTitles = BookDetail::whereHas('books')->count();
        $totalCopies = Book::count();
        $availableCopies = Book::where('status', 'available')->count();
        $borrowedCopies = Book::where('status', 'borrowed')->count();
        $damagedLostCopies = Book::where(function ($dq) {
            $dq->whereIn('book_condition_id', [4, 5])
               ->orWhereIn('status', ['damaged', 'lost']);
        })->count();

        // Conditions & Distinct Locations for Filter Dropdown
        $conditions = BookCondition::all();
        $locations = Book::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location')->sort()->values();

        // Viewing Book object if details modal is open
        $viewingBook = $this->viewingBookId ? Book::with(['condition', 'bookDetail.bookData.authors', 'bookDetail.publisher'])->find($this->viewingBookId) : null;
        $historyBook = $this->historyBookId ? Book::with(['borrowingTransactions.user', 'bookDetail.bookData'])->find($this->historyBookId) : null;

        return view('livewire.pages.dashboard.book-manager', [
            'books' => $books,
            'totalTitles' => $totalTitles,
            'totalCopies' => $totalCopies,
            'availableCopies' => $availableCopies,
            'borrowedCopies' => $borrowedCopies,
            'damagedLostCopies' => $damagedLostCopies,
            'conditions' => $conditions,
            'locations' => $locations,
            'viewingBook' => $viewingBook,
            'historyBook' => $historyBook,
        ]);
    }
}
