<?php

namespace App\Livewire\Components\Usermanager;

use App\Models\Account;
use App\Models\AccountStatus;
use App\Services\EmployeeRoleService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class StudentTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'ALL'; // 'ALL', 'Active', 'Inactive', 'Suspended', 'Deleted' (Superadmin only)
    public int $perPage = 10;

    // Multi-Selection (Bulk Actions)
    public array $selectedStudents = [];
    public bool $selectAll = false;

    // Applied filters
    public string $filterStatus = '';
    public string $filterProgram = '';
    public string $filterYearLevel = '';

    // Staged temporary filters inside dropdown
    public string $tempFilterStatus = '';
    public string $tempFilterProgram = '';
    public string $tempFilterYearLevel = '';

    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    // Single delete modal
    public bool $showDeleteModal = false;
    public ?int $selectedStudentAccountId = null;
    public string $selectedStudentName = '';
    public string $selectedStudentSchoolId = '';
    public int $selectedStudentActiveLoans = 0;

    public function getHeadersProperty(): array
    {
        return [
            ['index' => 'student', 'label' => 'Student', 'sortable' => true],
            ['index' => 'id_number', 'label' => 'Student ID', 'sortable' => true],
            ['index' => 'program', 'label' => 'Program & Year', 'sortable' => true],
            ['index' => 'status', 'label' => 'Account Status', 'sortable' => true],
            ['index' => 'last_login', 'label' => 'Last Login', 'sortable' => true],
            ['index' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ];
    }

    public function sortBy(string $column): void
    {
        if ($this->sort['column'] === $column) {
            $this->sort['direction'] = $this->sort['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort['column'] = $column;
            $this->sort['direction'] = 'asc';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function setTab(string $tab): void
    {
        if ($tab === 'Deleted' && !EmployeeRoleService::isAdmin()) {
            $tab = 'ALL';
        }

        $this->activeTab = $tab;
        $this->resetPage();
        $this->clearSelection();
    }

    // Selection Handling
    public function updatedSelectAll($value): void
    {
        if ($value) {
            $currentPageIds = $this->getCurrentPageStudentIds();
            $this->selectedStudents = array_map('strval', $currentPageIds);
        } else {
            $this->selectedStudents = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function applyFilters(): void
    {
        $this->filterStatus = $this->tempFilterStatus;
        $this->filterProgram = $this->tempFilterProgram;
        $this->filterYearLevel = $this->tempFilterYearLevel;
        $this->resetPage();
        $this->clearSelection();
    }

    public function clearFilters(): void
    {
        $this->tempFilterStatus = '';
        $this->tempFilterProgram = '';
        $this->tempFilterYearLevel = '';
        $this->filterStatus = '';
        $this->filterProgram = '';
        $this->filterYearLevel = '';
        $this->resetPage();
        $this->clearSelection();
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if (!empty($this->filterStatus)) $count++;
        if (!empty($this->filterProgram)) $count++;
        if (!empty($this->filterYearLevel)) $count++;
        return $count;
    }

    public function confirmDelete(int $accountId): void
    {
        $account = Account::with('student.borrowingTransactions')->find($accountId);
        if (!$account) {
            return;
        }

        $student = $account->student;
        $this->selectedStudentAccountId = $account->id;
        $this->selectedStudentName = $student ? trim("{$student->first_name} {$student->last_name}") : $account->username;
        $this->selectedStudentSchoolId = $student?->school_id_number ?? '—';
        $this->selectedStudentActiveLoans = $student
            ? $student->borrowingTransactions()->whereNull('return_date')->count()
            : 0;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedStudentAccountId = null;
        $this->selectedStudentName = '';
        $this->selectedStudentSchoolId = '';
        $this->selectedStudentActiveLoans = 0;
    }

    public function deleteStudent(?int $accountId = null): void
    {
        $targetId = $accountId ?? $this->selectedStudentAccountId;
        if (!$targetId) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canDeleteStudent(auth()->user())) {
            abort(403, 'Unauthorized action. You do not have permission to delete student accounts.');
        }

        $user = Account::with('student.borrowingTransactions')->find($targetId);
        if (!$user) {
            $this->dispatch('toast', message: 'Student account not found.', type: 'error');
            $this->closeDeleteModal();
            return;
        }

        if (auth()->check() && auth()->id() === $user->id) {
            $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
            $this->closeDeleteModal();
            return;
        }

        $student = $user->student;
        $activeLoans = $student ? $student->borrowingTransactions()->whereNull('return_date')->count() : 0;
        if ($activeLoans > 0) {
            $this->dispatch('toast', message: 'Cannot delete student with active borrowed books. Please return all borrowed items first.', type: 'error');
            $this->closeDeleteModal();
            return;
        }

        $name = $student ? trim("{$student->first_name} {$student->last_name}") : $user->username;

        $inactiveStatus = AccountStatus::where('status_name', 'Inactive')->first();
        if ($inactiveStatus) {
            $user->update(['status_id' => $inactiveStatus->id]);
        }

        if ($student) {
            $student->delete();
        }
        $user->delete();

        $this->closeDeleteModal();
        $this->clearSelection();

        $this->dispatch('toast', [
            'message' => "Student account for {$name} has been deleted.",
            'type' => 'warning',
            'undoEvent' => 'restore-student-account',
            'undoPayload' => ['id' => $user->id],
            'duration' => 10000,
        ]);
    }

    #[On('restore-student-account')]
    public function restoreStudent($id = null): void
    {
        $targetId = is_array($id) ? ($id['id'] ?? $id['userId'] ?? null) : $id;
        if (!$targetId) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canEditStudent(auth()->user()) && !EmployeeRoleService::canDeleteStudent(auth()->user())) {
            $this->dispatch('toast', message: 'Unauthorized action to restore student account.', type: 'error');
            return;
        }

        $user = Account::withTrashed()->with(['student' => fn ($q) => $q->withTrashed()])->find($targetId);
        if (!$user) {
            return;
        }

        $user->restore();
        if ($user->student && $user->student->trashed()) {
            $user->student->restore();
        }

        $activeStatus = AccountStatus::where('status_name', 'Active')->first();
        if ($activeStatus) {
            $user->update(['status_id' => $activeStatus->id]);
        }

        $name = $user->student ? trim("{$user->student->first_name} {$user->student->last_name}") : $user->username;
        $this->dispatch('toast', message: "Student account for {$name} was restored successfully.", type: 'success');
        $this->clearSelection();
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedStudents)) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canDeleteStudent(auth()->user())) {
            abort(403, 'Unauthorized action. You do not have permission to delete student accounts.');
        }

        $accounts = Account::with('student.borrowingTransactions')
            ->whereIn('id', $this->selectedStudents)
            ->get();

        $inactiveStatus = AccountStatus::where('status_name', 'Inactive')->first();
        $deletedIds = [];
        $deletedCount = 0;

        foreach ($accounts as $account) {
            if (auth()->check() && auth()->id() === $account->id) {
                continue;
            }

            $student = $account->student;
            $hasActiveLoans = $student && $student->borrowingTransactions()->whereNull('return_date')->exists();
            if ($hasActiveLoans) {
                continue;
            }

            if ($inactiveStatus) {
                $account->update(['status_id' => $inactiveStatus->id]);
            }

            if ($student) {
                $student->delete();
            }
            $account->delete();

            $deletedIds[] = $account->id;
            $deletedCount++;
        }

        $skipped = count($this->selectedStudents) - $deletedCount;
        if ($deletedCount > 0) {
            $msg = "Deleted {$deletedCount} student account(s).";
            if ($skipped > 0) {
                $msg .= " ({$skipped} student(s) with active loans were skipped)";
            }

            $this->dispatch('toast', [
                'message' => $msg,
                'type' => 'warning',
                'undoEvent' => 'restore-student-accounts',
                'undoPayload' => ['ids' => $deletedIds],
                'duration' => 10000,
            ]);
        } else {
            $this->dispatch('toast', message: 'No selected students could be deleted (they may have active borrowed books).', type: 'error');
        }

        $this->clearSelection();
    }

    public function bulkRestore(): void
    {
        if (empty($this->selectedStudents)) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canEditStudent(auth()->user()) && !EmployeeRoleService::canDeleteStudent(auth()->user())) {
            $this->dispatch('toast', message: 'Unauthorized action to restore student accounts.', type: 'error');
            return;
        }

        $this->restoreStudentAccounts($this->selectedStudents);
        $this->clearSelection();
    }

    #[On('restore-student-accounts')]
    public function restoreStudentAccounts($ids = null): void
    {
        $targetIds = is_array($ids) && isset($ids['ids']) ? $ids['ids'] : (array) $ids;
        if (empty($targetIds)) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canEditStudent(auth()->user()) && !EmployeeRoleService::canDeleteStudent(auth()->user())) {
            $this->dispatch('toast', message: 'Unauthorized action to restore student accounts.', type: 'error');
            return;
        }

        $accounts = Account::withTrashed()
            ->with(['student' => fn ($q) => $q->withTrashed()])
            ->whereIn('id', $targetIds)
            ->get();

        $activeStatus = AccountStatus::where('status_name', 'Active')->first();
        $restoredCount = 0;

        foreach ($accounts as $account) {
            if ($account->trashed() || ($account->student && $account->student->trashed())) {
                $account->restore();
                if ($account->student && $account->student->trashed()) {
                    $account->student->restore();
                }
                if ($activeStatus) {
                    $account->update(['status_id' => $activeStatus->id]);
                }
                $restoredCount++;
            }
        }

        if ($restoredCount > 0) {
            $this->dispatch('toast', message: "Restored {$restoredCount} student account(s) successfully.", type: 'success');
        }
    }

    protected function getCurrentPageStudentIds(): array
    {
        return $this->buildQuery()->paginate($this->perPage)->pluck('id')->toArray();
    }

    protected function buildQuery()
    {
        $isSuperAdmin = EmployeeRoleService::isAdmin();
        $isDeletedTab = $isSuperAdmin && $this->activeTab === 'Deleted';

        if (!$isSuperAdmin && $this->activeTab === 'Deleted') {
            $this->activeTab = 'ALL';
        }

        if ($isDeletedTab) {
            $query = Account::withTrashed()
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Student', 'Member']))
                ->where(function ($q) {
                    $q->whereNotNull('accounts.deleted_at')
                      ->orWhereHas('student', fn ($sq) => $sq->onlyTrashed());
                })
                ->with([
                    'role',
                    'status',
                    'student' => fn ($q) => $q->withTrashed(),
                ]);
        } else {
            // Strictly exclude soft-deleted accounts and soft-deleted student profiles
            $query = Account::query()
                ->whereNull('accounts.deleted_at')
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Student', 'Member']))
                ->whereHas('student', fn ($sq) => $sq->whereNull('deleted_at'))
                ->with([
                    'role',
                    'status',
                    'student',
                ]);
        }

        // 1. Tab quick filter (for non-Deleted tabs)
        if (!$isDeletedTab) {
            if ($this->activeTab === 'Active') {
                $query->whereHas('status', fn ($q) => $q->where('status_name', 'Active'));
            } elseif ($this->activeTab === 'Inactive') {
                $query->whereHas('status', fn ($q) => $q->where('status_name', 'Inactive'));
            } elseif ($this->activeTab === 'Suspended') {
                $query->whereHas('status', fn ($q) => $q->whereIn('status_name', ['Locked', 'Suspended']));
            }
        }

        // 2. Dropdown filters
        if (!empty($this->filterStatus)) {
            $query->whereHas('status', fn ($q) => $q->where('status_name', $this->filterStatus));
        }

        if (!empty($this->filterProgram)) {
            $query->whereHas('student', fn ($q) => ($isDeletedTab ? $q->withTrashed() : $q)->where('program', $this->filterProgram));
        }

        if (!empty($this->filterYearLevel)) {
            $query->whereHas('student', fn ($q) => ($isDeletedTab ? $q->withTrashed() : $q)->where('year_level', $this->filterYearLevel));
        }

        // 3. Search filter
        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search, $isDeletedTab) {
                $q->where('accounts.username', 'like', "%{$search}%")
                  ->orWhere('accounts.email', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search, $isDeletedTab) {
                      if ($isDeletedTab) {
                          $sq->withTrashed();
                      }
                      $sq->where(function ($sub) use ($search) {
                          $sub->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('school_id_number', 'like', "%{$search}%")
                              ->orWhere('program', 'like', "%{$search}%");
                      });
                  });
            });
        }

        // 4. Sorting
        $sortColumn = $this->sort['column'];
        $sortDirection = $this->sort['direction'];

        if ($sortColumn === 'student') {
            $query->leftJoin('students', 'accounts.id', '=', 'students.account_id')
                  ->select('accounts.*')
                  ->orderBy('students.last_name', $sortDirection)
                  ->orderBy('students.first_name', $sortDirection);
        } elseif ($sortColumn === 'id_number') {
            $query->leftJoin('students', 'accounts.id', '=', 'students.account_id')
                  ->select('accounts.*')
                  ->orderBy('students.school_id_number', $sortDirection);
        } elseif ($sortColumn === 'program') {
            $query->leftJoin('students', 'accounts.id', '=', 'students.account_id')
                  ->select('accounts.*')
                  ->orderBy('students.program', $sortDirection);
        } elseif ($sortColumn === 'status') {
            $query->leftJoin('account_statuses', 'accounts.status_id', '=', 'account_statuses.id')
                  ->select('accounts.*')
                  ->orderBy('account_statuses.status_name', $sortDirection);
        } else {
            $query->orderBy("accounts.{$sortColumn}", $sortDirection);
        }

        return $query;
    }

    public function render()
    {
        $students = $this->buildQuery()->paginate($this->perPage);

        // Calculate count metrics for tabs (strictly excluding soft-deleted accounts from non-Deleted counts)
        $baseStudentQuery = Account::query()
            ->whereNull('accounts.deleted_at')
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['Student', 'Member']))
            ->whereHas('student', fn ($sq) => $sq->whereNull('deleted_at'));

        $totalStudents = (clone $baseStudentQuery)->count();
        $activeStudents = (clone $baseStudentQuery)->whereHas('status', fn ($q) => $q->where('status_name', 'Active'))->count();
        $inactiveStudents = (clone $baseStudentQuery)->whereHas('status', fn ($q) => $q->where('status_name', 'Inactive'))->count();
        $lockedStudents = (clone $baseStudentQuery)->whereHas('status', fn ($q) => $q->whereIn('status_name', ['Locked', 'Suspended']))->count();

        $deletedStudents = EmployeeRoleService::isAdmin()
            ? Account::withTrashed()
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Student', 'Member']))
                ->where(function ($q) {
                    $q->whereNotNull('accounts.deleted_at')
                      ->orWhereHas('student', fn ($sq) => $sq->onlyTrashed());
                })
                ->count()
            : 0;

        $programs = config('settings.college.programs', []);
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        return view('livewire.components.usermanager.student-table', [
            'students' => $students,
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
            'lockedStudents' => $lockedStudents,
            'deletedStudents' => $deletedStudents,
            'programs' => $programs,
            'yearLevels' => $yearLevels,
        ]);
    }
}
