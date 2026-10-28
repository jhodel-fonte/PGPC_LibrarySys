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
    public string $activeTab = 'overview'; // 'overview', 'personal', 'account', 'history', 'reservations'
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

    // Reserve Book Modal
    public bool $showReserveModal = false;
    public string $reserveSearchBook = '';
    public ?int $selectedBookId = null;
    public int $reservePickupDays = 3;
    public string $reserveComment = '';

    public function mount($userId = null, $id = null, $isProfile = false): void
    {
        $this->isProfile = $isProfile;
        $this->userId = $userId ?? $id ?? request()->route('id') ?? request()->route('userId') ?? request()->route('user');
        if (! $this->userId && auth()->check()) {
            $this->userId = auth()->id();
        }
        $user = $this->user;

        if ($user) {
            if (($user->trashed() || $user->student?->trashed()) && !\App\Services\EmployeeRoleService::isAdmin()) {
                abort(404, 'Student account not found.');
            }

            $authUser = auth()->user();
            if ($authUser && $authUser->id !== $user->id && !\App\Services\EmployeeRoleService::canViewStudents($authUser)) {
                abort(403, 'Unauthorized action. You do not have permission to view student information.');
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
                'reservations.book.bookDetail.bookData.authors',
                'reservations.reservationStatus',
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

    #[Computed]
    public function reservations()
    {
        if (!$this->student) {
            return collect();
        }

        return \App\Models\BookReservation::with([
            'book.bookDetail.bookData.authors',
            'reservationStatus',
        ])
        ->where('student_id', $this->student->id)
        ->orderBy('reservation_date', 'desc')
        ->get();
    }

    #[Computed]
    public function activeReservations()
    {
        return $this->reservations
            ->filter(fn($r) => in_array((int)$r->reservation_status_id, [1, 2]))
            ->values();
    }

    #[Computed]
    public function activeReservationsCount(): int
    {
        return $this->activeReservations->count();
    }

    #[Computed]
    public function pastReservations()
    {
        return $this->reservations
            ->filter(fn($r) => !in_array((int)$r->reservation_status_id, [1, 2]))
            ->values();
    }

    #[Computed]
    public function searchedAvailableBooks()
    {
        $term = trim($this->reserveSearchBook);
        if (strlen($term) < 2) {
            return collect();
        }

        return \App\Models\Book::with(['bookDetail.bookData.authors'])
            ->whereNull('deleted_at')
            ->where('status', 'available')
            ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['disabled', 'archived'])
            ->whereHas('bookDetail', fn($bd) => $bd->whereNull('deleted_at')->whereHas('bookData', fn($b) => $b->whereNull('deleted_at')))
            ->where(function ($q) use ($term) {
                $q->where('accession_number', 'like', "%{$term}%")
                  ->orWhereHas('bookDetail.bookData', function ($b) use ($term) {
                      $b->where('book_title', 'like', "%{$term}%");
                  });
            })
            ->limit(6)
            ->get();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'personal', 'account', 'history', 'reservations'])) {
            $this->activeTab = $tab;
        }
    }

    public function openReserveModal(): void
    {
        $this->reserveSearchBook = '';
        $this->selectedBookId = null;
        $this->reservePickupDays = 3;
        $this->reserveComment = '';
        $this->showReserveModal = true;
    }

    public function closeReserveModal(): void
    {
        $this->showReserveModal = false;
        $this->selectedBookId = null;
    }

    public function selectReserveBook(int $bookId): void
    {
        $this->selectedBookId = $bookId;
    }

    public function confirmProfileReservation(): void
    {
        $this->validate([
            'selectedBookId' => 'required|exists:books,id',
            'reservePickupDays' => 'required|integer|min:1|max:7',
        ], [
            'selectedBookId.required' => 'Please select an available book copy.',
        ]);

        $student = $this->student;
        if (!$student) {
            $this->dispatch('toast', message: 'No student profile found.', type: 'error');
            return;
        }

        // Limit check
        if ($this->activeReservationsCount >= 3) {
            $this->dispatch('toast', message: 'You have reached the maximum limit of 3 active reservations.', type: 'error');
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($student) {
                $book = \App\Models\Book::lockForUpdate()->findOrFail($this->selectedBookId);

                if ($book->status !== 'available') {
                    throw new \RuntimeException('This book copy is no longer available.');
                }

                $pendingStatus = \App\Models\ReservationStatus::where('status', 'like', '%pending%')->first();
                $pendingStatusId = $pendingStatus ? $pendingStatus->id : 1;

                \App\Models\BookReservation::create([
                    'book_id' => $book->id,
                    'student_id' => $student->id,
                    'account_id' => $student->account_id ?? auth()->id(),
                    'reservation_status_id' => $pendingStatusId,
                    'reservation_date' => now(),
                    'due_date' => now()->addDays($this->reservePickupDays)->setTime(17, 0, 0),
                    'comment' => trim($this->reserveComment) ?: 'Reserved from student profile',
                ]);

                $book->update(['status' => 'reserved']);
            });

            $this->closeReserveModal();
            $this->activeTab = 'reservations';
            unset($this->reservations);
            unset($this->user);
            $this->dispatch('toast', message: 'Book reservation placed successfully! Please claim it before the deadline.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Reservation failed: ' . $e->getMessage(), type: 'error');
        }
    }

    public function cancelReservation(int $id): void
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                $reservation = \App\Models\BookReservation::findOrFail($id);

                if (!in_array((int)$reservation->reservation_status_id, [1, 2])) {
                    throw new \RuntimeException('Only active reservations can be cancelled.');
                }

                $reservation->update([
                    'reservation_status_id' => 4, // Cancelled
                    'cancelled_date' => now(),
                    'comment' => trim(($reservation->comment ?? '') . ' | Cancelled by user'),
                ]);

                if ($reservation->book && $reservation->book->status === 'reserved') {
                    $reservation->book->update(['status' => 'available']);
                }
            });

            unset($this->reservations);
            unset($this->user);
            $this->dispatch('toast', message: 'Reservation cancelled and copy released back to inventory.', type: 'info');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Could not cancel reservation: ' . $e->getMessage(), type: 'error');
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

            if (!\App\Services\EmployeeRoleService::canDeleteStudent($authUser)) {
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
        $userId = $user->id;
        $user->delete();

        $this->showDisableModal = false;
        unset($this->user);

        $this->dispatch('toast', [
            'message' => 'Student account has been disabled (soft-deleted). System access and borrowing privileges are suspended.',
            'type' => 'success',
            'undoEvent' => 'enable-student-account',
            'undoPayload' => ['userId' => $userId],
            'duration' => 10000,
        ]);
    }

    #[On('enable-student-account')]
    #[On('undo-disable-student')]
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
            if (!\App\Services\EmployeeRoleService::canEditStudent($authUser) && !\App\Services\EmployeeRoleService::canDeleteStudent($authUser)) {
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

        if ($student && !$student->trashed()) {
            $student->delete();
        }

        $inactiveStatus = \App\Models\AccountStatus::where('status_name', 'Inactive')->first();
        if ($inactiveStatus) {
            $user->update(['status_id' => $inactiveStatus->id]);
        }

        if (!$user->trashed()) {
            $user->delete();
        }

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
