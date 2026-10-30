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

        } catch (Throwable $e) {
            $this->markFailed($e);
            throw $e;
        }

        // A notification failure must not mark a successfully committed import as failed.
        try {
            if ($this->userId) {
                $user = Account::find($this->userId);
                if ($user) {
                    $fileName = $result->source_file_name ?? 'Catalog File';
                    $actionUrl = $this->actionUrl ?? route(\App\Services\EmployeeRoleService::getRoutePrefix($user) . 'book-management.index');

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
            Log::warning('Catalog import notification failed.', ['import_id' => $import->id, 'exception' => $e]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        Log::error('Catalog import failed.', ['import_id' => $this->catalogImportId, 'exception' => $exception]);
        $import = CatalogImport::find($this->catalogImportId);
        if ($import && $import->status !== 'completed') {
            $import->update([
                'status' => 'failed',
                'summary' => array_merge($import->summary ?? [], [
                    'error' => 'The import stopped before all rows were processed. Imported rows were kept; retry the remaining rows or contact an administrator with import ID '.$import->id.'.',
                ]),
            ]);
        }
    }
}
