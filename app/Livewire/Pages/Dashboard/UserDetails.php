<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Account;
use App\Models\AccountStatus;
use App\Models\BorrowingTransaction;
use App\Models\Fine;
use App\Models\Student;
use App\Models\Librarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', ['title' => 'User Management', 'subpage' => 'User Details', 'activepageRoute' => 'admin.user-management'])]
class UserDetails extends Component
{
    use WithPagination;

    public int|string $id;
    public string $activeTab = 'personal'; // 'personal', 'security', 'activity'
    public string $borrowingFilter = 'all'; // 'all', 'active', 'returned', 'overdue'

    // Status management modal
    public bool $showStatusModal = false;
    public ?int $selectedStatusId = null;
    public string $statusReason = '';

    // Edit profile modal
    public bool $showEditModal = false;
    public string $editFirstName = '';
    public string $editMiddleName = '';
    public string $editLastName = '';
    public string $editEmail = '';
    public string $editContact = '';
    public string $editProgram = '';
    public string $editYearLevel = '';
    public string $editNote = '';

    public function mount($id)
    {
        $this->id = $id;
        $user = $this->user;

        if (!$user) {
            abort(404, 'User account not found.');
        }

        $this->selectedStatusId = $user->status_id;
    }

    #[Computed]
    public function user()
    {
        return Account::with([
            'role',
            'status',
            'student.libraryStatus',
            'librarian',
        ])->find($this->id);
    }

    #[Computed]
    public function person()
    {
        if (!$this->user) return null;
        return $this->user->role?->name === 'Librarian' ? $this->user->librarian : $this->user->student;
    }

    #[Computed]
    public function fullName(): string
    {
        $person = $this->person;
        if ($person) {
            $parts = array_filter([$person->first_name, $person->middle_name, $person->last_name]);
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
    public function statuses()
    {
        return AccountStatus::orderBy('id')->get();
    }

    #[Computed]
    public function borrowingStats(): array
    {
        if (!$this->user || !$this->user->student) {
            return [
                'total' => 0,
                'active' => 0,
                'returned' => 0,
                'overdue' => 0,
            ];
        }

        $studentId = $this->user->student->id;
        $now = Carbon::now();

        $total = BorrowingTransaction::where('school_id', $studentId)->count();
        $returned = BorrowingTransaction::where('school_id', $studentId)->whereNotNull('return_date')->count();
        $active = BorrowingTransaction::where('school_id', $studentId)
            ->whereNull('return_date')
            ->where('due_date', '>=', $now)
            ->count();
        $overdue = BorrowingTransaction::where('school_id', $studentId)
            ->whereNull('return_date')
            ->where('due_date', '<', $now)
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'returned' => $returned,
            'overdue' => $overdue,
        ];
    }

    #[Computed]
    public function librarianStats(): array
    {
        if (!$this->user || !$this->user->librarian) {
            return ['issued' => 0, 'received' => 0];
        }

        $librarianId = $this->user->librarian->id;
        return [
            'issued' => BorrowingTransaction::where('issued_by_id', $librarianId)->count(),
            'received' => BorrowingTransaction::where('received_by_id', $librarianId)->count(),
        ];
    }

    #[Computed]
    public function finesStats(): array
    {
        if (!$this->user || !$this->user->student) {
            return ['total' => 0, 'pending' => 0, 'paid' => 0];
        }

        $studentId = $this->user->student->id;
        $fines = Fine::where('student_id', $studentId)->get();

        $total = $fines->sum('amount');
        $paid = $fines->where('status', 'paid')->sum('amount');
        $pending = $total - $paid;

        return [
            'total' => $total,
            'pending' => max(0, $pending),
            'paid' => $paid,
            'count' => $fines->count(),
        ];
    }

    #[Computed]
    public function borrowings()
    {
        if (!$this->user || !$this->user->student) {
            return collect();
        }

        $query = BorrowingTransaction::with([
            'book.bookDetail.bookData',
            'issuedBy',
            'receivedBy',
            'issuedCondition',
            'returnCondition',
            'fines',
        ])
        ->where('school_id', $this->user->student->id)
        ->latest('issued_date');

        $now = Carbon::now();

        if ($this->borrowingFilter === 'active') {
            $query->whereNull('return_date')->where('due_date', '>=', $now);
        } elseif ($this->borrowingFilter === 'overdue') {
            $query->whereNull('return_date')->where('due_date', '<', $now);
        } elseif ($this->borrowingFilter === 'returned') {
            $query->whereNotNull('return_date');
        }

        return $query->paginate(8, ['*'], 'borrowingPage');
    }

    #[Computed]
    public function fines()
    {
        if (!$this->user || !$this->user->student) {
            return collect();
        }

        return Fine::with(['fineType', 'borrowingTransaction.book.bookDetail.bookData'])
            ->where('student_id', $this->user->student->id)
            ->latest()
            ->paginate(8, ['*'], 'finesPage');
    }

    #[On('set-tab')]
    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function setBorrowingFilter(string $filter)
    {
        $this->borrowingFilter = $filter;
        $this->resetPage('borrowingPage');
    }

    #[On('open-status-modal')]
    public function openStatusModal()
    {
        $this->selectedStatusId = $this->user->status_id;
        $this->statusReason = '';
        $this->showStatusModal = true;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
    }

    public function updateStatus()
    {
        $this->validate([
            'selectedStatusId' => 'required|exists:account_statuses,id',
        ]);

        $account = Account::findOrFail($this->id);
        $oldStatus = $account->status?->status_name ?? 'Unknown';
        $account->update(['status_id' => $this->selectedStatusId]);
        
        $newStatus = AccountStatus::find($this->selectedStatusId)?->status_name ?? 'Updated';

        $this->showStatusModal = false;
        unset($this->user);

        $this->dispatch('toast', message: "Account status updated from {$oldStatus} to {$newStatus}.", type: 'success');
    }

    public function openEditModal()
    {
        $user = $this->user;
        $person = $this->person;

        $this->editFirstName = $person->first_name ?? '';
        $this->editMiddleName = $person->middle_name ?? '';
        $this->editLastName = $person->last_name ?? '';
        $this->editEmail = $user->email ?? '';
        $this->editContact = $person->contact_num ?? '';

        if ($user->student) {
            $this->editProgram = $user->student->program ?? '';
            $this->editYearLevel = $user->student->year_level ?? '';
            $this->editNote = $user->student->note ?? '';
        }

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
    }

    public function saveProfile()
    {
        $this->validate([
            'editFirstName' => 'required|string|max:100',
            'editLastName' => 'required|string|max:100',
            'editEmail' => ['required', 'email', 'max:255', Rule::unique('accounts', 'email')->ignore($this->id)],
            'editContact' => 'nullable|string|max:30',
        ]);

        DB::transaction(function () {
            $account = Account::findOrFail($this->id);
            $account->update(['email' => $this->editEmail]);

            if ($account->role?->name === 'Librarian' && $account->librarian) {
                $account->librarian->update([
                    'first_name' => $this->editFirstName,
                    'middle_name' => $this->editMiddleName,
                    'last_name' => $this->editLastName,
                    'contact_num' => $this->editContact,
                ]);
            } elseif ($account->student) {
                $account->student->update([
                    'first_name' => $this->editFirstName,
                    'middle_name' => $this->editMiddleName,
                    'last_name' => $this->editLastName,
                    'contact_num' => $this->editContact,
                    'program' => $this->editProgram,
                    'year_level' => $this->editYearLevel,
                    'note' => $this->editNote,
                ]);
            }
        });

        $this->showEditModal = false;
        unset($this->user);
        unset($this->person);

        $this->dispatch('toast', message: 'User profile details updated successfully.', type: 'success');
    }

    #[On('send-password-reset')]
    public function sendPasswordReset()
    {
        $account = $this->user;

        if (!$account || empty($account->email)) {
            $this->dispatch('toast', message: 'Cannot send password reset: User has no valid email address.', type: 'error');
            return;
        }

        try {
            $token = app('auth.password.broker')->createToken($account);
            $account->sendPasswordResetNotification($token);
            $this->dispatch('toast', message: "Password reset instructions sent to {$account->email}.", type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to send password reset email: ' . $e->getMessage(), type: 'error');
        }
    }

    #[On('toggle-email-verification')]
    public function toggleEmailVerification()
    {
        $account = Account::findOrFail($this->id);
        $newVerified = !$account->is_email_verified;

        $account->update([
            'is_email_verified' => $newVerified,
            'email_verified_at' => $newVerified ? Carbon::now() : null,
        ]);

        unset($this->user);
        $statusStr = $newVerified ? 'Verified' : 'Unverified';
        $this->dispatch('toast', message: "Email status marked as {$statusStr}.", type: 'success');
    }

    public function render()
    {
        return view('livewire.pages.dashboard.user-details');
    }
}
