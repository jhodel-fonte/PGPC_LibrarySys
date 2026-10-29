<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Account;
use App\Models\Student;
use App\Models\RegistrationEmailBlocklist;
use App\Mail\AccountApprovalResultEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', [
    'title' => 'User Management',
    'subpage' => 'Account Approvals',
    'activepageRoute' => 'admin.user-management.approvals',
])]
class UserApprovals extends Component
{
    use WithPagination;

    public string $activeTab = 'Pending';
    public string $search = '';
    public int $perPage = 10;
    public array $selectedIds = [];
    public ?array $reviewingApplicant = null;
    #[Validate('nullable|string|max:2000')]
    public string $rejectionRemarks = '';

    /**
     * Keyset pagination cursor cache: maps page number -> last seen account ID from previous page
     * so sequential/visited page queries jump directly via WHERE accounts.id < $lastSeenId.
     *
     * @var array<int, int>
     */
    public array $pageCursors = [];

    public function updatingSearch(): void
    {
        $this->pageCursors = [];
        $this->resetPage();
    }

    public function updatedPerPage(int|string $value): void
    {
        $this->perPage = in_array((int) $value, [5, 10, 25, 50], true) ? (int) $value : 10;
        $this->pageCursors = [];
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['Pending', 'Not Approved', 'Approved', 'All'], true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->selectedIds = [];
        $this->pageCursors = [];
        $this->resetPage();
    }

    public function toggleSelectAll(array $ids): void
    {
        $visibleIds = $this->applySearchFilter(
            $this->applyTabFilter($this->baseStudentsQuery(), $this->activeTab)
        )->whereIn('accounts.id', array_map('intval', $ids))
            ->pluck('accounts.id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $selected = array_map('strval', $this->selectedIds);
        $allSelected = $visibleIds !== [] && count(array_diff($visibleIds, $selected)) === 0;

        $this->selectedIds = $allSelected
            ? array_values(array_diff($selected, $visibleIds))
            : array_values(array_unique([...$selected, ...$visibleIds]));
    }

    public function openReview(int $accountId): void
    {
        $account = $this->baseStudentsQuery()
            ->with(['student', 'status'])
            ->find($accountId);

        if ($account) {
            $mapped = $this->mapAccountToRow($account);
            $this->reviewingApplicant = $mapped;
            $this->rejectionRemarks = $mapped['remarks'] ?? '';
        }
    }

    public function closeReview(): void
    {
        $this->reviewingApplicant = null;
        $this->rejectionRemarks = '';
    }

    public function approveApplicant(int $accountId): void
    {
        $account = $this->baseStudentsQuery()
            ->with('student')
            ->where('accounts.status_id', 4)
            ->whereKey($accountId)
            ->first();

        if (! $account || ! $account->student?->verification_document_path) {
            $this->dispatch('toast', message: 'A valid student account with an uploaded document is required for approval.', type: 'error');
            return;
        }

        DB::transaction(function () use ($account) {
            $account->update([
                'status_id' => 1, // Active
            ]);

            $account->student->update([
                'library_status_id' => 1,
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'verification_remarks' => null,
            ]);
        });
        Mail::to($account->email)->queue(new AccountApprovalResultEmail($account->fresh('student'), 'approved'));

        $this->selectedIds = array_values(array_diff(array_map('strval', $this->selectedIds), [(string) $accountId]));
        $this->pageCursors = [];
        $this->closeReview();
        $this->dispatch('toast', message: 'Student account approved and activated.', type: 'success');
    }

    public function rejectApplicant(int $accountId): void
    {
        $remarks = trim($this->rejectionRemarks) !== ''
            ? trim($this->rejectionRemarks)
            : 'Please upload a clearer verification document.';

        $this->validate();

        $account = $this->baseStudentsQuery()
            ->with('student')
            ->where('accounts.status_id', 4)
            ->whereKey($accountId)
            ->first();

        if (! $account) {
            $this->closeReview();
            return;
        }

        DB::transaction(function () use ($account, $remarks) {
            $account->update([
                'status_id' => 5, // Not Approved
            ]);

            if ($account->student) {
                $account->student->update([
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                    'verification_remarks' => $remarks,
                ]);
            }
        });
        Mail::to($account->email)->queue(new AccountApprovalResultEmail($account->fresh('student'), 'rejected', $remarks));

        $this->selectedIds = array_values(array_diff(array_map('strval', $this->selectedIds), [(string) $accountId]));
        $this->pageCursors = [];
        $this->closeReview();
        $this->dispatch('toast', message: 'Resubmission requested from student.', type: 'info');
    }

    public function blockApplicant(int $accountId): void
    {
        $account = $this->baseStudentsQuery()
            ->with('student')
            ->where('accounts.status_id', 4)
            ->whereKey($accountId)
            ->first();

        if (! $account) {
            $this->closeReview();
            return;
        }

        $days = (int) config('settings.registration.email_block_days', 3);
        $block = DB::transaction(function () use ($account) {
            $account->update(['status_id' => 2]); // Inactive

            if ($account->student) {
                $account->student->update([
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                    'verification_remarks' => 'Registration rejected. This email is temporarily blocked from registration.',
                ]);
            }

            return RegistrationEmailBlocklist::ban($account->email);
        });

        Mail::to($account->email)->queue(new AccountApprovalResultEmail(
            $account->fresh('student'),
            'blocked',
            "Your email is temporarily blocked from registration until {$block->expires_at->format('M d, Y')}.",
        ));

        $this->selectedIds = array_values(array_diff(array_map('strval', $this->selectedIds), [(string) $accountId]));
        $this->pageCursors = [];
        $this->closeReview();
        $this->dispatch('toast', message: "Registration rejected. The email is blocked for {$days} days.", type: 'info');
    }

    public function bulkApprove(): void
    {
        $ids = $this->selectedActionIds(requireDocument: true);
        if (empty($ids)) {
            $this->selectedIds = [];
            return;
        }

        $accounts = $this->baseStudentsQuery()
            ->with('student')
            ->whereIn('accounts.id', $ids)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($ids) {
            Account::whereIn('id', $ids)->update([
                'status_id' => 1, // Active
            ]);

            Student::whereIn('account_id', $ids)->update([
                'library_status_id' => 1,
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'verification_remarks' => null,
            ]);
        });
        foreach ($accounts as $account) {
            Mail::to($account->email)->queue(new AccountApprovalResultEmail($account->fresh('student'), 'approved'));
        }

        $this->selectedIds = [];
        $this->pageCursors = [];
        $this->dispatch('toast', message: count($ids) . ' student account(s) approved and activated.', type: 'success');
    }

    public function bulkReject(): void
    {
        $ids = $this->selectedActionIds();
        if (empty($ids)) {
            $this->selectedIds = [];
            return;
        }

        $accounts = $this->baseStudentsQuery()
            ->with('student')
            ->whereIn('accounts.id', $ids)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($ids) {
            Account::whereIn('id', $ids)->update([
                'status_id' => 5, // Not Approved
            ]);

            Student::whereIn('account_id', $ids)->update([
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'verification_remarks' => 'Please upload a clearer verification document.',
            ]);
        });
        foreach ($accounts as $account) {
            Mail::to($account->email)->queue(new AccountApprovalResultEmail(
                $account->fresh('student'),
                'rejected',
                'Please upload a clearer verification document.',
            ));
        }

        $this->selectedIds = [];
        $this->pageCursors = [];
        $this->dispatch('toast', message: count($ids) . ' student account(s) marked as Not Approved.', type: 'info');
    }

    /**
     * Only pending student accounts may be changed by bulk actions.
     *
     * @return array<int, int>
     */
    protected function selectedActionIds(bool $requireDocument = false): array
    {
        $requestedIds = array_values(array_unique(array_filter(
            array_map('intval', $this->selectedIds),
            fn (int $id): bool => $id > 0
        )));

        if ($requestedIds === []) {
            return [];
        }

        $query = $this->baseStudentsQuery()
            ->where('accounts.status_id', 4)
            ->whereIn('accounts.id', $requestedIds);

        if ($requireDocument) {
            $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery->whereNotNull('students.verification_document_path'));
        }

        return $query
            ->pluck('accounts.id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    protected function baseStudentsQuery(): Builder
    {
        return Account::query()
            ->whereNull('accounts.deleted_at')
            ->whereHas('role', fn (Builder $q) => $q->where('name', 'Student'))
            ->whereHas('student', fn (Builder $q) => $q->whereNull('students.deleted_at'));
    }

    protected function applyTabFilter(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'Pending' => $query->where('accounts.status_id', 4),
            'Not Approved' => $query->where('accounts.status_id', 5),
            'Approved' => $query->where('accounts.status_id', 1),
            default => $query->whereIn('accounts.status_id', [1, 4, 5]),
        };
    }

    protected function applySearchFilter(Builder $query): Builder
    {
        $term = trim($this->search);
        if ($term === '') {
            return $query;
        }

        $like = '%' . strtolower($term) . '%';

        return $query->where(function (Builder $q) use ($like) {
            $q->whereRaw('LOWER(accounts.username) LIKE ?', [$like])
                ->orWhereRaw('LOWER(accounts.email) LIKE ?', [$like])
                ->orWhereHas('student', function (Builder $sq) use ($like) {
                    $sq->whereRaw('LOWER(students.first_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(students.last_name) LIKE ?', [$like])
                        ->orWhereRaw("LOWER(CONCAT(students.first_name, ' ', students.last_name)) LIKE ?", [$like])
                        ->orWhereRaw('LOWER(students.school_id_number) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(students.program) LIKE ?', [$like]);
                });
        });
    }

    /**
     * Resolve the keyset cursor (last seen account ID from page $targetPage - 1)
     * so we can query the next page using WHERE accounts.id < $lastSeenId without scanning past rows.
     */
    protected function resolveCursorForPage(Builder $filteredQuery, int $targetPage): ?int
    {
        if ($targetPage <= 1) {
            return null;
        }

        if (isset($this->pageCursors[$targetPage])) {
            return (int) $this->pageCursors[$targetPage];
        }

        // Find the closest cached cursor before $targetPage so we only advance from the last known ID
        $startPage = 1;
        $cursorId = null;

        for ($p = $targetPage - 1; $p >= 2; $p--) {
            if (isset($this->pageCursors[$p])) {
                $startPage = $p;
                $cursorId = (int) $this->pageCursors[$p];
                break;
            }
        }

        for ($p = $startPage; $p < $targetPage; $p++) {
            $stepQuery = (clone $filteredQuery)->select('accounts.id')->orderByDesc('accounts.id');
            if ($cursorId !== null) {
                $stepQuery->where('accounts.id', '<', $cursorId);
            }

            $ids = $stepQuery->limit($this->perPage)->pluck('accounts.id');
            if ($ids->isEmpty()) {
                break;
            }

            $cursorId = (int) $ids->last();
            $this->pageCursors[$p + 1] = $cursorId;
        }

        return $cursorId;
    }

    protected function mapAccountToRow(Account $account): array
    {
        $student = $account->student;
        $fullName = $student
            ? trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name)
            : $account->username;

        $statusLabel = match ((int) $account->status_id) {
            4 => 'Pending',
            5 => 'Not Approved',
            1 => 'Approved',
            default => $account->status?->status_name ?? 'Pending',
        };

        $docPath = $student?->verification_document_path;
        $docUrl = $student?->verification_document_url;
        $isPdf = $docPath && str_ends_with(strtolower($docPath), '.pdf');

        $submittedAt = $student?->verification_submitted_at
            ? $student->verification_submitted_at->format('M d, Y · g:i A')
            : ($account->created_at ? $account->created_at->format('M d, Y · g:i A') : '—');

        return [
            'id' => $account->id,
            'name' => $fullName,
            'email' => $account->email ?? '—',
            'student_id' => $student?->school_id_number ?? '—',
            'program' => $student?->program ?? '—',
            'year_level' => $student?->year_level ?? '—',
            'contact' => $student?->contact_num ?: '—',
            'document_type' => $docPath ? $student->verification_document_type_label : 'No Document Uploaded',
            'document_file' => $docPath ? basename($docPath) : 'Waiting for student upload',
            'document_url' => $docUrl,
            'has_document' => ! empty($docPath),
            'is_pdf' => $isPdf,
            'submitted_at' => $submittedAt,
            'status' => $statusLabel,
            'remarks' => $student?->verification_remarks ?? '',
        ];
    }

    public function render()
    {
        $base = $this->baseStudentsQuery();

        // Status counts grouped in a single lightweight query
        $rawCounts = (clone $base)
            ->select('accounts.status_id', DB::raw('COUNT(*) as total'))
            ->whereIn('accounts.status_id', [1, 4, 5])
            ->groupBy('accounts.status_id')
            ->pluck('total', 'status_id');

        $pendingCount = (int) ($rawCounts[4] ?? 0);
        $notApprovedCount = (int) ($rawCounts[5] ?? 0);
        $approvedCount = (int) ($rawCounts[1] ?? 0);

        $counts = [
            'Pending' => $pendingCount,
            'Not Approved' => $notApprovedCount,
            'Approved' => $approvedCount,
            'All' => $pendingCount + $notApprovedCount + $approvedCount,
        ];

        $filteredQuery = $this->applySearchFilter(
            $this->applyTabFilter(clone $base, $this->activeTab)
        );

        $totalMatching = (clone $filteredQuery)->count();
        $lastPage = max(1, (int) ceil($totalMatching / $this->perPage));
        $currentPage = max(1, min((int) $this->getPage(), $lastPage));

        // Keyset (Seek) Pagination: jump directly after the last seen ID on the previous page
        $cursorId = $this->resolveCursorForPage($filteredQuery, $currentPage);

        $pageQuery = (clone $filteredQuery)
            ->with(['student', 'status'])
            ->orderByDesc('accounts.id');

        if ($cursorId !== null) {
            $pageQuery->where('accounts.id', '<', $cursorId);
        }

        $accounts = $pageQuery->limit($this->perPage)->get();

        if ($accounts->isNotEmpty()) {
            $this->pageCursors[$currentPage + 1] = (int) $accounts->last()->id;
        }

        $mappedRows = $accounts->map(fn (Account $acc) => $this->mapAccountToRow($acc))->values();

        $paginator = new LengthAwarePaginator(
            $mappedRows,
            $totalMatching,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('livewire.pages.dashboard.user-approvals', [
            'filteredApplicants' => $mappedRows,
            'paginator' => $paginator,
            'counts' => $counts,
        ]);
    }
}
