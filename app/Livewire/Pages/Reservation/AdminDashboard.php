<?php

namespace App\Livewire\Pages\Reservation;

use App\Models\Book;
use App\Models\BookReservation;
use App\Models\BorrowingTransaction;
use App\Models\BorrowType;
use App\Models\Librarian;
use App\Models\ReservationStatus;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.layouts.admin', [
    'title' => 'Reservations',
    'subpage' => 'Book Reservations',
    'activepageRoute' => 'admin.reservations',
])]
class AdminDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all'; // all, pending, approved, fulfilled, cancelled, expired

    #[Url(as: 'time')]
    public string $timeFilter = 'all'; // all, today, this_week, this_month

    public string $sortColumn = 'reservation_date';
    public string $sortDirection = 'desc';

    public int $perPage = 10;

    // View Details Modal
    public bool $showDetailModal = false;
    public ?int $selectedReservationId = null;

    // Cancellation Modal
    public bool $showCancelModal = false;
    public ?int $cancelReservationId = null;
    public string $cancelReason = '';

    // Quick Reserve Modal
    public bool $showCreateModal = false;
    public string $createSearchStudent = '';
    public string $createSearchBook = '';
    public ?int $createSelectedStudentId = null;
    public ?int $createSelectedBookId = null;
    public int $createPickupDays = 3;
    public string $createNotes = '';

    // Auto-update expired reservations on load
    public function mount(): void
    {
        $this->checkAndExpireReservations();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTimeFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'desc';
        }
    }

    /**
     * Mark pending or approved reservations as expired if pickup due date has passed.
     */
    protected function checkAndExpireReservations(): void
    {
        try {
            $expiredStatus = ReservationStatus::where('status', 'like', '%expired%')->first();
            $expiredStatusId = $expiredStatus ? $expiredStatus->id : 5;

            // Find reservations that are pending or approved but past due_date
            $overdueReservations = BookReservation::whereIn('reservation_status_id', [1, 2])
                ->where('due_date', '<', now())
                ->get();

            foreach ($overdueReservations as $res) {
                $res->update([
                    'reservation_status_id' => $expiredStatusId,
                    'cancelled_date' => now(),
                    'comment' => trim(($res->comment ?? '') . ' [Auto-expired: Pickup deadline passed]'),
                ]);

                // Release book copy if it was reserved
                if ($res->book && $res->book->status === 'reserved') {
                    $res->book->update(['status' => 'available']);
                }
            }
        } catch (\Throwable $e) {
            // Ignore in background
        }
    }

    #[Computed]
    public function stats(): array
    {
        $total = BookReservation::count();
        $pending = BookReservation::where('reservation_status_id', 1)->count();
        $approved = BookReservation::where('reservation_status_id', 2)->count();
        $fulfilled = BookReservation::where('reservation_status_id', 3)->count();
        $cancelled = BookReservation::whereIn('reservation_status_id', [4, 5])->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'fulfilled' => $fulfilled,
            'cancelled' => $cancelled,
        ];
    }

    #[Computed]
    public function reservations()
    {
        $query = BookReservation::query()
            ->with([
                'book.bookDetail.bookData.authors',
                'book.condition',
                'student.account',
                'reservationStatus',
            ]);

        // 1. Search Query
        $term = trim($this->search);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->whereHas('book.bookDetail.bookData', function ($b) use ($term) {
                    $b->where('book_title', 'like', "%{$term}%")
                      ->orWhere('isbn', 'like', "%{$term}%")
                      ->orWhere('call_number', 'like', "%{$term}%");
                })
                ->orWhereHas('book', function ($b) use ($term) {
                    $b->where('accession_number', 'like', "%{$term}%")
                      ->orWhere('code', 'like', "%{$term}%");
                })
                ->orWhereHas('student', function ($s) use ($term) {
                    $s->where('first_name', 'like', "%{$term}%")
                      ->orWhere('last_name', 'like', "%{$term}%")
                      ->orWhere('school_id_number', 'like', "%{$term}%");
                })
                ->orWhere('comment', 'like', "%{$term}%")
                ->orWhere('id', 'like', "%{$term}%");
            });
        }

        // 2. Status Filter
        if ($this->statusFilter !== 'all') {
            $statusMap = [
                'pending' => 1,
                'approved' => 2,
                'fulfilled' => 3,
                'cancelled' => 4,
                'expired' => 5,
            ];

            if (isset($statusMap[$this->statusFilter])) {
                $query->where('reservation_status_id', $statusMap[$this->statusFilter]);
            }
        }

        // 3. Time Filter
        if ($this->timeFilter === 'today') {
            $query->whereDate('reservation_date', Carbon::today());
        } elseif ($this->timeFilter === 'this_week') {
            $query->whereBetween('reservation_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($this->timeFilter === 'this_month') {
            $query->whereBetween('reservation_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }

        // 4. Sorting
        $column = in_array($this->sortColumn, ['reservation_date', 'due_date', 'id', 'created_at'], true)
            ? $this->sortColumn
            : 'reservation_date';

        $query->orderBy($column, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function selectedReservation(): ?BookReservation
    {
        if (!$this->selectedReservationId) {
            return null;
        }

        return BookReservation::with([
            'book.bookDetail.bookData.authors',
            'book.bookDetail.publisher',
            'book.condition',
            'student.account',
            'student.libraryStatus',
            'reservationStatus',
            'borrowingTransactions.issuedBy',
        ])->find($this->selectedReservationId);
    }

    public function viewDetails(int $id): void
    {
        $this->selectedReservationId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedReservationId = null;
    }

    /**
     * Approve reservation: marks status as Approved (ID 2), sets approved_date.
     */
    public function approveReservation(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $reservation = BookReservation::findOrFail($id);

                if ($reservation->reservation_status_id !== 1) {
                    throw new \RuntimeException('Only pending reservations can be approved.');
                }

                $reservation->update([
                    'reservation_status_id' => 2, // Approved
                    'approved_date' => now(),
                    'due_date' => now()->addDays(3), // 3 days window to pick up
                ]);

                // Ensure copy is marked as reserved
                if ($reservation->book) {
                    $reservation->book->update(['status' => 'reserved']);
                }
            });

            $this->dispatch('toast', message: 'Reservation #' . $id . ' approved successfully. The copy is reserved for student pickup.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to approve reservation: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Open Cancel Modal
     */
    public function openCancelModal(int $id): void
    {
        $this->cancelReservationId = $id;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelReservationId = null;
        $this->cancelReason = '';
    }

    /**
     * Cancel Reservation: marks status as Cancelled (ID 4) and releases book copy.
     */
    public function confirmCancelReservation(): void
    {
        if (!$this->cancelReservationId) {
            return;
        }

        try {
            DB::transaction(function () {
                $reservation = BookReservation::findOrFail($this->cancelReservationId);

                $reasonText = trim($this->cancelReason) !== ''
                    ? 'Reason: ' . trim($this->cancelReason)
                    : 'Cancelled by library staff';

                $currentComment = $reservation->comment ? $reservation->comment . ' | ' : '';

                $reservation->update([
                    'reservation_status_id' => 4, // Cancelled
                    'cancelled_date' => now(),
                    'comment' => $currentComment . $reasonText,
                ]);

                // Release physical copy back to available if no other approved reservation holds it
                if ($reservation->book) {
                    $hasOtherActive = BookReservation::where('book_id', $reservation->book_id)
                        ->where('id', '!=', $reservation->id)
                        ->whereIn('reservation_status_id', [1, 2])
                        ->exists();

                    if (!$hasOtherActive) {
                        $reservation->book->update(['status' => 'available']);
                    }
                }
            });

            $this->closeCancelModal();
            $this->dispatch('toast', message: 'Reservation has been cancelled and the book copy released.', type: 'info');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to cancel reservation: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Fulfill Reservation: student arrives at the desk, book is handed over.
     */
    public function fulfillReservation(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $reservation = BookReservation::with(['book', 'student'])->findOrFail($id);

                if (in_array($reservation->reservation_status_id, [3, 4, 5])) {
                    throw new \RuntimeException('This reservation has already been finalized or cancelled.');
                }

                // 1. Mark reservation fulfilled
                $reservation->update([
                    'reservation_status_id' => 3, // Fulfilled
                    'fulfilled_date' => now(),
                ]);

                // 2. Identify staff issuing the book
                $librarian = Auth::user()?->librarian ?? Librarian::first();
                $borrowType = BorrowType::first();

                // 3. Create Borrowing Transaction for the student
                if ($reservation->student && $reservation->book) {
                    BorrowingTransaction::create([
                        'book_id' => $reservation->book_id,
                        'school_id' => $reservation->student->id,
                        'issued_by_id' => $librarian?->id ?? 1,
                        'book_reservation_id' => $reservation->id,
                        'borrow_type_id' => $borrowType?->id ?? 1,
                        'issued_condition_id' => $reservation->book->book_condition_id ?? 1,
                        'return_condition_id' => null,
                        'issued_date' => now(),
                        'due_date' => now()->addDays(7), // Standard loan duration
                        'return_date' => null,
                        'received_by_id' => null,
                    ]);

                    // Update copy status to borrowed
                    $reservation->book->update(['status' => 'borrowed']);
                } else {
                    if ($reservation->book) {
                        $reservation->book->update(['status' => 'available']);
                    }
                }
            });

            $this->dispatch('toast', message: 'Reservation fulfilled! Book successfully checked out to the student.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to fulfill reservation: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Extend pickup deadline by 3 days
     */
    public function extendPickup(int $id): void
    {
        try {
            $reservation = BookReservation::findOrFail($id);
            $newDue = Carbon::parse($reservation->due_date)->addDays(3);
            $reservation->update([
                'due_date' => $newDue,
                'comment' => trim(($reservation->comment ?? '') . ' [Pickup deadline extended by staff]'),
            ]);

            $this->dispatch('toast', message: 'Pickup deadline extended to ' . $newDue->format('M d, Y') . '.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to extend deadline: ' . $e->getMessage(), type: 'error');
        }
    }

    // Modal for creating reservation manually on behalf of a student
    public function openCreateModal(): void
    {
        $this->createSearchStudent = '';
        $this->createSearchBook = '';
        $this->createSelectedStudentId = null;
        $this->createSelectedBookId = null;
        $this->createPickupDays = 3;
        $this->createNotes = '';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    #[Computed]
    public function searchedStudents()
    {
        $term = trim($this->createSearchStudent);
        if (strlen($term) < 2) {
            return collect();
        }

        return Student::where('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->orWhere('school_id_number', 'like', "%{$term}%")
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function searchedBooks()
    {
        $term = trim($this->createSearchBook);
        if (strlen($term) < 2) {
            return collect();
        }

        return Book::with(['bookDetail.bookData'])
            ->where('status', 'available')
            ->where(function ($q) use ($term) {
                $q->where('accession_number', 'like', "%{$term}%")
                  ->orWhereHas('bookDetail.bookData', function ($b) use ($term) {
                      $b->where('book_title', 'like', "%{$term}%");
                  });
            })
            ->limit(5)
            ->get();
    }

    public function selectStudent(int $id): void
    {
        $this->createSelectedStudentId = $id;
    }

    public function selectBook(int $id): void
    {
        $this->createSelectedBookId = $id;
    }

    public function submitManualReservation(): void
    {
        $this->validate([
            'createSelectedStudentId' => 'required|exists:students,id',
            'createSelectedBookId' => 'required|exists:books,id',
            'createPickupDays' => 'required|integer|min:1|max:14',
        ], [
            'createSelectedStudentId.required' => 'Please select a student from the list.',
            'createSelectedBookId.required' => 'Please select an available book copy.',
        ]);

        try {
            DB::transaction(function () {
                $book = Book::lockForUpdate()->findOrFail($this->createSelectedBookId);
                if ($book->status !== 'available') {
                    throw new \RuntimeException('Selected book copy is no longer available.');
                }

                $student = Student::findOrFail($this->createSelectedStudentId);

                $reservation = BookReservation::create([
                    'book_id' => $book->id,
                    'student_id' => $student->id,
                    'account_id' => $student->account_id,
                    'reservation_status_id' => 2, // Pre-approved when created by staff
                    'reservation_date' => now(),
                    'approved_date' => now(),
                    'due_date' => now()->addDays($this->createPickupDays),
                    'comment' => 'Staff reservation. Note: ' . (trim($this->createNotes) ?: 'None'),
                ]);

                $book->update(['status' => 'reserved']);
            });

            $this->closeCreateModal();
            $this->dispatch('toast', message: 'Manual reservation placed and pre-approved successfully!', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Failed to create reservation: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $routePrefix = request()->is('admin*') ? 'admin.' : 'librarian.';

        return view('livewire.pages.reservation.admin-dashboard', [
            'routePrefix' => $routePrefix,
            'reservations' => $this->reservations,
            'stats' => $this->stats,
        ])->layoutData([
            'title' => 'Reservations',
            'subpage' => 'Book Reservations',
            'activepageRoute' => $routePrefix . 'reservations',
        ]);
    }
}
