<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookDetail;
use App\Models\OpacCatalogView;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management'])]
class BookManager extends Component
{
    use WithPagination;

    // Search & Tabs
    public $search = '';
    public $inventoryView = 'Titles'; // Titles, Copies
    public string $catalogFilter = 'all';
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

    // View Details Modal
    public $showDetailsModal = false;
    public $viewingBookId = null;

    protected function rules(): array
    {
        $codeRule = Rule::unique('books', 'code');
        if ($this->editingBookId) {
            $codeRule = $codeRule->ignore($this->editingBookId);
        }

        return [
            'editCode' => ['nullable', 'string', 'max:255', $codeRule],
            'editLocation' => 'nullable|string|max:100',
            'editConditionId' => 'required|exists:book_conditions,id',
            'editStatus' => 'required|string|in:available,borrowed,reserved,damaged,lost,maintenance',
        ];
    }

    public function mount(): void
    {
        $searchParam = request()->query('search');
        if (!empty($searchParam)) {
            $this->search = trim($searchParam);
        }

        $detailId = request()->query('detail');
        if (!empty($detailId)) {
            $this->dispatch('open-book-details', id: (int) $detailId);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedCatalogFilter(): void
    {
        if (!in_array($this->catalogFilter, ['all', 'books', 'physical', 'magazine', 'journal'], true)) {
            $this->catalogFilter = 'all';
        }

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
        if ($tab === 'Deleted' && !\App\Services\EmployeeRoleService::isAdmin()) {
            $tab = 'All Copies';
        }
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
            ['index' => 'details', 'label' => 'Title', 'sortable' => true],
            ['index' => 'accession', 'label' => 'Accession No.', 'sortable' => true],
            ['index' => 'code', 'label' => 'Unique Code', 'sortable' => true],
            ['index' => 'location', 'label' => 'Location', 'sortable' => true],
            ['index' => 'condition', 'label' => 'Condition', 'sortable' => true],
            ['index' => 'status', 'label' => 'Status', 'sortable' => true],
            ['index' => 'fine', 'label' => 'Overdue fine / day', 'sortable' => false],
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
            $this->editCode = $book->code ?? '';
            $this->editLocation = $book->location ?? '';
            $this->editConditionId = $book->book_condition_id;
            $this->editStatus = $book->status;
            $this->showEditModal = true;
        }
    }

    public function generateEditCode(): void
    {
        $this->editCode = 'BK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
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
        if (auth()->check() && !auth()->user()->can('update', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to update book copies.');
        }

        $this->validate();

        $book = Book::find($this->editingBookId);
        if ($book) {
            $book->update([
                'code' => trim($this->editCode) ?: null,
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
        if (auth()->check() && !auth()->user()->can('update', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to update book condition.');
        }

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

    public function deleteCopy($bookId): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        $book = Book::findOrFail($bookId);
        $type = $this->inventoryView === 'Titles' ? 'title' : 'copy';
        $this->dispatch('request-catalog-delete', type: $type, ids: [$type === 'title' ? $book->book_detail_id : $book->id]);
    }
    public function deleteIssue(int $bookId): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        $book = Book::with('bookDetail')->findOrFail($bookId);
        abort_unless(in_array($book->bookDetail?->catalog_kind, ['magazine', 'journal'], true), 422);
        $this->dispatch('request-catalog-delete', type: 'issue', ids: [$book->book_detail_id]);
    }

    #[On('restore-book-copy')]
    public function restoreBookCopy($copyId = null): void
    {
        $id = is_array($copyId) ? ($copyId['copyId'] ?? $copyId['id'] ?? null) : $copyId;
        if (!$id) return;

        if (auth()->check() && !auth()->user()->can('update', Book::class) && !auth()->user()->can('create', Book::class)) {
            $this->dispatch('toast', message: 'Unauthorized action to restore book copy.', type: 'error');
            return;
        }

        $book = Book::withTrashed()->find($id);
        if ($book && $book->trashed()) {
            BookDetail::onlyTrashed()->find($book->book_detail_id)?->restore();
            $book->restore();
            $this->dispatch('toast', message: 'Book copy "' . $book->accession_number . '" was restored successfully.', type: 'success');
            $this->dispatch('copy-updated', bookDetailId: $book->book_detail_id, copyId: $book->id);
            $this->dispatch('book-details-updated');
        }
    }

    // View Details Modal
    public function viewDetails($bookId)
    {
        $book = Book::withTrashed()->find($bookId);
        $this->dispatch('open-book-details', id: $book?->book_detail_id ?? $bookId, bookId: $bookId);
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->viewingBookId = null;
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

        if (auth()->check() && !auth()->user()->can('update', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to update book copies.');
        }

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

        if (auth()->check() && !auth()->user()->can('update', Book::class)) {
            abort(403, 'Unauthorized action. You do not have permission to update book copies.');
        }

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

    public function bulkDelete(): void
    {
        abort_unless(auth()->user()?->can('delete', Book::class), 403);
        $copies = Book::whereIn('id', $this->selectedCopies)->get();
        $type = $this->inventoryView === 'Titles' ? 'title' : 'copy';
        $ids = $copies->pluck($type === 'title' ? 'book_detail_id' : 'id')->unique()->values()->all();
        if ($ids) {
            $this->dispatch('request-catalog-delete', type: $type, ids: $ids);
        }
    }

    #[On('catalog-deletion-completed')]
    public function afterCatalogDeletion(): void
    {
        $this->clearSelection();
    }
    public function bulkRestore(): void
    {
        if (empty($this->selectedCopies)) return;

        if (auth()->check() && !auth()->user()->can('update', Book::class) && !auth()->user()->can('create', Book::class)) {
            $this->dispatch('toast', message: 'Unauthorized action to restore book copies.', type: 'error');
            return;
        }

        $books = Book::onlyTrashed()->whereIn('id', $this->selectedCopies)->get();
        BookDetail::onlyTrashed()->whereIn('id', $books->pluck('book_detail_id'))->restore();
        $count = Book::onlyTrashed()->whereIn('id', $books->modelKeys())->restore();
        if ($count > 0) {
            $this->dispatch('toast', message: "Restored {$count} book copies successfully.", type: 'success');
            $this->dispatch('book-details-updated');
        }

        $this->clearSelection();
    }

    #[On('restore-book-copies')]
    public function restoreBookCopies($copyIds = null): void
    {
        $ids = is_array($copyIds) && isset($copyIds['copyIds']) ? $copyIds['copyIds'] : (array) $copyIds;
        if (empty($ids)) return;

        if (auth()->check() && !auth()->user()->can('update', Book::class) && !auth()->user()->can('create', Book::class)) {
            $this->dispatch('toast', message: 'Unauthorized action to restore book copies.', type: 'error');
            return;
        }

        $books = Book::onlyTrashed()->whereIn('id', $ids)->get();
        BookDetail::onlyTrashed()->whereIn('id', $books->pluck('book_detail_id'))->restore();
        $count = Book::onlyTrashed()->whereIn('id', $books->modelKeys())->restore();
        if ($count > 0) {
            $this->dispatch('toast', message: "Restored {$count} book copies successfully.", type: 'success');
            $this->dispatch('book-details-updated');
        }
    }

    protected function getCurrentPageBookIds(): array
    {
        return $this->buildQuery()->paginate($this->perPage)->pluck('id')->toArray();
    }

    protected function buildQuery()
    {
        $isSuperAdmin = \App\Services\EmployeeRoleService::isAdmin();
        $isDeletedTab = $isSuperAdmin && $this->activeTab === 'Deleted';

        if (!$isSuperAdmin && $this->activeTab === 'Deleted') {
            $this->activeTab = 'All Copies';
        }

        $relations = [
            'condition',
            'bookDetail' => fn ($q) => $isDeletedTab ? $q->withTrashed() : $q->whereNull('deleted_at'),
            'bookDetail.periodicalIssue',
            'bookDetail.bookData.authors',
            'bookDetail.bookData.categories',
            'bookDetail.publisher',
        ];

        if ($this->inventoryView === 'Titles') {
            $relations['bookDetail.books'] = fn ($q) => $isDeletedTab ? $q->onlyTrashed() : $q->whereNull('deleted_at');
        }

        $query = ($isDeletedTab ? Book::onlyTrashed() : Book::whereNull('books.deleted_at'))
            ->with($relations)
            ->select('books.*');

        if (! $isDeletedTab) {
            $query->whereHas('bookDetail', fn ($detail) => $detail->whereHas('bookData'));
        }

        $query->whereHas('bookDetail', function ($detail) use ($isDeletedTab) {
            if ($isDeletedTab) $detail->withTrashed();
            $this->applyCatalogType($detail);
        });

        if ($this->inventoryView === 'Titles') {
            $representativeCopies = ($isDeletedTab ? Book::onlyTrashed() : Book::whereNull('deleted_at'))
                ->selectRaw('MIN(id) as id')
                ->groupBy('book_detail_id');

            $query->joinSub($representativeCopies, 'primary_books', function ($join) {
                $join->on('books.id', '=', 'primary_books.id');
            });
        }

        // Search Query (via Laravel Scout Database Driver)
        if (!empty($this->search)) {
            $searchTerm = trim($this->search);

            // 1. Search copy-level attributes via Scout on Book (accession_number, code, location, status)
            $copyIds = Book::search($searchTerm)
                ->query(fn ($q) => $isDeletedTab ? $q->onlyTrashed() : $q->whereNull('deleted_at'))
                ->keys();

            // 2. Search bibliographic & catalog attributes via Scout on OpacCatalogView (title, authors, categories, isbn, call_number, publisher)
            $detailIds = BookDetail::whereHas('bookData', fn ($q) => $q->where('book_title', 'ilike', '%'.$searchTerm.'%'))->pluck('id')
                ->merge(OpacCatalogView::search($searchTerm)->keys())->unique();

            // 3. Search condition status (e.g. Good, Fair, Damaged)
            $conditionIds = BookCondition::where('status', 'ilike', '%' . $searchTerm . '%')->pluck('id')->toArray();

            $query->where(function ($q) use ($copyIds, $detailIds, $conditionIds) {
                $q->whereIn('books.id', $copyIds)
                  ->orWhereIn('books.book_detail_id', $detailIds);

                if (!empty($conditionIds)) {
                    $q->orWhereIn('books.book_condition_id', $conditionIds);
                }
            });
        }

        // Active Tab Filter
        if ($this->activeTab === 'Available') {
            $this->inventoryView === 'Titles'
                ? $query->whereHas('bookDetail.books', fn ($q) => $q->whereNull('deleted_at')->where('status', 'available'))
                : $query->whereNull('books.deleted_at')->where('books.status', 'available');
        } elseif ($this->activeTab === 'Borrowed') {
            $this->inventoryView === 'Titles'
                ? $query->whereHas('bookDetail.books', fn ($q) => $q->whereNull('deleted_at')->where('status', 'borrowed'))
                : $query->whereNull('books.deleted_at')->where('books.status', 'borrowed');
        } elseif ($this->activeTab === 'Damaged/Lost') {
            if ($this->inventoryView === 'Titles') {
                $query->whereHas('bookDetail.books', function ($q) {
                    $q->whereNull('deleted_at')
                      ->where(function ($sub) {
                          $sub->whereIn('book_condition_id', [4, 5])
                              ->orWhereIn('status', ['damaged', 'lost']);
                      });
                });
            } else {
                $query->whereNull('books.deleted_at')
                      ->where(function ($dq) {
                          $dq->whereIn('books.book_condition_id', [4, 5])
                             ->orWhereIn('books.status', ['damaged', 'lost']);
                      });
            }
        } elseif ($isDeletedTab) {
            if ($this->inventoryView === 'Titles') {
                $query->whereHas('bookDetail', fn ($q) => $q->withTrashed()->whereHas('books', fn ($copies) => $copies->onlyTrashed()));
            } else {
                $query->onlyTrashed();
            }
        } else {
            // 'All Copies' or any other tab: strictly exclude soft-deleted copies
            $query->whereNull('books.deleted_at');
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

    private function applyCatalogType($query): void
    {
        match ($this->catalogFilter) {
            'books' => $query->where('catalog_kind', 'book'),
            'magazine' => $query->where('catalog_kind', 'magazine'),
            'journal' => $query->where('catalog_kind', 'journal'),
            'physical' => $query->whereNotIn('catalog_kind', ['book', 'magazine', 'journal']),
            default => null,
        };
    }

    public function render()
    {
        $books = $this->buildQuery()->paginate($this->perPage);

        // Fetch counts for the selected catalog type, excluding soft-deleted records.
        $catalogKindFilter = function ($detail) {
            $detail->whereHas('bookData');
            $this->applyCatalogType($detail);
        };
        $titleQuery = BookDetail::whereHas('bookData')->whereHas('books');
        $this->applyCatalogType($titleQuery);
        $totalTitles = $titleQuery->count();
        $activeCopies = Book::whereHas('bookDetail', $catalogKindFilter);
        $totalCopies = (clone $activeCopies)->count();
        $availableCopies = (clone $activeCopies)->where('status', 'available')->count();
        $borrowedCopies = (clone $activeCopies)->where('status', 'borrowed')->count();
        $damagedLostCopies = (clone $activeCopies)->where(function ($dq) {
            $dq->whereIn('book_condition_id', [4, 5])
               ->orWhereIn('status', ['damaged', 'lost']);
        })->count();
        $deletedCopies = \App\Services\EmployeeRoleService::isAdmin()
            ? Book::onlyTrashed()->whereHas('bookDetail', function ($detail) use ($catalogKindFilter) {
                $detail->withTrashed();
                $catalogKindFilter($detail);
            })->count()
            : 0;

        // Conditions & Distinct Locations for Filter Dropdown
        $conditions = BookCondition::all();
        $locations = Book::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location')->sort()->values();

        // Viewing Book object if details modal is open
        $viewingBook = $this->viewingBookId ? Book::withTrashed()->with(['condition', 'bookDetail.bookData.authors', 'bookDetail.publisher'])->find($this->viewingBookId) : null;

        return view('livewire.pages.dashboard.book-manager', [
            'books' => $books,
            'totalTitles' => $totalTitles,
            'totalCopies' => $totalCopies,
            'availableCopies' => $availableCopies,
            'borrowedCopies' => $borrowedCopies,
            'damagedLostCopies' => $damagedLostCopies,
            'deletedCopies' => $deletedCopies,
            'conditions' => $conditions,
            'locations' => $locations,
            'viewingBook' => $viewingBook,
        ]);
    }
}
