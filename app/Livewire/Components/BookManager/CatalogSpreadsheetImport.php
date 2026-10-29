<?php

namespace App\Livewire\Components\BookManager;

use App\Models\Book;
use App\Models\CatalogImport;
use App\Models\CatalogImportRow;
use App\Services\CatalogSpreadsheetImportService;
use App\Services\ExtractMarcData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CatalogSpreadsheetImport extends Component
{
    use WithFileUploads, WithPagination;

    // Upload state
    public $spreadsheet;
    public string $selectedFileName = '';
    public string $selectedFileSize = '';
    public string $detectedFileType = ''; // 'spreadsheet' or 'marc'
    public bool $hasSelectedFile = false;
    public ?string $fileValidationError = null;

    // Cataloging options
    public string $defaultCollection = 'General Circulation';
    public string $defaultMaterialType = 'Book';
    public bool $autoGenerateAccession = true;
    public string $accessionPrefix = 'PGPC-2026-';
    public int $startAccessionNumber = 1001;

    // Review / Processed state
    public ?int $catalogImportId = null;
    public ?string $marcImportKey = null;
    public array $marcFileSummary = [];

    // Notifications & Status
    public ?string $message = null;
    public ?string $messageType = null;
    public bool $importCompleted = false;
    public int $importedCount = 0;
    public int $failedCount = 0;

    // Spreadsheet Preview & Table state
    public string $activeTab = 'all'; // 'all', 'ready', 'issues'
    public string $search = '';
    public int $perPage = 10;
    public ?int $inspectRowId = null;
    public ?array $inspectRowData = null;

    protected const MARC_EXTENSIONS = ['mrc', 'marc', 'dat', 'xml'];
    protected const SPREADSHEET_EXTENSIONS = ['xlsx', 'xls', 'csv', 'txt'];
    protected const ALL_ALLOWED_EXTENSIONS = ['xlsx', 'xls', 'csv', 'txt', 'mrc', 'marc', 'dat', 'xml'];

    public function mount(): void
    {
        $this->accessionPrefix = 'PGPC-' . date('Y') . '-';

        // Suggest next accession number based on latest book
        $latest = Book::orderBy('id', 'desc')->value('accession_number');
        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $this->startAccessionNumber = ((int) $matches[1]) + 1;
        } else {
            $this->startAccessionNumber = 1001;
        }

        // Reconnect to active background import if exists for this user
        if (auth()->check()) {
            $activeImport = CatalogImport::where('uploaded_by', auth()->id())
                ->where('status', 'importing')
                ->latest()
                ->first();

            if ($activeImport) {
                $this->catalogImportId = $activeImport->id;
            }
        }
    }

    /**
     * Triggered when file input updates.
     */
    public function updatedSpreadsheet(): void
    {
        $this->fileValidationError = null;
        $this->message = null;
        $this->messageType = null;

        if (!$this->spreadsheet) {
            $this->hasSelectedFile = false;
            return;
        }

        try {
            $this->validate([
                'spreadsheet' => 'required|file|max:51200', // 50MB
            ]);

            $ext = strtolower($this->spreadsheet->getClientOriginalExtension());

            if (!in_array($ext, self::ALL_ALLOWED_EXTENSIONS, true)) {
                $this->fileValidationError = "Unsupported file format (.{$ext}). Please select an Excel (.xlsx, .xls), CSV (.csv, .txt), or MARC (.mrc, .marc, .dat, .xml) file.";
                $this->clearFile();
                return;
            }

            $this->selectedFileName = $this->spreadsheet->getClientOriginalName();
            $this->selectedFileSize = $this->formatBytes($this->spreadsheet->getSize());
            $this->detectedFileType = $this->isMarcFile($ext) ? 'marc' : 'spreadsheet';
            $this->hasSelectedFile = true;
        } catch (Throwable $e) {
            $this->fileValidationError = 'File upload failed: ' . $e->getMessage();
            $this->clearFile();
        }
    }

    /**
     * Clear selected file before review.
     */
    public function clearFile(): void
    {
        $this->reset('spreadsheet', 'selectedFileName', 'selectedFileSize', 'detectedFileType', 'hasSelectedFile');
    }

    /**
     * Step 1 -> Step 2: Process and validate file records for preview.
     */
    public function review(CatalogSpreadsheetImportService $service): void
    {
        @set_time_limit(300);
        $this->resetValidation('spreadsheet');
        $this->fileValidationError = null;
        $this->message = null;
        $this->messageType = null;

        if (!$this->spreadsheet) {
            $this->fileValidationError = 'Please select a catalog file to review.';
            return;
        }

        $extension = strtolower($this->spreadsheet->getClientOriginalExtension());

        // Handle MARC file review
        if ($this->isMarcFile($extension)) {
            $this->reviewMarcFile($extension);
            return;
        }

        // Handle Spreadsheet review
        $originalName = $this->spreadsheet->getClientOriginalName();
        $safeName = 'catalog_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $extension;
        $storedPath = null;

        try {
            Storage::disk('local')->makeDirectory('catalog_imports');
            $storedPath = $this->spreadsheet->storeAs('catalog_imports', $safeName, 'local');

            if (!$storedPath) {
                throw new \RuntimeException('The uploaded file could not be saved to disk.');
            }

            $options = [
                'default_material_type' => $this->defaultMaterialType,
                'default_collection' => $this->defaultCollection,
                'auto_generate_accession' => $this->autoGenerateAccession,
                'accession_prefix' => $this->accessionPrefix,
                'start_accession_number' => $this->startAccessionNumber,
            ];

            $import = $service->review(
                Storage::disk('local')->path($storedPath),
                $originalName,
                auth()->id(),
                $options
            );

            $import->update(['source_file_path' => $storedPath]);
            $this->catalogImportId = $import->id;

            $validCount = $import->summary['valid_count'] ?? 0;
            $invalidCount = $import->summary['invalid_count'] ?? 0;

            $this->message = "{$import->total_rows} row(s) read from {$originalName}. {$validCount} ready to import" . ($invalidCount > 0 ? ", {$invalidCount} with issues." : '.');
            $this->messageType = $invalidCount > 0 ? 'warning' : 'info';

            $this->reset('spreadsheet', 'hasSelectedFile');
            $this->resetPage();
        } catch (Throwable $exception) {
            Log::error('Catalog spreadsheet review failed.', [
                'file_name' => $originalName,
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            if ($storedPath && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }

            $this->catalogImportId = null;
            $this->fileValidationError = 'Processing failed: ' . $exception->getMessage();
            $this->message = $this->fileValidationError;
            $this->messageType = 'error';
            $this->reset('spreadsheet', 'hasSelectedFile');
        }
    }

    /**
     * Process MARC format upload.
     */
    protected function reviewMarcFile(string $extension): void
    {
        Storage::disk('local')->makeDirectory('marc_uploads');
        $name = $this->spreadsheet->getClientOriginalName();
        $safeName = 'marc_' . time() . '_' . Str::random(10) . '.' . $extension;
        $storedPath = $this->spreadsheet->storeAs('marc_uploads', $safeName, 'local');
        $this->marcImportKey = 'marc_' . (auth()->id() ?? 'guest') . '_' . Str::random(16);

        $result = app(ExtractMarcData::class)->extractFromFile(
            Storage::disk('local')->path($storedPath),
            $name,
            $this->marcImportKey
        );

        $this->reset('spreadsheet', 'hasSelectedFile');

        if (!$result['success'] || empty($result['records'])) {
            $this->marcImportKey = null;
            $this->message = $result['error'] ?? 'No valid MARC records were found in the selected file.';
            $this->messageType = 'warning';
            return;
        }

        $this->marcFileSummary = $result['summary'];
        $this->message = null;
        $this->messageType = null;
    }

    /**
     * Check if extension or contents correspond to a MARC file.
     */
    protected function isMarcFile(string $extension): bool
    {
        if (in_array($extension, self::MARC_EXTENSIONS, true)) {
            return true;
        }

        if ($extension === 'txt' && $this->spreadsheet) {
            $handle = @fopen($this->spreadsheet->getRealPath(), 'rb');
            $head = $handle ? (string) fread($handle, 5) : '';
            if ($handle) {
                fclose($handle);
            }

            return (bool) preg_match('/^\d{5}$/', $head);
        }

        return false;
    }

    /**
     * Step 2 -> Step 3: Dispatch background queue job to commit valid rows into library catalog.
     */
    public function importSpreadsheet(): void
    {
        $import = $this->catalogImport;
        if (!$import) {
            return;
        }

        // Mark import as importing
        $import->update(['status' => 'importing']);

        $actionUrl = request()->is('librarian*')
            ? route('librarian.books.index')
            : route('admin.books.index');

        // Dispatch background job to database queue
        \App\Jobs\ProcessCatalogImportJob::dispatch($import->id, auth()->id(), $actionUrl);

        $this->message = "Import is running in the background. You can safely refresh, browse other pages, or close this tab.";
        $this->messageType = 'info';

        $this->dispatch('show-toast', [
            'type' => 'info',
            'title' => 'Import Queued in Background',
            'message' => 'Adding records to the database. You can safely refresh or navigate away.',
        ]);
    }

    /**
     * Open inspect details modal for a single spreadsheet row.
     */
    public function inspectRow(int $rowId): void
    {
        $row = CatalogImportRow::find($rowId);
        if ($row) {
            $rawData = is_array($row->raw_data) ? $row->raw_data : (json_decode($row->raw_data ?? '{}', true) ?: []);
            $errors = is_array($row->errors) ? $row->errors : (json_decode($row->errors ?? '[]', true) ?: []);

            $this->inspectRowId = $row->id;
            $this->inspectRowData = [
                'id' => $row->id,
                'row_number' => $row->row_number,
                'status' => $row->status,
                'raw_data' => $rawData,
                'errors' => $errors,
            ];
        }
    }

    /**
     * Close inspect details modal.
     */
    public function closeInspectModal(): void
    {
        $this->inspectRowId = null;
        $this->inspectRowData = null;
    }

    /**
     * Reset import back to initial upload state.
     */
    public function resetImport(): void
    {
        if ($this->catalogImport && !empty($this->catalogImport->source_file_path)) {
            if (Storage::disk('local')->exists($this->catalogImport->source_file_path)) {
                Storage::disk('local')->delete($this->catalogImport->source_file_path);
            }
        }

        $this->reset(
            'spreadsheet',
            'selectedFileName',
            'selectedFileSize',
            'detectedFileType',
            'hasSelectedFile',
            'fileValidationError',
            'catalogImportId',
            'marcImportKey',
            'marcFileSummary',
            'message',
            'messageType',
            'activeTab',
            'search',
            'inspectRowId',
            'inspectRowData',
            'importCompleted',
            'importedCount',
            'failedCount'
        );

        $this->resetPage();
    }

    /**
     * Generate and download sample CSV template for librarians.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pgpc_catalog_import_template.csv"',
        ];

        $columns = [
            'title',
            'author',
            'accession_no',
            'material_type',
            'isbn',
            'call_number',
            'publisher',
            'publisher_address',
            'publication_year',
            'categories',
            'subject',
            'sublocation',
        ];

        $sampleRows = [
            [
                'Introduction to Modern Database Systems',
                'C. J. Date; Hugh Darwen',
                'PGPC-2026-0001',
                'Book',
                '978-0321197849',
                'QA76.9.D3 D37',
                'Addison-Wesley',
                'Boston, MA',
                '2022',
                'Computer Science; Information Technology',
                'Database Management; Relational Databases',
                'General Circulation',
            ],
            [
                'Philippine History and Government',
                'Gregorio F. Zaide; Sonia M. Zaide',
                'PGPC-2026-0002',
                'Book',
                '978-9710860579',
                'DS668 .Z35',
                'All-Nations Publishing',
                'Quezon City, Philippines',
                '2021',
                'History; Social Sciences',
                'Philippine Politics; Philippine Republic',
                'Filipiniana Section',
            ],
            [
                'Research Design: Qualitative and Quantitative Approaches',
                'John W. Creswell; J. David Creswell',
                '',
                'Book',
                '978-1506386706',
                'H62 .C69 2018',
                'SAGE Publications',
                'Thousand Oaks, CA',
                '2018',
                'Social Sciences; Research Methods',
                'Qualitative Research; Mixed Methods',
                'Reference Section',
            ],
        ];

        $callback = function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($file, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function getCatalogImportProperty(): ?CatalogImport
    {
        return $this->catalogImportId ? CatalogImport::find($this->catalogImportId) : null;
    }

    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), $precision) . ' ' . $units[$power];
    }

    public function render()
    {
        $catalogImport = $this->catalogImport;
        $paginatedRows = null;
        $validRows = 0;
        $importedRows = 0;
        $issueRows = 0;
        $totalRows = 0;
        $isImporting = false;
        $progressPercent = 0;

        if ($catalogImport) {
            $isImporting = $catalogImport->status === 'importing';

            // Auto-detect background completion or failure
            if ($catalogImport->status === 'completed' && !$this->importCompleted) {
                $this->importCompleted = true;
                $this->importedCount = (int) $catalogImport->imported_rows;
                $this->failedCount = (int) $catalogImport->failed_rows;
                $this->message = "Import completed! Successfully imported {$catalogImport->imported_rows} record(s) into the library catalog." . ($catalogImport->failed_rows > 0 ? " ({$catalogImport->failed_rows} had issues)." : '');
                $this->messageType = $catalogImport->failed_rows > 0 ? 'warning' : 'success';
                $this->activeTab = $this->importedCount > 0 ? 'imported' : 'issues';
            } elseif ($catalogImport->status === 'failed' && $this->messageType !== 'error') {
                $this->message = 'Background import process encountered an error. Please inspect logs or retry.';
                $this->messageType = 'error';
            }

            $totalRows = $catalogImport->total_rows;
            $validRows = $catalogImport->rows()->where('status', 'pending')->count();
            $importedRows = $catalogImport->rows()->where('status', 'imported')->count();
            $issueRows = $catalogImport->rows()->whereIn('status', ['invalid', 'failed'])->count();

            if ($isImporting && $totalRows > 0) {
                $processed = $importedRows + $issueRows;
                $progressPercent = min(100, max(5, (int) round(($processed / $totalRows) * 100)));
            }

            $query = $catalogImport->rows()->orderBy('row_number')->orderBy('id');

            // Tab filter
            if ($this->activeTab === 'ready') {
                $query->where('status', 'pending');
            } elseif ($this->activeTab === 'imported') {
                $query->where('status', 'imported');
            } elseif ($this->activeTab === 'issues') {
                $query->whereIn('status', ['invalid', 'failed']);
            }

            // Search filter
            if (!empty($this->search)) {
                $term = strtolower(trim($this->search));
                $isPgsql = DB::getDriverName() === 'pgsql';

                if ($isPgsql) {
                    $query->where(function ($q) use ($term) {
                        $q->whereRaw('LOWER(raw_data::text) LIKE ?', ["%{$term}%"])
                          ->orWhereRaw('LOWER(COALESCE(errors::text, \'\')) LIKE ?', ["%{$term}%"]);
                    });
                } else {
                    $query->where(function ($q) use ($term) {
                        $q->where('raw_data->title', 'like', "%{$term}%")
                          ->orWhere('raw_data->author', 'like', "%{$term}%")
                          ->orWhere('raw_data->accession_no', 'like', "%{$term}%")
                          ->orWhere('raw_data->isbn', 'like', "%{$term}%");
                    });
                }
            }

            $paginatedRows = $query->cursorPaginate($this->perPage, ['*'], 'page');
        }

        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';

        return view('livewire.components.book-manager.catalog-spreadsheet-import', [
            'catalogImport' => $catalogImport,
            'paginatedRows' => $paginatedRows,
            'totalRows' => $totalRows,
            'validRows' => $validRows,
            'importedRows' => $importedRows,
            'issueRows' => $issueRows,
            'isImporting' => $isImporting,
            'progressPercent' => $progressPercent,
            'routePrefix' => $routePrefix,
        ]);
    }
}
