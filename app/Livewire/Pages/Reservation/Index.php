<?php

namespace App\Livewire\Pages\Reservation;

use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\Category;
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
    'title' => 'Reserve a Book',
    'subpage' => 'Browse & Reserve',
    'activepageRoute' => 'student.reservations',
])]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'category')]
    public string $selectedCategory = 'all';

    public int $perPage = 8;

    // Reservation Modal state
    public bool $showModal = false;
    public ?int $selectedBookDetailId = null;
    public ?int $selectedBookCopyId = null;
    public int $pickupDays = 3;
    public string $studentComment = '';
    public bool $isSubmitting = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function currentStudent(): ?Student
    {
        $user = Auth::user();
        return $user?->student;
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function activeReservationsCount(): int
    {
        $student = $this->currentStudent;
        if (!$student) {
            return 0;
        }

        return BookReservation::where('student_id', $student->id)
            ->whereIn('reservation_status_id', [1, 2])
            ->count();
    }

    #[Computed]
    public function catalogBooks()
    {
        $query = BookDetail::publicCatalog()
            ->whereNull('deleted_at')
            ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
            ->with([
                'bookData.authors',
                'bookData.categories',
                'publisher',
                'books' => function ($q) {
                    $q->whereNull('deleted_at')
                      ->where('status', 'available')
                      ->whereNotIn(DB::raw('LOWER(status)'), ['disabled', 'archived']);
                },
            ])
            ->whereHas('books', function ($q) {
                $q->whereNull('deleted_at')
                  ->whereIn('status', ['available', 'borrowed', 'reserved']);
            });

        // 1. Search Query
        $term = trim($this->search);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->whereHas('bookData', function ($b) use ($term) {
                    $b->where('book_title', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%");
                })
                ->orWhere('isbn', 'like', "%{$term}%")
                ->orWhere('call_number', 'like', "%{$term}%")
                ->orWhereHas('bookData.authors', function ($a) use ($term) {
                    $a->where('first_name', 'like', "%{$term}%")
                      ->orWhere('last_name', 'like', "%{$term}%");
                });
            });
        }

        // 2. Category Filter
        if ($this->selectedCategory !== 'all') {
            $catId = (int) $this->selectedCategory;
            $query->whereHas('bookData.categories', function ($c) use ($catId) {
                $c->where('categories.id', $catId);
            });
        }

        return $query->orderBy('id', 'desc')->cursorPaginate($this->perPage, ['*'], 'page');
    }

    #[Computed]
    public function selectedBookDetail(): ?BookDetail
    {
        if (!$this->selectedBookDetailId) {
            return null;
        }

        return BookDetail::publicCatalog()->with([
            'bookData.authors',
            'bookData.categories',
            'publisher',
            'books' => fn($q) => $q->whereNull('deleted_at')
                ->where('status', 'available')
                ->whereNotIn(DB::raw('LOWER(status)'), ['disabled', 'archived']),
        ])
        ->whereNull('deleted_at')
        ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
        ->find($this->selectedBookDetailId);
    }

    public function openReservationModal(int $bookDetailId): void
    {
        $detail = BookDetail::publicCatalog()->with(['books' => fn($q) => $q->whereNull('deleted_at')
            ->where('status', 'available')
            ->whereNotIn(DB::raw('LOWER(status)'), ['disabled', 'archived'])])
            ->whereNull('deleted_at')
            ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
            ->find($bookDetailId);

        if (!$detail) {
            $this->dispatch('toast', message: 'This item is for counter loan only and cannot be reserved online.', type: 'error');
            return;
        }

        $this->selectedBookDetailId = $detail->id;
        $firstAvailableCopy = $detail?->books->first();

        $this->selectedBookCopyId = $firstAvailableCopy?->id;
        $this->pickupDays = 3;
        $this->studentComment = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedBookDetailId = null;
        $this->selectedBookCopyId = null;
        $this->studentComment = '';
    }

    public function submitReservation()
    {
        if (Auth::user()?->reservations_blocked) {
            $this->dispatch('toast', message: 'Your account cannot place reservations. Please contact the library.', type: 'error');
            return;
        }

        $detail = $this->selectedBookDetailId
            ? BookDetail::publicCatalog()->whereKey($this->selectedBookDetailId)->first()
            : null;

        if (!$detail) {
            $this->dispatch('toast', message: 'This item is for counter loan only and cannot be reserved online.', type: 'error');
            return;
        }

        if (! $this->selectedBookCopyId) {
            try {
                app(\App\Services\ReservationWaitlistService::class)->join($detail->id, Auth::user());
                $this->closeModal();
                $this->dispatch('toast', message: 'You joined the waitlist. We will notify you when a copy is reserved for you.', type: 'success');
            } catch (\Throwable $e) {
                $this->dispatch('toast', message: $e->getMessage(), type: 'error');
            }
            return;
        }

        $this->validate([
            'selectedBookCopyId' => 'required|exists:books,id',
            'pickupDays' => 'required|integer|min:1|max:7',
        ], [
            'selectedBookCopyId.required' => 'An available book copy could not be determined.',
        ]);

        $student = $this->currentStudent;
        if (!$student) {
            $this->dispatch('toast', message: 'No student profile detected. Please log in as a student.', type: 'error');
            return;
        }

        // Check if student already has 3 or more active reservations
        $activeCount = BookReservation::where('student_id', $student->id)
            ->whereIn('reservation_status_id', [1, 2])
            ->count();

        if ($activeCount >= 3) {
            $this->dispatch('toast', message: 'You have reached the maximum limit of 3 active book reservations.', type: 'error');
            return;
        }

        $this->isSubmitting = true;

        try {
            $reservation = DB::transaction(function () use ($student) {
                $detail = BookDetail::publicCatalog()->lockForUpdate()->findOrFail($this->selectedBookDetailId);
                if (\App\Models\ReservationWaitlist::where('book_detail_id', $this->selectedBookDetailId)->where('status', 'waiting')->exists()) {
                    throw new \RuntimeException('Copies are reserved for people already on the waitlist.');
                }
                $book = Book::lockForUpdate()->findOrFail($this->selectedBookCopyId);

                if ($book->book_detail_id !== $detail->id || $book->status !== 'available') {
                    throw new \RuntimeException('This book copy is no longer available. Please select another copy or title.');
                }

                $pendingStatus = ReservationStatus::where('status', 'like', '%pending%')->first();
                $pendingStatusId = $pendingStatus ? $pendingStatus->id : 1;

                $newReservation = BookReservation::create([
                    'book_id' => $book->id,
                    'student_id' => $student->id,
                    'account_id' => $student->account_id ?? Auth::id(),
                    'reservation_status_id' => $pendingStatusId,
                    'reservation_date' => now(),
                    'due_date' => now()->addDays($this->pickupDays)->setTime(17, 0, 0),
                    'comment' => trim($this->studentComment) ?: 'Student reservation through student portal',
                ]);

                // Mark copy as reserved
                $book->update(['status' => 'reserved']);

                return $newReservation;
            });

            $this->closeModal();
            $this->isSubmitting = false;

            return redirect()->route('student.reservations.confirmation', $reservation->id);
        } catch (\Throwable $e) {
            $this->isSubmitting = false;
            $this->dispatch('toast', message: 'Reservation failed: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.pages.reservation.index', [
            'books' => $this->catalogBooks,
            'categories' => $this->categories,
            'student' => $this->currentStudent,
            'activeCount' => $this->activeReservationsCount,
            'waitingEntries' => DB::table('reservation_waitlist')
                ->join('book_details', 'book_details.id', '=', 'reservation_waitlist.book_detail_id')
                ->join('book_datas', 'book_datas.id', '=', 'book_details.book_data_id')
                ->where('reservation_waitlist.account_id', Auth::id())
                ->where('reservation_waitlist.status', 'waiting')
                ->select('reservation_waitlist.id', 'book_datas.book_title')
                ->orderBy('reservation_waitlist.id')->get(),
        ]);
    }

    public function leaveWaitlist(int $id): void
    {
        \App\Models\ReservationWaitlist::whereKey($id)->where('account_id', Auth::id())
            ->where('status', 'waiting')->update(['status' => 'cancelled']);
    }
}
