<?php

namespace App\Livewire\Pages\Dashboard;

use App\Services\ExtractMarcData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Import MARC Records', 'activepageRoute' => 'admin.book-management.index'])]
class Import extends Component
{
    use WithFileUploads;

    // Upload state: true shows the uploaded file summary and review component; false shows the upload dropzone
    public bool $hasUploadedFile = false;

    // Actual uploaded file
    public $marcFile = null;

    // File selection & verification state (Step 1 file check)
    public bool $isFileSelected = false;
    public string $selectedFileName = '';
    public string $selectedFileSize = '';
    public ?string $fileValidationError = null;

    // 30-minute duplicate filename detection notice
    public ?array $duplicateFileNotice = null;

    // Stored file relative path in storage/app/private (e.g. 'marc_uploads/marc_xxxx.mrc')
    public string $storedFilePath = '';

    // Unique cache key for storing extracted MARC records in backend cache
    public string $importKey = '';

    // Extracted records and summary
    public array $records = [];
    public array $fileSummary = [];

    // File details
    public string $fileName = '';
    public string $fileSize = '';
    public string $fileFormat = 'MARC 21 Bibliographic (ISO 2709 Binary)';
    public string $encoding = 'UTF-8 Unicode';
    public string $uploadedAt = '';
    public int $totalRecordsFound = 0;

    // Cataloging Preferences
    public string $defaultCollection = 'General Circulation';
    public int $defaultCopies = 1;
    public string $accessionPrefix = 'PGPC-2026-';
    public int $startAccessionNumber = 1042;
    public string $duplicatePolicy = 'skip'; // 'skip', 'update', 'always_create'
    public string $characterEncoding = 'UTF-8';

    public function mount(): void
    {
        $this->hasUploadedFile = false;
        $this->isFileSelected = false;
        $this->storedFilePath = '';
        $this->importKey = '';
        $this->duplicateFileNotice = null;
        $this->records = [];
        $this->fileSummary = [];
    }

    /**
     * Step 1: Handle file selection/drop.
     * Validates file format, stores to storage/app/private, and immediately extracts and displays preview.
     */
    public function updatedMarcFile(): void
    {
        $this->fileValidationError = null;

        try {
            $this->validate([
                'marcFile' => 'required|file|max:51200', // 50MB max
            ]);

            $ext = strtolower($this->marcFile->getClientOriginalExtension());
            $allowedExtensions = ['mrc', 'marc', 'dat', 'txt', 'xml'];

            if (!in_array($ext, $allowedExtensions)) {
                $this->fileValidationError = "Unsupported file format (.{$ext}). Please select a standard MARC21 binary (.mrc, .marc, .dat) or MARCXML (.xml) file.";
                $this->marcFile = null;
                $this->isFileSelected = false;
                $this->selectedFileName = '';
                $this->selectedFileSize = '';
                $this->storedFilePath = '';

                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'title' => 'Invalid File Format',
                    'message' => $this->fileValidationError,
                ]);
                return;
            }

            // Clean up any previously stored temp file if replacing
            if (!empty($this->storedFilePath) && Storage::disk('local')->exists($this->storedFilePath)) {
                Storage::disk('local')->delete($this->storedFilePath);
            }

            // Ensure destination directory exists in storage/app/private
            Storage::disk('local')->makeDirectory('marc_uploads');

            // Save first to storage/app/private
            $this->selectedFileName = $this->marcFile->getClientOriginalName();
            $this->selectedFileSize = $this->formatBytes($this->marcFile->getSize());
            $safeName = 'marc_' . time() . '_' . Str::random(10) . '.' . $ext;
            $this->storedFilePath = $this->marcFile->storeAs('marc_uploads', $safeName, 'local');
            $this->isFileSelected = true;

            // Spot if a similar file was already uploaded within the 30-minute cache window
            $this->checkDuplicateFilename($this->selectedFileName);
        } catch (\Throwable $e) {
            Log::error('Import::updatedMarcFile error: ' . $e->getMessage());
            $this->fileValidationError = 'File upload failed: ' . $e->getMessage();
            $this->marcFile = null;
            $this->isFileSelected = false;
            $this->selectedFileName = '';
            $this->selectedFileSize = '';
            $this->storedFilePath = '';

            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Upload Failed',
                'message' => $this->fileValidationError,
            ]);
        }
    }

    /**
     * Read verified file from storage/app/private, extract records, and redirect to preview table.
     */
    public function processAndReviewFile(): bool
    {
        $this->fileValidationError = null;

        // If file not saved to private storage yet but marcFile is present, save it first
        if (empty($this->storedFilePath) && $this->marcFile) {
            try {
                Storage::disk('local')->makeDirectory('marc_uploads');
                $ext = strtolower($this->marcFile->getClientOriginalExtension());
                $safeName = 'marc_' . time() . '_' . Str::random(10) . '.' . $ext;
                $this->storedFilePath = $this->marcFile->storeAs('marc_uploads', $safeName, 'local');
                $this->selectedFileName = $this->marcFile->getClientOriginalName();
                $this->selectedFileSize = $this->formatBytes($this->marcFile->getSize());
                $this->isFileSelected = true;
            } catch (\Throwable $e) {
                Log::error('Import::processAndReviewFile store error: ' . $e->getMessage());
                $this->fileValidationError = 'Could not save the uploaded file: ' . $e->getMessage();
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'title' => 'Storage Error',
                    'message' => $this->fileValidationError,
                ]);
                return false;
            }
        }

        if (empty($this->storedFilePath) || !Storage::disk('local')->exists($this->storedFilePath)) {
            if (!$this->fileValidationError) {
                $this->fileValidationError = 'Please select or drop a valid MARC file first.';
            }
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'title' => 'No File Selected',
                'message' => $this->fileValidationError,
            ]);
            return false;
        }

        try {
            // Resolve absolute path in private storage (storage/app/private/marc_uploads/...)
            $fullPath = Storage::disk('local')->path($this->storedFilePath);

            // Generate a unique session-safe cache key for fast pagination and low Livewire state overhead
            $this->importKey = 'marc_' . (auth()->id() ?? 'guest') . '_' . Str::random(16);

            $service = app(ExtractMarcData::class);
            $result = $service->extractFromFile($fullPath, $this->selectedFileName, $this->importKey);

            if (!$result['success'] || empty($result['records'])) {
                $errorMsg = $result['error'] ?? 'Could not extract valid MARC21 records from the uploaded file.';
                $this->fileValidationError = $errorMsg;
                $this->hasUploadedFile = false;
                $this->importKey = '';

                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'title' => 'Extraction Failed',
                    'message' => $errorMsg,
                ]);
                return false;
            }

            // Records are safely cached in backend cache; keep empty in Livewire state to prevent huge request payloads
            $this->records = [];
            $this->fileSummary = $result['summary'];
            $this->fileName = $result['summary']['name'];
            $this->fileSize = $result['summary']['size'];
            $this->fileFormat = $result['summary']['format'];
            $this->encoding = $result['summary']['encoding'];
            $this->uploadedAt = $result['summary']['uploaded_at'];
            $this->totalRecordsFound = $result['summary']['total_parsed'];
            $this->hasUploadedFile = true;
            $this->duplicateFileNotice = null;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'File Read Successfully',
                'message' => "Successfully parsed {$this->totalRecordsFound} bibliographic records. Review the table below before cataloging.",
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Import::processAndReviewFile extraction exception: ' . $e->getMessage());
            $this->fileValidationError = 'Error extracting file: ' . $e->getMessage();
            $this->hasUploadedFile = false;
            $this->importKey = '';

            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Extraction Error',
                'message' => $this->fileValidationError,
            ]);

            return false;
        }
    }

    /**
     * Check if a file with this name was uploaded within the 30-minute cache window.
     */
    public function checkDuplicateFilename(string $fileName): void
    {
        $this->duplicateFileNotice = null;

        $cleanName = trim($fileName);
        if (empty($cleanName)) {
            return;
        }

        $service = app(ExtractMarcData::class);
        $cached = $service->getCachedFileByName($cleanName);

        if ($cached) {
            $uploadedAt = Carbon::parse($cached['uploaded_at']);
            $expiresAt = Carbon::parse($cached['expires_at']);
            $minutesLeft = max(1, (int) round(Carbon::now()->diffInMinutes($expiresAt, false)));
            $timeAgo = $uploadedAt->diffForHumans();

            $this->duplicateFileNotice = [
                'filename' => $cached['filename'],
                'import_key' => $cached['import_key'],
                'total_records' => $cached['total_records'] ?? 0,
                'size' => $cached['size'] ?? '',
                'uploaded_by' => $cached['uploaded_by'] ?? 'Librarian',
                'time_ago' => $timeAgo,
                'minutes_left' => $minutesLeft,
                'records_available' => $cached['records_available'] ?? false,
            ];

            $this->dispatch('show-toast', [
                'type' => 'warning',
                'title' => 'Similar File Recently Uploaded',
                'message' => "A file named '{$cached['filename']}' was already uploaded {$timeAgo} ({$cached['total_records']} records). Cache expires in {$minutesLeft} minutes.",
            ]);
        }
    }

    /**
     * Reuse already-extracted MARC records directly from the 30-minute memory cache.
     */
    public function useCachedImport(string $importKey): void
    {
        $service = app(ExtractMarcData::class);
        $cached = $service->getCachedRecords($importKey);

        if (!$cached || empty($cached['records'])) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'title' => 'Cache Expired',
                'message' => 'The 30-minute cache for this file has expired. Please upload and extract the file again.',
            ]);
            $this->duplicateFileNotice = null;
            return;
        }

        $summary = $cached['summary'] ?? [];
        $this->importKey = $importKey;
        $this->records = [];
        $this->fileSummary = $summary;
        $this->fileName = $summary['name'] ?? $this->selectedFileName;
        $this->fileSize = $summary['size'] ?? $this->selectedFileSize;
        $this->fileFormat = $summary['format'] ?? 'MARC 21 Bibliographic (ISO 2709 Binary)';
        $this->encoding = $summary['encoding'] ?? 'UTF-8 Unicode';
        $this->uploadedAt = $summary['uploaded_at'] ?? Carbon::now()->toIso8601String();
        $this->totalRecordsFound = $summary['total_parsed'] ?? count($cached['records']);
        $this->hasUploadedFile = true;
        $this->duplicateFileNotice = null;

        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Loaded from 30m Cache',
            'message' => "Successfully loaded {$this->totalRecordsFound} records from memory cache without re-uploading.",
        ]);
    }

    /**
     * Remove the current file, delete from private storage, and reset back to empty dropzone.
     */
    public function removeFile(): void
    {
        if (!empty($this->storedFilePath) && Storage::disk('local')->exists($this->storedFilePath)) {
            Storage::disk('local')->delete($this->storedFilePath);
        }

        $this->hasUploadedFile = false;
        $this->marcFile = null;
        $this->isFileSelected = false;
        $this->selectedFileName = '';
        $this->selectedFileSize = '';
        $this->storedFilePath = '';
        $this->fileValidationError = null;
        $this->duplicateFileNotice = null;
        $this->records = [];
        $this->fileSummary = [];
        $this->fileName = '';
        $this->fileSize = '';
        $this->uploadedAt = '';
        $this->totalRecordsFound = 0;
    }

    /**
     * Helper to human-format byte sizes.
     */
    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), $precision) . ' ' . $units[$power];
    }

    public function render()
    {
        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';

        return view('livewire.pages.dashboard.import', [
            'routePrefix' => $routePrefix,
        ]);
    }
}
