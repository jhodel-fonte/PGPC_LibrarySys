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
        return $user?->student ?? Student::first();
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
        $query = BookDetail::query()
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
                  ->where('status', 'available')
                  ->whereNotIn(DB::raw('LOWER(status)'), ['disabled', 'archived']);
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

        return $query->orderBy('id', 'desc')->paginate($this->perPage);
    }

    #[Computed]
    public function selectedBookDetail(): ?BookDetail
    {
        if (!$this->selectedBookDetailId) {
            return null;
        }

        return BookDetail::with([
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
        $this->selectedBookDetailId = $bookDetailId;
        $detail = BookDetail::with(['books' => fn($q) => $q->whereNull('deleted_at')
            ->where('status', 'available')
            ->whereNotIn(DB::raw('LOWER(status)'), ['disabled', 'archived'])])
            ->whereNull('deleted_at')
            ->whereHas('bookData', fn($q) => $q->whereNull('deleted_at'))
            ->find($bookDetailId);
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
                $book = Book::lockForUpdate()->findOrFail($this->selectedBookCopyId);

                if ($book->status !== 'available') {
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
                    'due_date' => now()->addDays($this->pickupDays),
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
        ]);
    }
}
