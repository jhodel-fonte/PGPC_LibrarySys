<?php

namespace App\Livewire\Components\BookManager;

use App\Services\ExtractMarcData;
use App\Models\CatalogImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MarcUpload extends Component
{
    use WithPagination;

    // Backend cache key for extracted records
    public string $importKey = '';

    // Active tab and search filter
    public string $activeTab = 'all'; // 'all', 'ready', 'warnings'
    public string $search = '';
    public int $perPage = 10; // 10, 25, 50, 100

    // Selected record IDs for cataloging
    public array $selectedRecords = [];
    public bool $selectAll = false;

    // Configuration passed from parent or defaults
    public string $defaultCollection = 'General Circulation';
    public int $defaultCopies = 1;
    public string $accessionPrefix = 'PGPC-2026-';
    public int $startAccessionNumber = 1042;
    public string $duplicatePolicy = 'skip';

    // File Summary Info
    public array $fileSummary = [
        'name' => '',
        'size' => '',
        'format' => 'MARC 21 Bibliographic (ISO 2709 Binary)',
        'encoding' => 'UTF-8 Unicode',
        'uploaded_at' => '',
        'total_parsed' => 0,
        'ready_count' => 0,
        'warnings_count' => 0,
    ];

    // Raw MARC Tag Modal
    public ?array $activeRecordModal = null;
    public bool $showMarcModal = false;

    // Import action state
    public bool $importCompleted = false;
    public int $importedCount = 0;

    /**
     * Fallback in-memory extracted records (used if backend cache is not supplied)
     */
    public array $records = [];

    /**
     * Runtime memoized records within the same request lifecycle
     */
    protected ?array $memoizedRecords = null;

    public function mount(
        ?string $importKey = null,
        ?array $records = null,
        ?array $fileSummary = null,
        string $defaultCollection = 'General Circulation',
        int $defaultCopies = 1,
        string $accessionPrefix = 'PGPC-2026-',
        int $startAccessionNumber = 1042,
        string $duplicatePolicy = 'skip'
    ): void {
        $this->importKey = $importKey ?? '';
        $this->records = $records ?? [];

        $service = app(ExtractMarcData::class);
        $allRecords = [];

        if (!empty($this->importKey)) {
            $cached = $service->getCachedRecords($this->importKey);
            if ($cached && isset($cached['records']) && is_array($cached['records'])) {
                $allRecords = $cached['records'];
                if (($fileSummary === null || empty($fileSummary)) && isset($cached['summary'])) {
                    $fileSummary = $cached['summary'];
                }
            }
        }

        if (empty($allRecords)) {
            $allRecords = $this->records;
        }

        if ($fileSummary !== null && !empty($fileSummary)) {
            $this->fileSummary = $fileSummary;
        }

        $this->defaultCollection = $defaultCollection;
        $this->defaultCopies = $defaultCopies;
        $this->accessionPrefix = $accessionPrefix;
        $this->startAccessionNumber = $startAccessionNumber;
        $this->duplicatePolicy = $duplicatePolicy;

        // Auto-select records marked 'ready'
        $readyIds = array_map(
            fn($r) => (string)$r['id'],
            array_filter($allRecords, fn($r) => ($r['status'] ?? '') === 'ready')
        );
        $this->selectedRecords = !empty($readyIds) ? $readyIds : array_map(fn($r) => (string)$r['id'], $allRecords);
    }

    /**
     * Retrieve all extracted records from cache or fallback memory.
     */
    public function getAllRecords(): array
    {
        if ($this->memoizedRecords !== null) {
            return $this->memoizedRecords;
        }

        if (!empty($this->importKey)) {
            $cached = app(ExtractMarcData::class)->getCachedRecords($this->importKey);
            if ($cached && isset($cached['records']) && is_array($cached['records'])) {
                $this->memoizedRecords = $cached['records'];
                return $this->memoizedRecords;
            }
        }

        $this->memoizedRecords = $this->records;
        return $this->memoizedRecords;
    }

    /**
     * Persist updated records back to cache (or fallback array).
     */
    protected function saveAllRecords(array $records): void
    {
        $this->memoizedRecords = $records;

        if (!empty($this->importKey)) {
            app(ExtractMarcData::class)->updateCachedRecords(
                $this->importKey,
                $records,
                $this->fileSummary
            );
        } else {
            $this->records = $records;
        }
    }

    /**
     * Reset pagination when search keyword changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when records-per-page changes.
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Set active filter tab and reset pagination.
     */
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Toggle selection for all currently filtered records.
     */
    public function toggleSelectAll(): void
    {
        $filtered = $this->filteredRecords;
        $filteredIds = array_map(fn($r) => (string)$r['id'], $filtered);

        if (empty($filteredIds)) {
            return;
        }

        $allFilteredSelected = empty(array_diff($filteredIds, $this->selectedRecords));

        if ($allFilteredSelected) {
            // Deselect all filtered items
            $this->selectedRecords = array_values(array_diff($this->selectedRecords, $filteredIds));
            $this->selectAll = false;
        } else {
            // Select all filtered items (merge with existing selections)
            $this->selectedRecords = array_values(array_unique(array_merge($this->selectedRecords, $filteredIds)));
            $this->selectAll = true;
        }
    }

    /**
     * Select all records marked as 'ready'.
     */
    public function selectOnlyReady(): void
    {
        $all = $this->getAllRecords();
        $ready = array_filter($all, fn($r) => ($r['status'] ?? '') === 'ready');
        $this->selectedRecords = array_values(array_map(fn($r) => (string)$r['id'], $ready));

        $filteredIds = array_map(fn($r) => (string)$r['id'], $this->filteredRecords);
        $this->selectAll = count($filteredIds) > 0 && empty(array_diff($filteredIds, $this->selectedRecords));
    }

    /**
     * Open MARC21 Raw Tag modal for a specific record.
     */
    public function openMarcModal(string $recordId): void
    {
        $all = $this->getAllRecords();
        foreach ($all as $r) {
            if ((string)$r['id'] === (string)$recordId) {
                $this->activeRecordModal = $r;
                $this->showMarcModal = true;
                return;
            }
        }
    }

    /**
     * Close MARC21 Raw Tag modal.
     */
    public function closeMarcModal(): void
    {
        $this->showMarcModal = false;
        $this->activeRecordModal = null;
    }

    /**
     * Exclude a record from the import batch.
     */
    public function removeRecord(string $recordId): void
    {
        $all = $this->getAllRecords();
        $updated = array_values(array_filter($all, fn($r) => (string)$r['id'] !== (string)$recordId));
        $this->saveAllRecords($updated);

        $this->selectedRecords = array_values(array_filter($this->selectedRecords, fn($id) => (string)$id !== (string)$recordId));
        $this->recalculateCounts($updated);

        $this->dispatch('show-toast', [
            'type' => 'info',
            'title' => 'Record Excluded',
            'message' => 'The selected bibliographic record was excluded from the import batch.',
        ]);
    }

    /**
     * Recalculate summary counts.
     */
    public function recalculateCounts(?array $records = null): void
    {
        $records = $records ?? $this->getAllRecords();
        $this->fileSummary['total_parsed'] = count($records);
        $this->fileSummary['ready_count'] = count(array_filter($records, fn($r) => ($r['status'] ?? '') === 'ready'));
        $this->fileSummary['warnings_count'] = count(array_filter($records, fn($r) => ($r['status'] ?? '') === 'warning'));
    }

    /**
     * Trigger import confirmation modal.
     */
    public function importSelectedRecords(): void
    {
        $selected = array_values(array_filter($this->getAllRecords(), fn ($record) => in_array((string) $record['id'], $this->selectedRecords, true)));
        if (empty($selected)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'No Records Selected',
                'message' => 'Please select at least one record to import into the library collection.'
            ]);
            return;
        }

        abort_unless(auth()->check(), 403);

        try {
            $import = DB::transaction(function () use ($selected) {
                $import = CatalogImport::create([
                    'uploaded_by' => auth()->id(),
                    'source_file_name' => $this->fileSummary['name'] ?? 'MARC import',
                    'source_file_path' => '',
                    'source_type' => 'marc',
                    'status' => 'reviewing',
                    'total_rows' => count($selected),
                    'imported_rows' => 0,
                    'failed_rows' => 0,
                    'summary' => ['options' => [
                        'default_collection' => $this->defaultCollection,
                        'default_copies' => max(1, $this->defaultCopies),
                        'accession_prefix' => $this->accessionPrefix,
                        'start_accession_number' => $this->startAccessionNumber,
                        'duplicate_policy' => $this->duplicatePolicy,
                    ]],
                ]);
                foreach ($selected as $index => $record) {
                    $record['author'] = $record['authors'] ?? '';
                    $import->rows()->create(['row_number' => $index + 1, 'raw_data' => $record, 'status' => 'pending']);
                }
                return $import;
            });
            $this->dispatch('marc-import-ready', importId: $import->id);
        } catch (\Throwable $exception) {
            Log::error('Could not prepare MARC import.', ['exception' => $exception]);
            $this->dispatch('show-toast', ['type' => 'error', 'title' => 'Import failed', 'message' => 'Could not prepare the MARC records. Please try again.']);
        }
    }

    public function simulateImport(): void
    {
        $this->importSelectedRecords();
    }

    public function resetImportState(): void
    {
        $this->importCompleted = false;
        $this->importedCount = 0;
    }

    public function resetSimulation(): void
    {
        $this->resetImportState();
    }

    /**
     * Compute the filtered records array based on activeTab and search term.
     */
    public function getFilteredRecordsProperty(): array
    {
        $all = $this->getAllRecords();

        return array_values(array_filter($all, function ($r) {
            // Tab filter
            if ($this->activeTab === 'ready' && ($r['status'] ?? '') !== 'ready') {
                return false;
            }
            if ($this->activeTab === 'warnings' && ($r['status'] ?? '') !== 'warning') {
                return false;
            }

            // Search filter
            if (!empty($this->search)) {
                $term = strtolower(trim($this->search));
                $inTitle = str_contains(strtolower($r['title'] ?? ''), $term);
                $inAuthor = str_contains(strtolower($r['authors'] ?? ''), $term);
                $inIsbn = str_contains(strtolower($r['isbn'] ?? ''), $term);
                $inCall = str_contains(strtolower($r['call_number'] ?? ''), $term);
                $inPublisher = str_contains(strtolower($r['publisher'] ?? ''), $term);

                return $inTitle || $inAuthor || $inIsbn || $inCall || $inPublisher;
            }

            return true;
        }));
    }

    /**
     * Compute the paginated slice for the preview table.
     */
    public function getPaginatedRecordsProperty(): LengthAwarePaginator
    {
        $filtered = $this->filteredRecords;
        $total = count($filtered);
        $page = (int) $this->getPage();
        $perPage = $this->perPage > 0 ? $this->perPage : 10;
        $lastPage = max(1, (int) ceil($total / $perPage));

        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $items = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function render()
    {
        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
        $allRecords = $this->getAllRecords();
        $filtered = $this->filteredRecords;

        return view('livewire.components.book-manager.marc-upload', [
            'routePrefix' => $routePrefix,
            'paginatedRecords' => $this->paginatedRecords,
            'totalCount' => count($allRecords),
            'filteredCount' => count($filtered),
            'filteredRecords' => $filtered,
        ]);
    }
}
