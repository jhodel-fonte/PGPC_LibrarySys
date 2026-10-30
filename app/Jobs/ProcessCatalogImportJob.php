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

        if ($import->status !== 'importing') {
            Log::info('[ProcessCatalogImportJob] CatalogImport is no longer waiting to run.', [
                'import_id' => $this->catalogImportId,
            ]);
            return;
        }

        // PHP memory exhaustion is fatal and bypasses catch/finally. Keep a small
        // reserve so shutdown can persist a useful failure for the progress page.
        $reserve = str_repeat(' ', 2 * 1024 * 1024);
        $running = true;
        register_shutdown_function(function () use (&$reserve, &$running) {
            $error = error_get_last();
            if (! $running || ! $error || ! in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
                return;
            }
            $reserve = null;
            try {
                // A fatal error can leave the current row transaction open.
                // Roll it back before recording the batch failure.
                while (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                    \Illuminate\Support\Facades\DB::rollBack();
                }
                $this->markFailed(new \RuntimeException($error['message']));
            } catch (Throwable $exception) {
                error_log('Could not record catalog import failure: '.$exception->getMessage());
            }
        });

        try {
            Log::info('[ProcessCatalogImportJob] Starting background catalog import.', [
                'import_id' => $import->id,
                'total_rows' => $import->total_rows,
                'user_id' => $this->userId,
            ]);

            $result = $service->commit($import);

            if ($result->status === 'cancelled') {
                return;
            }

            if ($result->status === 'importing') {
                self::dispatch($this->catalogImportId, $this->userId, $this->actionUrl)->onConnection('database');
                return;
            }

            Log::info('[ProcessCatalogImportJob] Background catalog import finished successfully.', [
                'import_id' => $result->id,
                'imported_rows' => $result->imported_rows,
                'failed_rows' => $result->failed_rows,
            ]);

        } catch (Throwable $e) {
            $this->markFailed($e);
            throw $e;
        } finally {
            $running = false;
            $reserve = null;
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
        if ($import && $import->status === 'importing') {
            $import->update([
                'status' => 'failed',
                'summary' => array_merge($import->summary ?? [], [
                    'error' => $exception && str_contains(strtolower($exception->getMessage()), 'memory')
                        ? 'The import stopped because the server ran out of memory. Imported records were kept. Retry the remaining records or ask an administrator to increase the worker memory limit. Import ID: '.$import->id.'.'
                        : 'The import stopped before all rows were processed. Imported rows were kept; retry the remaining rows or contact an administrator with import ID '.$import->id.'.',
                ]),
            ]);
        }
    }
}
