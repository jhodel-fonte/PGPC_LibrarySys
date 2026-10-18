<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Book Management', 'subpage' => 'Import MARC Records', 'activepageRoute' => 'admin.book-management.index'])]
class Import extends Component
{
    // Upload state: true shows the uploaded file summary and review component; false shows the upload dropzone
    public bool $hasUploadedFile = true;

    // File details
    public string $fileName = 'PGPC_Acquisitions_2026_Batch_A.mrc';
    public string $fileSize = '184.2 KB';
    public string $fileFormat = 'MARC 21 Bibliographic (ISO 2709 Binary)';
    public string $encoding = 'UTF-8 Unicode';
    public string $uploadedAt = 'October 19, 2026 at 10:45 AM';
    public int $totalRecordsFound = 7;

    // Cataloging Preferences
    public string $defaultCollection = 'General Circulation';
    public int $defaultCopies = 1;
    public string $accessionPrefix = 'PGPC-2026-';
    public int $startAccessionNumber = 1042;
    public string $duplicatePolicy = 'skip'; // 'skip', 'update', 'always_create'
    public string $characterEncoding = 'UTF-8';

    public function simulateFileUpload(): void
    {
        $this->hasUploadedFile = true;
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'File Uploaded & Extracted',
            'message' => 'Successfully parsed MARC21 records. You can now review each book below.'
        ]);
    }

    public function removeFile(): void
    {
        $this->hasUploadedFile = false;
    }

    public function render()
    {
        $routePrefix = request()->is('librarian*') ? 'librarian.' : 'admin.';

        return view('livewire.pages.dashboard.import', [
            'routePrefix' => $routePrefix,
        ]);
    }
}
