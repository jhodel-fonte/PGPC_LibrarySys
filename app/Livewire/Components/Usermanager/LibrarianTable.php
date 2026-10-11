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

    public function render()
    {
        $query = Account::whereHas('role', function ($q) {
            $q->whereIn('name', ['Librarian', 'Head Librarian']);
        })->with(['role', 'status', 'librarian']);

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
        $baseLibrarianQuery = Account::whereHas('role', fn ($q) => $q->whereIn('name', ['Librarian', 'Head Librarian']));
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
