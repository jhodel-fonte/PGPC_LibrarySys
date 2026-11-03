<?php

namespace App\Jobs;

use App\Livewire\Components\Usermanager\StudentTable;
use App\Livewire\Pages\Dashboard\Analytics;
use App\Livewire\Pages\Dashboard\BookManager;
use App\Models\Account;
use App\Models\Book;
use App\Services\EmployeeRoleService;
use App\Services\SpreadsheetExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ExportSpreadsheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(public int $userId, public string $notificationId, public string $kind, public array $filters) {}

    public static function allowed(Account $user, string $kind): bool
    {
        return match ($kind) {
            'catalog' => $user->can('viewAny', Book::class),
            'students' => EmployeeRoleService::canViewStudents($user),
            'analytics' => EmployeeRoleService::canViewReports($user),
            default => false,
        };
    }

    public static function queueFor(Account $user, string $kind, array $filters): void
    {
        $id = (string) Str::uuid();
        $user->notifications()->create([
            'id' => $id,
            'type' => self::class,
            'data' => ['title' => 'Excel export queued', 'message' => 'Your '.$kind.' export is waiting for the queue worker.', 'status' => 'queued', 'kind' => $kind],
        ]);
        try {
            self::dispatch($user->id, $id, $kind, $filters)->onConnection('database');
        } catch (Throwable $e) {
            $user->notifications()->whereKey($id)->delete();
            throw $e;
        }
    }

    public function handle(): void
    {
        $user = Account::findOrFail($this->userId);
        $notification = $user->notifications()->whereKey($this->notificationId)->first();
        if (!$notification) return;
        abort_unless(self::allowed($user, $this->kind), 403);
        $notification->update(['data' => array_merge($notification->data, ['title' => 'Excel export in progress', 'message' => 'Preparing your '.$this->kind.' spreadsheet.', 'status' => 'running'])]);
        $previous = Auth::user();
        Auth::setUser($user);
        try {
            $component = match ($this->kind) {
                'catalog' => new BookManager(),
                'students' => new StudentTable(),
                'analytics' => new Analytics(),
            };
            foreach ($this->filters as $key => $value) $component->$key = $value;
            $workbook = SpreadsheetExport::workbook($component->exportSheets());
            $path = 'exports/'.$this->notificationId.'.xlsx';
            try {
                Storage::disk('local')->makeDirectory('exports');
                (new Xlsx($workbook))->save(Storage::disk('local')->path($path));
            } finally {
                $workbook->disconnectWorksheets();
            }
            $updated = $user->notifications()->whereKey($this->notificationId)->update(['read_at' => null, 'data' => json_encode(array_merge($notification->data, ['title' => 'Excel export ready', 'message' => 'Your '.$this->kind.' spreadsheet is ready to download.', 'status' => 'completed']))]);
            if (!$updated) Storage::disk('local')->delete($path);
        } finally {
            $previous ? Auth::setUser($previous) : Auth::forgetUser();
        }
    }

    public function failed(?Throwable $exception): void
    {
        $notification = Account::find($this->userId)?->notifications()->whereKey($this->notificationId)->first();
        if ($notification) {
            $notification->update(['read_at' => null, 'data' => array_merge($notification->data, ['title' => 'Excel export failed', 'message' => 'The '.$this->kind.' export could not be completed. Please try again.', 'status' => 'failed'])]);
        }
        Storage::disk('local')->delete('exports/'.$this->notificationId.'.xlsx');
    }
}
