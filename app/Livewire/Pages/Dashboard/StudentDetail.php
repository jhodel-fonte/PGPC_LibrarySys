<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Account;
use App\Models\AccountStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'User Management', 'subpage' => 'Student Details', 'activepageRoute' => 'admin.user-management'])]
class StudentDetail extends Component
{
    public int|string|null $userId = null;
    public string $activeTab = 'overview'; // 'overview', 'personal', 'account', 'history'
    public bool $isProfile = false;

    // Status management modal
    public bool $showStatusModal = false;
    public ?int $selectedStatusId = null;

    // Delete & Disable student modals
    public bool $showDeleteModal = false;
    public bool $showDisableModal = false;

    // Edit profile modal
    public bool $showEditModal = false;
    public string $editFirstName = '';
    public string $editMiddleName = '';
    public string $editLastName = '';
    public string $editEmail = '';
    public string $editContact = '';
    public string $editProgram = '';
    public string $editYearLevel = '';
    public string $editGender = '';
    public string $editNote = '';

    public function mount($userId = null, $id = null, $isProfile = false): void
    {
        $this->isProfile = $isProfile;
        $this->userId = $userId ?? $id ?? request()->route('id') ?? request()->route('userId') ?? request()->route('user');
        if (! $this->userId && auth()->check()) {
            $this->userId = auth()->id();
        }
        $user = $this->user;

        if ($user) {
            $authUser = auth()->user();
            if ($authUser && !$authUser->hasRole('Admin') && $authUser->id !== $user->id) {
                $canView = $authUser->hasPermission('view_students') || $authUser->hasPermission('manage_users');
                if (!$canView) {
                    abort(403, 'Unauthorized action. You do not have permission to view student information.');
                }
            }

            $this->selectedStatusId = $user->status_id;
            $student = $user->student;
            $this->editFirstName = $student->first_name ?? '';
            $this->editMiddleName = $student->middle_name ?? '';
            $this->editLastName = $student->last_name ?? '';
            $this->editEmail = $user->email ?? '';
            $this->editContact = $student->contact_num ?? '';
            $this->editProgram = $student->program ?? '';
            $this->editYearLevel = $student->year_level ?? '';
            $this->editGender = $student->gender ?? '';
            $this->editNote = $student->note ?? '';
        }
    }

    #[Computed]
    public function user()
    {
        if (!$this->userId) {
            return null;
        }

        return Account::withTrashed()->with([
            'role',
            'status',
            'student' => fn($q) => $q->withTrashed()->with([
                'libraryStatus',
                'borrowingTransactions.book.bookDetail.bookData.authors',
                'borrowingTransactions.issuedBy',
                'borrowingTransactions.receivedBy',
                'borrowingTransactions.borrowType',
                'borrowingTransactions.fines',
                'fines',
            ]),
        ])->find($this->userId);
    }

    #[Computed]
    public function student()
    {
        return $this->user?->student;
    }

    #[Computed]
    public function fullName(): string
    {
        $student = $this->student;
        if ($student) {
            $parts = array_filter([$student->first_name, $student->middle_name, $student->last_name]);
            return implode(' ', $parts);
        }
        return $this->user->username ?? 'N/A';
    }

    #[Computed]
    public function initials(): string
    {
        $name = $this->fullName;
        return collect(explode(' ', $name))
            ->map(fn($n) => substr($n, 0, 1))
            ->take(2)
            ->join('');
    }

    #[Computed]
    public function formattedDob(): string
    {
        $dob = $this->student?->date_of_birth;
        if (empty($dob)) {
            return 'Not specified';
        }

        try {
            return \Carbon\Carbon::parse($dob)->format('M d, Y');
        } catch (\Throwable $e) {
            try {
                return \Carbon\Carbon::createFromFormat('m-Y', $dob)->format('M Y');
            } catch (\Throwable $e2) {
                try {
                    return \Carbon\Carbon::createFromFormat('m/Y', $dob)->format('M Y');
                } catch (\Throwable $e3) {
                    return (string) $dob;
                }
            }
        }
    }

    #[Computed]
    public function allTransactions()
    {
        return $this->student?->borrowingTransactions ?? collect();
    }

    #[Computed]
    public function activeLoans()
    {
        return $this->allTransactions
            ->whereNull('return_date')
            ->sortByDesc('issued_date')
            ->values();
    }

    #[Computed]
    public function activeLoansCount(): int
    {
        return $this->activeLoans->count();
    }

    #[Computed]
    public function overdueLoansCount(): int
    {
        return $this->activeLoans
            ->filter(fn($t) => $t->due_date && $t->due_date->isPast())
            ->count();
    }

    #[Computed]
    public function unpaidFinesTotal(): float
    {
        return (float) ($this->student?->fines->whereNull('paid_at')->sum('amount') ?? 0);
    }

    #[Computed]
    public function recentActivity()
    {
        return $this->allTransactions
            ->sortByDesc(fn($t) => $t->return_date ?? $t->issued_date ?? $t->created_at)
            ->take(6)
            ->values();
    }

    #[Computed]
    public function circulationHistory()
    {
        return $this->allTransactions
            ->sortByDesc(fn($t) => $t->issued_date ?? $t->created_at)
            ->values();
    }

    #[Computed]
    public function statuses()
    {
        return AccountStatus::orderBy('id')->get();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'personal', 'history'])) {
            $this->activeTab = $tab;
        }
    }

    public function openEditModal(): void
    {
        $student = $this->student;
        if ($student) {
            $this->editFirstName = $student->first_name ?? '';
            $this->editMiddleName = $student->middle_name ?? '';
            $this->editLastName = $student->last_name ?? '';
            $this->editEmail = $this->user->email ?? '';
            $this->editContact = $student->contact_num ?? '';
            $this->editProgram = $student->program ?? '';
            $this->editYearLevel = $student->year_level ?? '';
            $this->editGender = $student->gender ?? '';
            $this->editNote = $student->note ?? '';
        }
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function saveEditModal(): void
    {
        $student = $this->student;
        $user = $this->user;

        if ($student) {
            $student->update([
                'first_name' => $this->editFirstName,
                'middle_name' => $this->editMiddleName,
                'last_name' => $this->editLastName,
                'gender' => $this->editGender ?: null,
                'contact_num' => $this->editContact,
                'program' => $this->editProgram,
                'year_level' => $this->editYearLevel,
                'note' => $this->editNote,
            ]);
        }

        if ($user && !empty($this->editEmail)) {
            $user->update([
                'email' => $this->editEmail,
            ]);
        }

        $this->showEditModal = false;
    }

    public function openStatusModal(): void
    {
        $this->selectedStatusId = $this->user?->status_id;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
    }

    public function updateStatus(): void
    {
        if ($this->selectedStatusId && $this->user) {
            $this->user->update([
                'status_id' => $this->selectedStatusId,
            ]);
        }
        $this->showStatusModal = false;
    }

    #[On('open-disable-modal')]
    public function openDisableModal(): void
    {
        $this->showDisableModal = true;
    }

    public function closeDisableModal(): void
    {
        $this->showDisableModal = false;
    }

    #[On('disable-student-account')]
    public function disableAccount(): void
    {
        $user = $this->user;
        $student = $this->student;

        if (!$user) {
            $this->dispatch('toast', message: 'Student account not found.', type: 'error');
            $this->showDisableModal = false;
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if ($authUser->id === $user->id) {
                $this->dispatch('toast', message: 'You cannot disable your own account.', type: 'error');
                $this->showDisableModal = false;
                return;
            }

            if (!$authUser->hasRole('Admin') && !$authUser->hasPermission('delete_students') && !$authUser->hasPermission('manage_users')) {
                abort(403, 'Unauthorized action. You do not have permission to disable student accounts.');
            }
        }

        // Soft-delete student record
        if ($student) {
            $student->delete();
        }

        // Set status to Inactive
        $inactiveStatus = \App\Models\AccountStatus::where('status_name', 'Inactive')->first();
        if ($inactiveStatus) {
            $user->update(['status_id' => $inactiveStatus->id]);
        }

        // Soft-delete account record
        $user->delete();

        $this->showDisableModal = false;
        unset($this->user);

        $this->dispatch('toast', message: 'Student account has been disabled (soft-deleted). System access and borrowing privileges are suspended.', type: 'success');
    }

    #[On('enable-student-account')]
    public function enableAccount(): void
    {
        $user = $this->user;
        $student = $this->student;

        if (!$user) {
            $this->dispatch('toast', message: 'Student account not found.', type: 'error');
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if (!$authUser->hasRole('Admin') && !$authUser->hasPermission('delete_students') && !$authUser->hasPermission('manage_users') && !$authUser->hasPermission('edit_students')) {
                abort(403, 'Unauthorized action. You do not have permission to enable student accounts.');
            }
        }

        // Restore account and student records
        $user->restore();
        if ($student) {
            $student->restore();
        }

        // Set status back to Active
        $activeStatus = \App\Models\AccountStatus::where('status_name', 'Active')->first();
        if ($activeStatus) {
            $user->update(['status_id' => $activeStatus->id]);
        }

        unset($this->user);

        $this->dispatch('toast', message: 'Student account has been successfully enabled and set to Active.', type: 'success');
    }

    #[On('open-delete-modal')]
    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteStudent(): void
    {
        $user = $this->user;
        $student = $this->student;

        if (!$user) {
            $this->dispatch('toast', message: 'Student account not found.', type: 'error');
            $this->showDeleteModal = false;
            return;
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if ($authUser->id === $user->id) {
                $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
                $this->showDeleteModal = false;
                return;
            }

            if ($student && !$authUser->can('delete', $student)) {
                abort(403, 'Unauthorized action. You do not have permission to delete student accounts.');
            }
        }

        if ($this->activeLoansCount > 0) {
            $this->dispatch('toast', message: 'Cannot delete student with active borrowed books. Please return all borrowed items first.', type: 'error');
            $this->showDeleteModal = false;
            return;
        }

        if ($student) {
            $student->delete();
        }
        $user->delete();

        session()->flash('toast', [
            'message' => 'Student account has been successfully deleted.',
            'type' => 'success',
        ]);

        $prefix = request()->is('librarian*') ? 'librarian.' : 'admin.';
        $this->redirect(route($prefix . 'user-management', ['type' => 'student']), navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.dashboard.student-detail', [
            'user' => $this->user,
            'student' => $this->student,
            'fullName' => $this->fullName,
            'initials' => $this->initials,
            'formattedDob' => $this->formattedDob,
            'activeLoans' => $this->activeLoans,
            'activeLoansCount' => $this->activeLoansCount,
            'overdueLoansCount' => $this->overdueLoansCount,
            'unpaidFinesTotal' => $this->unpaidFinesTotal,
            'recentActivity' => $this->recentActivity,
            'circulationHistory' => $this->circulationHistory,
            'statuses' => $this->statuses,
        ]);
    }
}
