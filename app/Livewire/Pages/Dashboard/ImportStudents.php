<?php

namespace App\Livewire\Pages\Dashboard;

use App\Jobs\ProcessStudentImport;
use App\Jobs\SendStudentImportInvitation;
use App\Models\StudentImport;
use App\Services\EmployeeRoleService;
use App\Services\StudentImportColumns;
use App\Services\StudentSpreadsheetImportService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.layouts.admin', ['title' => 'User Management', 'subpage' => 'Import Students'])]
class ImportStudents extends Component
{
    use WithFileUploads;

    public $spreadsheet;
    #[Locked] public ?int $importId = null;
    public array $mapping = [];
    public ?string $message = null;

    public function mount(): void
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        $this->importId = StudentImport::where('uploaded_by', auth()->id())->latest()->value('id');
        $this->mapping = $this->import()?->mapping ?? [];
    }

    private function import(): ?StudentImport
    {
        return $this->importId ? StudentImport::where('uploaded_by', auth()->id())->findOrFail($this->importId) : null;
    }

    public function inspect(StudentSpreadsheetImportService $service): void
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        $this->validate(['spreadsheet' => 'required|file|max:51200|extensions:csv,xlsx,xls']);
        if ($this->import()?->status === 'importing') { $this->message = 'Wait for the current import to finish or cancel it first.'; return; }
        $path = $this->spreadsheet->store('student-imports', 'local');
        $import = StudentImport::create(['uploaded_by' => auth()->id(), 'filename' => $this->spreadsheet->getClientOriginalName(), 'file_path' => $path]);
        try {
            $service->inspect($import);
            $this->importId = $import->id;
            $this->mapping = $import->fresh()->mapping;
            $this->message = null;
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            $import->delete();
            $this->message = 'Could not read the spreadsheet: '.$e->getMessage();
        }
    }

    public function review(StudentSpreadsheetImportService $service): void
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        $import = $this->import();
        if (! $import || ! in_array($import->status, ['reviewing', 'reviewed'], true)) return;
        try {
            if (array_diff(array_keys($this->mapping), array_keys($import->headers))) throw new \RuntimeException('Invalid column mapping.');
            $service->stage($import, $this->mapping);
            $this->message = null;
        } catch (\Throwable $e) { $this->message = 'Review failed: '.$e->getMessage(); }
    }

    public function start(): void
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        $import = $this->import();
        if (! $import || $import->status !== 'reviewed' || ! $import->rows()->where('status', 'pending')->exists()) return;
        $updated = StudentImport::whereKey($import->id)->where('status', 'reviewed')->update(['status' => 'importing']);
        if ($updated) {
            try { ProcessStudentImport::dispatch($import->id)->onConnection('database'); }
            catch (\Throwable $e) {
                $import->update(['status' => 'failed', 'error' => 'Could not start the background import. Check the queue configuration.']);
                report($e);
            }
        }
    }

    public function cancel(): void
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        if ($import = $this->import()) StudentImport::whereKey($import->id)->whereIn('status', ['reviewed', 'importing'])->update(['status' => 'cancelled']);
    }

    public function retryInvitation(int $rowId): void
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        $row = $this->import()?->rows()->whereKey($rowId)->where('email_status', 'failed')->firstOrFail();
        if ($row && $row->account_id) {
            $row->update(['email_status' => 'pending', 'email_error' => null]);
            SendStudentImportInvitation::dispatch($row->id)->onConnection('database');
        }
    }

    public function downloadErrors()
    {
        abort_unless(EmployeeRoleService::canCreateStudent(), 403);
        $import = $this->import();
        abort_unless($import, 404);
        return response()->streamDownload(function () use ($import) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Row', 'Student ID', 'Email', 'Error'], ',', '"', '');
            foreach ($import->rows()->whereIn('status', ['invalid', 'failed'])->cursor() as $row) {
                $safe = fn ($value) => preg_match('/^[=+@\-\t\r]/', (string) $value) ? "'".$value : $value;
                fputcsv($out, [$row->row_number, $safe($row->data['school_id_number'] ?? ''), $safe($row->data['email'] ?? ''), $safe($row->error)], ',', '"', '');
            }
            fclose($out);
        }, 'student-import-errors.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $import = $this->import();
        $rows = $import ? $import->rows()->orderBy('id')->limit(25)->get() : collect();
        $rows->each(fn ($row) => $row->generated_username = UserService::generateDefaultUsername($row->data, 'student'));
        return view('livewire.pages.dashboard.import-students', [
            'import' => $import, 'fields' => StudentImportColumns::FIELDS,
            'counts' => $import ? $import->rows()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status') : collect(),
            'emailCounts' => $import ? $import->rows()->whereNotNull('email_status')->select('email_status', DB::raw('count(*) as total'))->groupBy('email_status')->pluck('total', 'email_status') : collect(),
            'rows' => $rows,
            'failedEmails' => $import ? $import->rows()->where('email_status', 'failed')->limit(25)->get() : collect(),
        ]);
    }
}
