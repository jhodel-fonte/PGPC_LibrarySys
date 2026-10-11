<?php

namespace App\Livewire\Components\Usermanager;

use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class StudentTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'ALL'; // 'ALL', 'Active', 'Inactive', 'Suspended'
    
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
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->filterStatus = $this->tempFilterStatus;
        $this->filterProgram = $this->tempFilterProgram;
        $this->filterYearLevel = $this->tempFilterYearLevel;
        $this->resetPage();
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
    }

    public function getActiveFilterCountProperty(): int
    {
        $count = 0;
        if (!empty($this->filterStatus)) $count++;
        if (!empty($this->filterProgram)) $count++;
        if (!empty($this->filterYearLevel)) $count++;
        return $count;
    }

    public function render()
    {
        $query = Account::whereHas('role', function ($q) {
            $q->whereIn('name', ['Student', 'Member']);
        })->with(['role', 'status', 'student']);

        // 1. Tab quick filter
        if ($this->activeTab === 'Active') {
            $query->whereHas('status', fn ($q) => $q->where('status_name', 'Active'));
        } elseif ($this->activeTab === 'Inactive') {
            $query->whereHas('status', fn ($q) => $q->where('status_name', 'Inactive'));
        } elseif ($this->activeTab === 'Suspended') {
            $query->whereHas('status', fn ($q) => $q->whereIn('status_name', ['Locked', 'Suspended']));
        }

        // 2. Dropdown filters
        if (!empty($this->filterStatus)) {
            $query->whereHas('status', fn ($q) => $q->where('status_name', $this->filterStatus));
        }

        if (!empty($this->filterProgram)) {
            $query->whereHas('student', fn ($q) => $q->where('program', $this->filterProgram));
        }

        if (!empty($this->filterYearLevel)) {
            $query->whereHas('student', fn ($q) => $q->where('year_level', $this->filterYearLevel));
        }

        // 3. Search filter
        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('school_id_number', 'like', "%{$search}%")
                         ->orWhere('program', 'like', "%{$search}%");
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
            $query->orderBy($sortColumn, $sortDirection);
        }

        $students = $query->paginate(10);

        // Calculate count metrics for tabs
        $baseStudentQuery = Account::whereHas('role', fn ($q) => $q->whereIn('name', ['Student', 'Member']));
        $totalStudents = (clone $baseStudentQuery)->count();
        $activeStudents = (clone $baseStudentQuery)->whereHas('status', fn ($q) => $q->where('status_name', 'Active'))->count();
        $inactiveStudents = (clone $baseStudentQuery)->whereHas('status', fn ($q) => $q->where('status_name', 'Inactive'))->count();
        $lockedStudents = (clone $baseStudentQuery)->whereHas('status', fn ($q) => $q->whereIn('status_name', ['Locked', 'Suspended']))->count();

        $programs = config('settings.college.programs', []);
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        return view('livewire.components.usermanager.student-table', [
            'students' => $students,
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
            'lockedStudents' => $lockedStudents,
            'programs' => $programs,
            'yearLevels' => $yearLevels,
        ]);
    }
}
