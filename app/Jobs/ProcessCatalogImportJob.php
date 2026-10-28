<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Account;
use App\Models\CatalogImport;
use App\Notifications\CatalogImportCompletedNotification;
use App\Services\CatalogSpreadsheetImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCatalogImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 1800;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    public function __construct(
        public int $catalogImportId,
        public ?int $userId = null,
        public ?string $actionUrl = null
    ) {}

    /**
     * Execute the job in the background.
     */
    public function handle(CatalogSpreadsheetImportService $service): void
    {
        $import = CatalogImport::find($this->catalogImportId);
        if (!$import) {
            Log::warning('[ProcessCatalogImportJob] CatalogImport record not found.', [
                'import_id' => $this->catalogImportId,
            ]);
            return;
        }

        if ($import->status === 'completed') {
            Log::info('[ProcessCatalogImportJob] CatalogImport is already completed.', [
                'import_id' => $this->catalogImportId,
            ]);
            return;
        }

        try {
            Log::info('[ProcessCatalogImportJob] Starting background catalog import.', [
                'import_id' => $import->id,
                'total_rows' => $import->total_rows,
                'user_id' => $this->userId,
            ]);

            $result = $service->commit($import);

            Log::info('[ProcessCatalogImportJob] Background catalog import finished successfully.', [
                'import_id' => $result->id,
                'imported_rows' => $result->imported_rows,
                'failed_rows' => $result->failed_rows,
            ]);

            // Dispatch in-app notification to the initiating user
            if ($this->userId) {
                $user = Account::find($this->userId);
                if ($user) {
                    $fileName = $result->source_file_name ?? 'Catalog File';
                    $actionUrl = $this->actionUrl ?? (request()->is('librarian*') ? route('librarian.books.index') : route('admin.books.index'));

                    $user->notify(new CatalogImportCompletedNotification(
                        importedCount: (int) $result->imported_rows,
                        failedCount: (int) $result->failed_rows,
                        fileName: $fileName,
                        actionUrl: $actionUrl
                    ));

                    Log::info('[ProcessCatalogImportJob] Sent CatalogImportCompletedNotification to user.', [
                        'user_id' => $this->userId,
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::error('[ProcessCatalogImportJob] Background catalog import failed with exception.', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $import->update([
                'status' => 'failed',
            ]);

            if ($this->userId) {
                $user = Account::find($this->userId);
                if ($user) {
                    $fileName = $import->source_file_name ?? 'Catalog File';
                    $actionUrl = $this->actionUrl ?? (request()->is('librarian*') ? route('librarian.books.index') : route('admin.books.index'));

                    $user->notify(new CatalogImportCompletedNotification(
                        importedCount: (int) ($import->imported_rows ?? 0),
                        failedCount: (int) ($import->total_rows ?? 0),
                        fileName: $fileName,
                        actionUrl: $actionUrl,
                        isError: true,
                        errorMessage: $e->getMessage()
                    ));
                }
            }

            throw $e;
        }
    }
}
