<?php

namespace App\Livewire\Components\Usermanager;

use App\Models\Account;
use App\Models\AccountStatus;
use App\Services\EmployeeRoleService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LibrarianTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'ALL'; // 'ALL', 'Head Librarian', 'Librarian', 'Deleted' (Superadmin only)
    public int $perPage = 10;

    // Multi-Selection (Bulk Actions)
    public array $selectedLibrarians = [];
    public bool $selectAll = false;

    // Applied filters
    public string $filterStatus = '';
    public string $filterRole = '';

    // Staged temporary filters inside dropdown
    public string $tempFilterStatus = '';
    public string $tempFilterRole = '';

    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    // Modals for disable and soft-delete
    public bool $showDisableModal = false;
    public bool $showDeleteModal = false;
    public ?int $selectedLibrarianId = null;
    public string $selectedLibrarianName = '';
    public bool $selectedLibrarianHasTransactions = false;

    public function getHeadersProperty(): array
    {
        return [
            ['index' => 'staff', 'label' => 'Staff Member', 'sortable' => true],
            ['index' => 'id_number', 'label' => 'Employee ID', 'sortable' => true],
            ['index' => 'role', 'label' => 'Role', 'sortable' => true],
            ['index' => 'contact', 'label' => 'Contact Number', 'sortable' => false],
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
            $currentPageIds = $this->getCurrentPageLibrarianIds();
            $this->selectedLibrarians = array_map('strval', $currentPageIds);
        } else {
            $this->selectedLibrarians = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selectedLibrarians = [];
        $this->selectAll = false;
    }

    public function applyFilters(): void
    {
        $this->filterStatus = $this->tempFilterStatus;
        $this->filterRole = $this->tempFilterRole;
        $this->resetPage();
        $this->clearSelection();
    }

    public function clearFilters(): void
    {
        $this->tempFilterStatus = '';
        $this->tempFilterRole = '';
        $this->filterStatus = '';
        $this->filterRole = '';
        $this->resetPage();
        $this->clearSelection();
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if (!empty($this->filterStatus)) $count++;
        if (!empty($this->filterRole)) $count++;
        return $count;
    }

    public function confirmDisable(int $id): void
    {
        $account = Account::with('librarian')->find($id);
        if (!$account) return;

        $this->selectedLibrarianId = $id;
        $this->selectedLibrarianName = $account->librarian ? trim("{$account->librarian->first_name} {$account->librarian->last_name}") : $account->username;
        $this->showDisableModal = true;
    }

    public function closeDisableModal(): void
    {
        $this->showDisableModal = false;
        $this->selectedLibrarianId = null;
        $this->selectedLibrarianName = '';
    }

    public function disableLibrarian(): void
    {
        if (!$this->selectedLibrarianId) return;

        $user = Account::with('librarian')->find($this->selectedLibrarianId);
        if (!$user) {
            $this->dispatch('toast', message: 'User account not found.', type: 'error');
            $this->closeDisableModal();
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if ($authUser->id === $user->id) {
                $this->dispatch('toast', message: 'You cannot disable your own account.', type: 'error');
                $this->closeDisableModal();
                return;
            }

            if (!EmployeeRoleService::canManageLibrarians($authUser)) {
                abort(403, 'Unauthorized action. You do not have permission to disable librarian accounts.');
            }
        }

        if ($user->librarian) {
            $user->librarian->delete();
        }

        $inactiveStatus = AccountStatus::where('status_name', 'Inactive')->first();
        if ($inactiveStatus) {
            $user->update(['status_id' => $inactiveStatus->id]);
        }

        $user->delete();

        $name = $this->selectedLibrarianName;
        $userId = $user->id;
        $this->closeDisableModal();
        $this->clearSelection();
        $this->dispatch('toast', [
            'message' => "Librarian account for {$name} has been disabled (soft-deleted).",
            'type' => 'success',
            'undoEvent' => 'enable-librarian-account',
            'undoPayload' => ['id' => $userId],
            'duration' => 10000,
        ]);
    }

    #[On('enable-librarian-account')]
    #[On('restore-librarian-account')]
    public function enableLibrarian($id): void
    {
        $targetId = is_array($id) ? ($id['id'] ?? $id['userId'] ?? null) : $id;
        if (!$targetId) return;

        $user = Account::withTrashed()->with(['librarian' => fn($q) => $q->withTrashed()])->find($targetId);
        if (!$user) {
            $this->dispatch('toast', message: 'User account not found.', type: 'error');
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if (!EmployeeRoleService::canManageLibrarians($authUser)) {
                abort(403, 'Unauthorized action. You do not have permission to enable librarian accounts.');
            }
        }

        $user->restore();
        if ($user->librarian && $user->librarian->trashed()) {
            $user->librarian->restore();
        }

        $activeStatus = AccountStatus::where('status_name', 'Active')->first();
        if ($activeStatus) {
            $user->update(['status_id' => $activeStatus->id]);
        }

        $name = $user->librarian ? trim("{$user->librarian->first_name} {$user->librarian->last_name}") : $user->username;
        $this->dispatch('toast', message: "Librarian account for {$name} has been restored and set to Active.", type: 'success');
        $this->clearSelection();
    }

    public function confirmDelete(int $id): void
    {
        $account = Account::with('librarian')->find($id);
        if (!$account) return;

        $this->selectedLibrarianId = $id;
        $this->selectedLibrarianName = $account->librarian ? trim("{$account->librarian->first_name} {$account->librarian->last_name}") : $account->username;
        $this->selectedLibrarianHasTransactions = false;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedLibrarianId = null;
        $this->selectedLibrarianName = '';
        $this->selectedLibrarianHasTransactions = false;
    }

    /**
     * Soft-delete the librarian account and profile (replaces permanent hard-delete).
     */
    public function deleteLibrarian(): void
    {
        if (!$this->selectedLibrarianId) return;

        $user = Account::with('librarian')->find($this->selectedLibrarianId);
        if (!$user) {
            $this->dispatch('toast', message: 'User account not found.', type: 'error');
            $this->closeDeleteModal();
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if ($authUser->id === $user->id) {
                $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
                $this->closeDeleteModal();
                return;
            }

            if (!EmployeeRoleService::canManageLibrarians($authUser)) {
                abort(403, 'Unauthorized action. You do not have permission to delete librarian accounts.');
            }
        }

        $name = $this->selectedLibrarianName ?: ($user->librarian ? trim("{$user->librarian->first_name} {$user->librarian->last_name}") : $user->username);
        $userId = $user->id;

        $inactiveStatus = AccountStatus::where('status_name', 'Inactive')->first();
        if ($inactiveStatus) {
            $user->update(['status_id' => $inactiveStatus->id]);
        }

        if ($user->librarian) {
            $user->librarian->delete();
        }
        $user->delete();

        $this->closeDeleteModal();
        $this->clearSelection();
        $this->dispatch('toast', [
            'message' => "Librarian account for {$name} has been deleted.",
            'type' => 'warning',
            'undoEvent' => 'enable-librarian-account',
            'undoPayload' => ['id' => $userId],
            'duration' => 10000,
        ]);
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedLibrarians)) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canManageLibrarians(auth()->user())) {
            abort(403, 'Unauthorized action. You do not have permission to delete librarian accounts.');
        }

        $accounts = Account::with('librarian')
            ->whereIn('id', $this->selectedLibrarians)
            ->get();

        $inactiveStatus = AccountStatus::where('status_name', 'Inactive')->first();
        $deletedIds = [];
        $deletedCount = 0;

        foreach ($accounts as $account) {
            if (auth()->check() && auth()->id() === $account->id) {
                continue;
            }

            if ($inactiveStatus) {
                $account->update(['status_id' => $inactiveStatus->id]);
            }

            if ($account->librarian) {
                $account->librarian->delete();
            }
            $account->delete();

            $deletedIds[] = $account->id;
            $deletedCount++;
        }

        $skipped = count($this->selectedLibrarians) - $deletedCount;
        if ($deletedCount > 0) {
            $msg = "Deleted {$deletedCount} librarian account(s).";
            if ($skipped > 0) {
                $msg .= " ({$skipped} skipped)";
            }

            $this->dispatch('toast', [
                'message' => $msg,
                'type' => 'warning',
                'undoEvent' => 'restore-librarian-accounts',
                'undoPayload' => ['ids' => $deletedIds],
                'duration' => 10000,
            ]);
        } else {
            $this->dispatch('toast', message: 'No selected librarian accounts could be deleted.', type: 'error');
        }

        $this->clearSelection();
    }

    public function bulkRestore(): void
    {
        if (empty($this->selectedLibrarians)) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canManageLibrarians(auth()->user())) {
            $this->dispatch('toast', message: 'Unauthorized action to restore librarian accounts.', type: 'error');
            return;
        }

        $this->restoreLibrarianAccounts($this->selectedLibrarians);
        $this->clearSelection();
    }

    #[On('restore-librarian-accounts')]
    public function restoreLibrarianAccounts($ids = null): void
    {
        $targetIds = is_array($ids) && isset($ids['ids']) ? $ids['ids'] : (array) $ids;
        if (empty($targetIds)) {
            return;
        }

        if (auth()->check() && !EmployeeRoleService::canManageLibrarians(auth()->user())) {
            $this->dispatch('toast', message: 'Unauthorized action to restore librarian accounts.', type: 'error');
            return;
        }

        $accounts = Account::withTrashed()
            ->with(['librarian' => fn ($q) => $q->withTrashed()])
            ->whereIn('id', $targetIds)
            ->get();

        $activeStatus = AccountStatus::where('status_name', 'Active')->first();
        $restoredCount = 0;

        foreach ($accounts as $account) {
            if ($account->trashed() || ($account->librarian && $account->librarian->trashed())) {
                $account->restore();
                if ($account->librarian && $account->librarian->trashed()) {
                    $account->librarian->restore();
                }
                if ($activeStatus) {
                    $account->update(['status_id' => $activeStatus->id]);
                }
                $restoredCount++;
            }
        }

        if ($restoredCount > 0) {
            $this->dispatch('toast', message: "Restored {$restoredCount} librarian account(s) successfully.", type: 'success');
        }
    }

    protected function getCurrentPageLibrarianIds(): array
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
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Librarian', 'Head Librarian']))
                ->where(function ($q) {
                    $q->whereNotNull('accounts.deleted_at')
                      ->orWhereHas('librarian', fn ($lq) => $lq->onlyTrashed());
                })
                ->with([
                    'role',
                    'status',
                    'librarian' => fn ($q) => $q->withTrashed(),
                ]);
        } else {
            // Strictly exclude soft-deleted accounts and soft-deleted librarian profiles
            $query = Account::query()
                ->whereNull('accounts.deleted_at')
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Librarian', 'Head Librarian']))
                ->whereHas('librarian', fn ($lq) => $lq->whereNull('deleted_at'))
                ->with([
                    'role',
                    'status',
                    'librarian',
                ]);
        }

        // 1. Tab quick filter (for non-Deleted tabs)
        if (!$isDeletedTab) {
            if ($this->activeTab === 'Head Librarian') {
                $query->whereHas('role', fn ($q) => $q->where('name', 'Head Librarian'));
            } elseif ($this->activeTab === 'Librarian') {
                $query->whereHas('role', fn ($q) => $q->where('name', 'Librarian'));
            }
        }

        // 2. Dropdown filters
        if (!empty($this->filterStatus)) {
            $query->whereHas('status', fn ($q) => $q->where('status_name', $this->filterStatus));
        }

        if (!empty($this->filterRole)) {
            $query->whereHas('role', fn ($q) => $q->where('name', $this->filterRole));
        }

        // 3. Search filter
        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search, $isDeletedTab) {
                $q->where('accounts.username', 'like', "%{$search}%")
                  ->orWhere('accounts.email', 'like', "%{$search}%")
                  ->orWhereHas('librarian', function ($lq) use ($search, $isDeletedTab) {
                      if ($isDeletedTab) {
                          $lq->withTrashed();
                      }
                      $lq->where(function ($sub) use ($search) {
                          $sub->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('school_id_number', 'like', "%{$search}%")
                              ->orWhere('contact_num', 'like', "%{$search}%");
                      });
                  });
            });
        }

        // 4. Sorting
        $sortColumn = $this->sort['column'];
        $sortDirection = $this->sort['direction'];

        if ($sortColumn === 'staff') {
            $query->leftJoin('librarians', 'accounts.id', '=', 'librarians.account_id')
                  ->select('accounts.*')
                  ->orderBy('librarians.last_name', $sortDirection)
                  ->orderBy('librarians.first_name', $sortDirection);
        } elseif ($sortColumn === 'id_number') {
            $query->leftJoin('librarians', 'accounts.id', '=', 'librarians.account_id')
                  ->select('accounts.*')
                  ->orderBy('librarians.school_id_number', $sortDirection);
        } elseif ($sortColumn === 'role') {
            $query->leftJoin('roles', 'accounts.role_id', '=', 'roles.id')
                  ->select('accounts.*')
                  ->orderBy('roles.name', $sortDirection);
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
        $librarians = $this->buildQuery()->paginate($this->perPage);

        // Metrics for tabs (strictly excluding soft-deleted accounts from non-Deleted counts)
        $baseLibrarianQuery = Account::query()
            ->whereNull('accounts.deleted_at')
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['Librarian', 'Head Librarian']))
            ->whereHas('librarian', fn ($lq) => $lq->whereNull('deleted_at'));

        $totalLibrarians = (clone $baseLibrarianQuery)->count();
        $headLibrarians = (clone $baseLibrarianQuery)->whereHas('role', fn ($q) => $q->where('name', 'Head Librarian'))->count();
        $regularLibrarians = (clone $baseLibrarianQuery)->whereHas('role', fn ($q) => $q->where('name', 'Librarian'))->count();

        $deletedLibrarians = EmployeeRoleService::isAdmin()
            ? Account::withTrashed()
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Librarian', 'Head Librarian']))
                ->where(function ($q) {
                    $q->whereNotNull('accounts.deleted_at')
                      ->orWhereHas('librarian', fn ($lq) => $lq->onlyTrashed());
                })
                ->count()
            : 0;

        return view('livewire.components.usermanager.librarian-table', [
            'librarians' => $librarians,
            'totalLibrarians' => $totalLibrarians,
            'headLibrarians' => $headLibrarians,
            'regularLibrarians' => $regularLibrarians,
            'deletedLibrarians' => $deletedLibrarians,
        ]);
    }
}
