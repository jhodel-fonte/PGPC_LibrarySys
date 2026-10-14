<?php

namespace App\Livewire\Components\Usermanager;

use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class LibrarianTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'ALL'; // 'ALL', 'Head Librarian', 'Librarian'
    
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

    // Modals for disable and delete
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
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->filterStatus = $this->tempFilterStatus;
        $this->filterRole = $this->tempFilterRole;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->tempFilterStatus = '';
        $this->tempFilterRole = '';
        $this->filterStatus = '';
        $this->filterRole = '';
        $this->resetPage();
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
        $account = Account::withTrashed()->with('librarian')->find($id);
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

        $user = Account::withTrashed()->with('librarian')->find($this->selectedLibrarianId);
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

            if (!$authUser->hasRole('Admin') && !$authUser->hasPermission('manage_librarian') && !$authUser->hasPermission('manage_librarians') && !$authUser->hasPermission('manage_users')) {
                abort(403, 'Unauthorized action. You do not have permission to disable librarian accounts.');
            }
        }

        if ($user->librarian) {
            $user->librarian->delete();
        }

        $inactiveStatus = \App\Models\AccountStatus::where('status_name', 'Inactive')->first();
        if ($inactiveStatus) {
            $user->update(['status_id' => $inactiveStatus->id]);
        }

        $user->delete();

        $name = $this->selectedLibrarianName;
        $this->closeDisableModal();
        $this->dispatch('toast', message: "Librarian account for {$name} has been disabled (soft-deleted).", type: 'success');
    }

    public function enableLibrarian(int $id): void
    {
        $user = Account::withTrashed()->with(['librarian' => fn($q) => $q->withTrashed()])->find($id);
        if (!$user) {
            $this->dispatch('toast', message: 'User account not found.', type: 'error');
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if (!$authUser->hasRole('Admin') && !$authUser->hasPermission('manage_librarian') && !$authUser->hasPermission('manage_librarians') && !$authUser->hasPermission('manage_users')) {
                abort(403, 'Unauthorized action. You do not have permission to enable librarian accounts.');
            }
        }

        $user->restore();
        if ($user->librarian) {
            $user->librarian->restore();
        }

        $activeStatus = \App\Models\AccountStatus::where('status_name', 'Active')->first();
        if ($activeStatus) {
            $user->update(['status_id' => $activeStatus->id]);
        }

        $name = $user->librarian ? trim("{$user->librarian->first_name} {$user->librarian->last_name}") : $user->username;
        $this->dispatch('toast', message: "Librarian account for {$name} has been successfully enabled and set to Active.", type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $account = Account::withTrashed()->with([
            'librarian' => fn($q) => $q->withTrashed()->with(['issuedTransactions', 'receivedTransactions', 'receivedPayments'])
        ])->find($id);

        if (!$account) return;

        $this->selectedLibrarianId = $id;
        $this->selectedLibrarianName = $account->librarian ? trim("{$account->librarian->first_name} {$account->librarian->last_name}") : $account->username;
        $librarian = $account->librarian;
        $this->selectedLibrarianHasTransactions = $librarian && (
            $librarian->issuedTransactions()->exists() ||
            $librarian->receivedTransactions()->exists() ||
            $librarian->receivedPayments()->exists()
        );
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedLibrarianId = null;
        $this->selectedLibrarianName = '';
        $this->selectedLibrarianHasTransactions = false;
    }

    public function hardDeleteLibrarian(): void
    {
        if (!$this->selectedLibrarianId) return;

        $user = Account::withTrashed()->with([
            'librarian' => fn($q) => $q->withTrashed()->with(['issuedTransactions', 'receivedTransactions', 'receivedPayments'])
        ])->find($this->selectedLibrarianId);

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

            if (!$authUser->hasRole('Admin') && !$authUser->hasPermission('manage_librarian') && !$authUser->hasPermission('manage_librarians') && !$authUser->hasPermission('manage_users')) {
                abort(403, 'Unauthorized action. You do not have permission to delete librarian accounts.');
            }
        }

        $librarian = $user->librarian;
        $hasTransactions = $librarian && (
            $librarian->issuedTransactions()->exists() ||
            $librarian->receivedTransactions()->exists() ||
            $librarian->receivedPayments()->exists()
        );

        if ($hasTransactions) {
            $this->closeDeleteModal();
            $this->dispatch('toast', message: 'Cannot permanently delete this librarian account because they are linked to existing circulation transactions and audit records. Use "Disable Account" instead to deactivate access while preserving library history.', type: 'error');
            return;
        }

        $name = $this->selectedLibrarianName;

        $user->permissions()->detach();

        if ($librarian && !empty($librarian->getRawOriginal('image_url'))) {
            $rawImg = $librarian->getRawOriginal('image_url');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($rawImg)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($rawImg);
            }
        }

        if ($librarian) {
            $librarian->forceDelete();
        }
        $user->forceDelete();

        $this->closeDeleteModal();
        $this->dispatch('toast', message: "Librarian account for {$name} has been permanently deleted from the database.", type: 'success');
    }

    public function render()
    {
        $query = Account::withTrashed()->whereHas('role', function ($q) {
            $q->whereIn('name', ['Librarian', 'Head Librarian']);
        })->with([
            'role',
            'status',
            'librarian' => fn($q) => $q->withTrashed(),
        ]);

        // 1. Tab quick filter
        if ($this->activeTab === 'Head Librarian') {
            $query->whereHas('role', fn ($q) => $q->where('name', 'Head Librarian'));
        } elseif ($this->activeTab === 'Librarian') {
            $query->whereHas('role', fn ($q) => $q->where('name', 'Librarian'));
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
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('librarian', function ($lq) use ($search) {
                      $lq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('school_id_number', 'like', "%{$search}%")
                         ->orWhere('contact_num', 'like', "%{$search}%");
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
            $query->orderBy($sortColumn, $sortDirection);
        }

        $librarians = $query->paginate(10);

        // Metrics for tabs
        $baseLibrarianQuery = Account::withTrashed()->whereHas('role', fn ($q) => $q->whereIn('name', ['Librarian', 'Head Librarian']));
        $totalLibrarians = (clone $baseLibrarianQuery)->count();
        $headLibrarians = (clone $baseLibrarianQuery)->whereHas('role', fn ($q) => $q->where('name', 'Head Librarian'))->count();
        $regularLibrarians = (clone $baseLibrarianQuery)->whereHas('role', fn ($q) => $q->where('name', 'Librarian'))->count();

        return view('livewire.components.usermanager.librarian-table', [
            'librarians' => $librarians,
            'totalLibrarians' => $totalLibrarians,
            'headLibrarians' => $headLibrarians,
            'regularLibrarians' => $regularLibrarians,
        ]);
    }
}
