<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CatalogImportCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $importedCount,
        public int $failedCount,
        public string $fileName,
        public string $actionUrl,
        public bool $isError = false,
        public ?string $errorMessage = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->isError) {
            return [
                'title' => 'Catalog Import Failed',
                'message' => "The import process for '{$this->fileName}' failed: " . ($this->errorMessage ?? 'Unexpected error occurred.'),
                'type' => 'error',
                'action_url' => $this->actionUrl,
                'imported_count' => 0,
                'failed_count' => $this->failedCount,
            ];
        }

        $hasIssues = $this->failedCount > 0;

        return [
            'title' => $hasIssues ? 'Catalog Import Partially Completed' : 'Catalog Import Completed',
            'message' => "Successfully imported {$this->importedCount} book copy record(s) from '{$this->fileName}' into the catalog." . ($hasIssues ? " ({$this->failedCount} row(s) had issues)." : ''),
            'type' => $hasIssues ? 'warning' : 'success',
            'action_url' => $this->actionUrl,
            'imported_count' => $this->importedCount,
            'failed_count' => $this->failedCount,
        ];
    }
}
