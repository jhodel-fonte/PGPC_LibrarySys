<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Account;
use App\Models\Role;
use App\Models\AccountStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;

#[Layout('livewire.layouts.admin', ['title' => 'User Management'])]
#[Lazy]
class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $activeTab = 'ALL'; // 'ALL', 'Student', 'Librarian'
    public $filterStatus = '';
    public $filterRole = '';
    public $filterVerification = '';

    // Staged filter properties before clicking Apply
    public $tempFilterStatus = '';
    public $tempFilterRole = '';
    public $tempFilterVerification = '';

    public array $sort = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public function placeholder()
    {
        return <<<'HTML'
        <div class="min-h-full bg-[#F8FAFC]">
            <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 space-y-6">
                <!-- Header Skeleton -->
                <div class="flex justify-between items-start animate-pulse">
                    <div>
                        <div class="h-8 bg-slate-200 rounded w-48 mb-2"></div>
                        <div class="h-4 bg-slate-200 rounded w-64"></div>
                    </div>
                    <div class="flex gap-3">
                        <div class="h-11 w-28 bg-slate-200 rounded-2xl"></div>
                        <div class="h-11 w-32 bg-slate-200 rounded-2xl"></div>
                    </div>
                </div>

                <!-- Stats Skeleton -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5">
                    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between gap-3 animate-pulse">
                        <div>
                            <div class="h-3.5 bg-slate-200 rounded w-24 mb-2"></div>
                            <div class="h-7 bg-slate-200 rounded w-16"></div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-200 shrink-0"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between gap-3 animate-pulse">
                        <div>
                            <div class="h-3.5 bg-slate-200 rounded w-24 mb-2"></div>
                            <div class="h-7 bg-slate-200 rounded w-16"></div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-200 shrink-0"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between gap-3 animate-pulse">
                        <div>
                            <div class="h-3.5 bg-slate-200 rounded w-24 mb-2"></div>
                            <div class="h-7 bg-slate-200 rounded w-16"></div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-200 shrink-0"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3.5 flex items-center justify-between gap-3 animate-pulse">
                        <div>
                            <div class="h-3.5 bg-slate-200 rounded w-24 mb-2"></div>
                            <div class="h-7 bg-slate-200 rounded w-16"></div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-200 shrink-0"></div>
                    </div>
                </div>

                <!-- Table Skeleton -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-pulse">
                    <div class="px-6 py-4 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex gap-2">
                            <div class="h-9 w-20 bg-slate-200 rounded-lg"></div>
                            <div class="h-9 w-20 bg-slate-200 rounded-lg"></div>
                            <div class="h-9 w-20 bg-slate-200 rounded-lg"></div>
                        </div>
                        <div class="h-10 w-full md:w-[320px] bg-slate-200 rounded-xl"></div>
                    </div>
                    <div class="p-5 space-y-5">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-slate-200 shrink-0"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                                <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                            </div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-slate-200 shrink-0"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                                <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                            </div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-slate-200 shrink-0"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                                <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                            </div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-slate-200 shrink-0"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                                <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                            </div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-6 bg-slate-200 rounded-md w-20"></div>
                            <div class="h-4 bg-slate-200 rounded w-20"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function getHeadersProperty()
    {
        return [
            ['index' => 'user', 'label' => 'User', 'sortable' => true],
            ['index' => 'id_number', 'label' => 'ID Number', 'sortable' => false],
            ['index' => 'role', 'label' => 'Account Type', 'sortable' => true],
            ['index' => 'status', 'label' => 'Status', 'sortable' => true],
            ['index' => 'last_login', 'label' => 'Last Login', 'sortable' => true],
            ['index' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ];
    }

    public function sortBy($column)
    {
        if ($this->sort['column'] === $column) {
            $this->sort['direction'] = $this->sort['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort['column'] = $column;
            $this->sort['direction'] = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->filterStatus = $this->tempFilterStatus;
        $this->filterRole = $this->tempFilterRole;
        $this->filterVerification = $this->tempFilterVerification;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->tempFilterStatus = '';
        $this->tempFilterRole = '';
        $this->tempFilterVerification = '';
        $this->filterStatus = '';
        $this->filterRole = '';
        $this->filterVerification = '';
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if (!empty($this->filterStatus)) $count++;
        if (!empty($this->filterRole)) $count++;
        if (!empty($this->filterVerification)) $count++;
        return $count;
    }

    public function render()
    {
        $query = Account::with(['role', 'status', 'student', 'librarian']);

        // 1. Apply Top Tab Filter (ALL, Student, Librarian)
        if ($this->activeTab === 'Student') {
            $query->whereHas('role', function($q) {
                $q->whereIn('name', ['Student', 'Member']);
            });
        } elseif ($this->activeTab === 'Librarian') {
            $query->whereHas('role', function($q) {
                $q->whereIn('name', ['Librarian', 'Head Librarian']);
            });
        }

        // 2. Apply Dropdown Filters
        if (!empty($this->filterStatus)) {
            $query->whereHas('status', function ($q) {
                $q->where('status_name', $this->filterStatus);
            });
        }

        if (!empty($this->filterRole)) {
            $query->whereHas('role', function ($q) {
                $q->where('name', $this->filterRole);
            });
        }

        if ($this->filterVerification === 'verified') {
            $query->where('is_email_verified', true);
        } elseif ($this->filterVerification === 'unverified') {
            $query->where('is_email_verified', false);
        }

        // 3. Apply Search Filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('student', function($sq) {
                      $sq->where('first_name', 'like', '%' . $this->search . '%')
                         ->orWhere('last_name', 'like', '%' . $this->search . '%')
                         ->orWhere('school_id_number', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('librarian', function($lq) {
                      $lq->where('first_name', 'like', '%' . $this->search . '%')
                         ->orWhere('last_name', 'like', '%' . $this->search . '%')
                         ->orWhere('school_id_number', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // 4. Apply Sorting
        $sortColumn = $this->sort['column'];
        $sortDirection = $this->sort['direction'];

        if ($sortColumn === 'role') {
            $query->join('roles', 'accounts.role_id', '=', 'roles.id')
                  ->select('accounts.*')
                  ->orderBy('roles.name', $sortDirection);
        } elseif ($sortColumn === 'status') {
            $query->join('account_statuses', 'accounts.status_id', '=', 'account_statuses.id')
                  ->select('accounts.*')
                  ->orderBy('account_statuses.status_name', $sortDirection);
        } elseif ($sortColumn === 'user') {
            $query->orderBy('username', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $users = $query->paginate(10);

        // Fetch statistics efficiently
        $totalStudents = Account::whereHas('role', function($q) { $q->whereIn('name', ['Student', 'Member']); })->count();
        $totalLibrarians = Account::whereHas('role', function($q) { $q->whereIn('name', ['Librarian', 'Head Librarian']); })->count();
        $activeAccounts = Account::whereHas('status', function($q) { $q->where('status_name', 'Active'); })->count();
        $lockedAccounts = Account::whereHas('status', function($q) {
            $q->whereIn('status_name', ['Locked', 'Suspended']);
        })->count();

        return view('livewire.pages.dashboard.user-management', [
            'users' => $users,
            'totalStudents' => $totalStudents,
            'totalLibrarians' => $totalLibrarians,
            'activeAccounts' => $activeAccounts,
            'lockedAccounts' => $lockedAccounts,
        ]);
    }
}
