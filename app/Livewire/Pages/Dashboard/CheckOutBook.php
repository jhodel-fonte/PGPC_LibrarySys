<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BorrowingTransaction;
use App\Models\Fine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('livewire.layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Borrow Books', 'activepageRoute' => 'admin.circulation-desk.index'])]
class CheckOutBook extends Component
{
    public $scannedMember = null;
    public $checkoutBooks = [];
    public $lastScannedBook = null;
    public $errorMessage = '';
    public $showConfirmChangeMember = false;
    public $pendingStudentId = null;
    public $pendingStudentName = '';
    public $stats = [
        'total' => 0,
        'due_date' => '',
        'overdue_count' => 0,
        'unpaid_fines' => 0.00,
        'borrow_limit_reached' => false,
        'remaining_slots' => 3
    ];

    public function mount()
    {
        \App\Services\EmployeeRoleService::authorizeBorrow('Unauthorized action. You do not have permission to process book borrowings.');

        $this->updateStats();
    }

    public function getQrConfig()
    {
        return config('settings', []);
    }

    public function updateStats()
    {
        $loanPeriod = (int) config('settings.library_config.loan_period', config('settings.loan_period', 5));
        $this->stats['total'] = count($this->checkoutBooks);
        $this->stats['due_date'] = Carbon::now()->addDays($loanPeriod)->format('M. d, Y');

        if ($this->scannedMember) {
            $studentId = $this->scannedMember['id'];

            // 1. Calculate overdue books count
            $overdueCount = BorrowingTransaction::where('school_id', $studentId)
                ->whereNull('return_date')
                ->where('due_date', '<', Carbon::now())
                ->count();
            $this->stats['overdue_count'] = $overdueCount;

            // 2. Calculate unpaid fines amount
            $unpaidFines = Fine::where('student_id', $studentId)
                ->where('fine_status', 'unpaid')
                ->sum('amount');
            $this->stats['unpaid_fines'] = floatval($unpaidFines);

            // 3. Max active borrow limits check
            $activeBorrowedCount = BorrowingTransaction::where('school_id', $studentId)
                ->whereNull('return_date')
                ->count();

            $maxLimit = 3;
            $currentQueuedCount = count($this->checkoutBooks);
            $totalAssigned = $activeBorrowedCount + $currentQueuedCount;

            $this->stats['remaining_slots'] = max(0, $maxLimit - $totalAssigned);
            $this->stats['borrow_limit_reached'] = ($totalAssigned >= $maxLimit);
        } else {
            $this->stats['overdue_count'] = 0;
            $this->stats['unpaid_fines'] = 0.00;
            $this->stats['remaining_slots'] = 3;
            $this->stats['borrow_limit_reached'] = false;
        }
    }

    protected function normalizeCode($code): string
    {
        if (!is_string($code) && !is_numeric($code)) {
            return '';
        }

        $code = (string) $code;
        // Strip scanner non-printable characters, carriage returns, newlines, tabs
        $code = trim(preg_replace('/[\r\n\t\x00-\x1F\x7F]+/u', '', $code));

        if ($code === '') {
            return '';
        }

        // Handle JSON payloads (e.g. from QR codes: {"id":"...", ...} or {"school_id_number":"..."})
        if ((str_starts_with($code, '{') && str_ends_with($code, '}')) || (str_starts_with($code, '[') && str_ends_with($code, ']'))) {
            $decoded = json_decode($code, true);
            if (is_array($decoded)) {
                $candidate = $decoded['school_id_number']
                    ?? $decoded['student_id']
                    ?? $decoded['accession_number']
                    ?? $decoded['code']
                    ?? $decoded['isbn']
                    ?? $decoded['value']
                    ?? $decoded['id']
                    ?? null;
                if ($candidate && (is_string($candidate) || is_numeric($candidate))) {
                    $code = trim((string) $candidate);
                }
            }
        }

        // Handle URLs (e.g. OPAC book details: http://.../opac/book/detail/9780132350884 or .../student/2023-0001)
        if (filter_var($code, FILTER_VALIDATE_URL) || preg_match('#^https?://#i', $code)) {
            $path = parse_url($code, PHP_URL_PATH);
            if ($path) {
                $segments = array_values(array_filter(explode('/', trim($path, '/'))));
                if (!empty($segments)) {
                    $code = end($segments);
                }
            }
        }

        // Strip surrounding quotes or delimiters
        $code = trim($code, "\"'` \t\n\r");

        return $code;
    }

    protected function findStudent(string $code): ?Student
    {
        if (empty($code)) return null;

        $lowerCode = strtolower($code);
        $cleanAlnum = preg_replace('/[^a-zA-Z0-9]/', '', $code);

        // 1. Direct match or case-insensitive on school_id_number
        $student = Student::with(['libraryStatus', 'account'])
            ->where(function ($q) use ($code, $lowerCode, $cleanAlnum) {
                $q->where('school_id_number', $code)
                  ->orWhereRaw('LOWER(school_id_number) = ?', [$lowerCode]);

                if (!empty($cleanAlnum)) {
                    $q->orWhereRaw("LOWER(REPLACE(REPLACE(school_id_number, '-', ''), ' ', '')) = ?", [strtolower($cleanAlnum)]);
                }
            })
            ->first();

        if ($student) {
            return $student;
        }

        // 2. Primary key ID match (if numeric)
        if (is_numeric($code)) {
            $student = Student::with(['libraryStatus', 'account'])->find((int) $code);
            if ($student) {
                return $student;
            }
        }

        // 3. Match via linked Account (username or email)
        $student = Student::with(['libraryStatus', 'account'])
            ->whereHas('account', function ($q) use ($code, $lowerCode) {
                $q->where('username', $code)
                  ->orWhereRaw('LOWER(username) = ?', [$lowerCode])
                  ->orWhereRaw('LOWER(email) = ?', [$lowerCode]);
            })
            ->first();

        return $student;
    }

    protected function findBook(string $code): ?Book
    {
        if (empty($code)) return null;

        $lowerCode = strtolower($code);
        $cleanAlnum = preg_replace('/[^a-zA-Z0-9]/', '', $code);

        $query = Book::withTrashed()->with([
            'bookDetail.bookData.authors',
            'bookDetail.bookData.categories',
            'condition'
        ]);

        // 1. Match by accession_number or code column
        $book = (clone $query)->where(function ($q) use ($code, $lowerCode, $cleanAlnum) {
            $q->where('accession_number', $code)
              ->orWhere('code', $code)
              ->orWhereRaw('LOWER(accession_number) = ?', [$lowerCode])
              ->orWhereRaw('LOWER(code) = ?', [$lowerCode]);

            if (!empty($cleanAlnum)) {
                $q->orWhereRaw("LOWER(REPLACE(REPLACE(accession_number, '-', ''), ' ', '')) = ?", [strtolower($cleanAlnum)])
                  ->orWhereRaw("LOWER(REPLACE(REPLACE(code, '-', ''), ' ', '')) = ?", [strtolower($cleanAlnum)]);
            }
        })->first();

        if ($book) {
            return $book;
        }

        // 2. Match by direct physical copy primary key ID (if numeric)
        if (is_numeric($code)) {
            $book = (clone $query)->find((int) $code);
            if ($book) {
                return $book;
            }
        }

        // 3. Match by ISBN (barcode on physical book)
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $code);
        if (strlen($cleanIsbn) >= 8) {
            $bookDetail = BookDetail::whereRaw("LOWER(REPLACE(REPLACE(isbn, '-', ''), ' ', '')) = ?", [strtolower($cleanIsbn)])
                ->orWhereRaw("LOWER(isbn) = ?", [$lowerCode])
                ->first();

            if ($bookDetail) {
                // Check for available copy first
                $available = (clone $query)->where('book_detail_id', $bookDetail->id)
                    ->where('status', 'available')
                    ->whereNull('deleted_at')
                    ->first();
                if ($available) {
                    return $available;
                }

                // Fallback to any copy under this detail so the user gets proper status feedback
                $anyCopy = (clone $query)->where('book_detail_id', $bookDetail->id)->first();
                if ($anyCopy) {
                    return $anyCopy;
                }
            }
        }

        // 4. Match by Call Number
        $bookDetail = BookDetail::whereRaw('LOWER(call_number) = ?', [$lowerCode])->first();
        if ($bookDetail) {
            $copy = (clone $query)->where('book_detail_id', $bookDetail->id)
                ->where('status', 'available')
                ->whereNull('deleted_at')
                ->first() ?? (clone $query)->where('book_detail_id', $bookDetail->id)->first();

            if ($copy) {
                return $copy;
            }
        }

        return null;
    }

    #[On('search-code')]
    public function handleSearchCode($code)
    {
        $this->errorMessage = ''; // Clear previous error

        $code = $this->normalizeCode($code);
        if (empty($code)) return;

        // 1. Check if the code matches a member ID (Student table)
        $student = $this->findStudent($code);

        if ($student) {
            if ($this->scannedMember && !empty($this->checkoutBooks) && $this->scannedMember['id'] !== $student->id) {
                $this->pendingStudentId = $student->id;
                $this->pendingStudentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
                $this->showConfirmChangeMember = true;
                $this->dispatch('clear-search-input');
                return;
            }
            $this->loadMember($student);
            $this->dispatch('clear-search-input');
            return;
        }

        // 2. Check if the code matches a book accession number, code, or ISBN
        $book = $this->findBook($code);

        if ($book) {
            $this->processBookCheckout($book);
            $this->dispatch('clear-search-input');
            return;
        }

        // Fallback error
        $this->errorMessage = 'No student profile or book copy found matching code: "' . $code . '"';
        $this->dispatch('clear-search-input');
    }

    public function loadMember($student)
    {
        $this->errorMessage = '';
        $statusName = $student->libraryStatus ? $student->libraryStatus->status : 'Active';

        $this->scannedMember = [
            'id' => $student->id,
            'name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'school_id' => $student->school_id_number,
            'course' => trim(($student->program ?? '') . ' - ' . ($student->year_level ?? '')),
            'status' => $statusName,
            'email' => $student->account ? $student->account->email : 'N/A',
            'contact' => $student->contact_num ?? 'N/A'
        ];

        $this->checkoutBooks = []; // Reset currently scanned book queue for new member
        $this->lastScannedBook = null;
        $this->updateStats();

        // Proactively set warnings for the librarian to address eligibility issues
        if (strtolower($statusName) !== 'active') {
            $this->errorMessage = 'Warning: This student profile is currently blocked or inactive. Borrowing is suspended.';
        } elseif ($this->stats['overdue_count'] > 0) {
            $this->errorMessage = 'Warning: Student has ' . $this->stats['overdue_count'] . ' overdue book(s) on their account. Return overdue items first.';
        } elseif ($this->stats['unpaid_fines'] > 0) {
            $this->errorMessage = 'Warning: Student has ₱' . number_format($this->stats['unpaid_fines'], 2) . ' outstanding unpaid fines.';
        } elseif ($this->stats['borrow_limit_reached']) {
            $this->errorMessage = 'Warning: Student has reached the maximum borrowing capacity (3 books active).';
        }
    }

    public function processBookCheckout($book)
    {
        $this->errorMessage = '';

        // Get book metadata relations
        $detail = $book->bookDetail;
        $data = $detail ? $detail->bookData : null;
        $authorName = 'Unknown Author';
        if ($data && $data->authors->isNotEmpty()) {
            $authorName = $data->authors->map(function($a) {
                return trim($a->first_name . ' ' . $a->last_name);
            })->implode(', ');
        }

        $categoryName = 'General';
        if ($data && $data->categories && $data->categories->isNotEmpty()) {
            $categoryName = $data->categories->first()->name;
        }

        $bookItem = [
            'book_id' => $book->id,
            'book' => $data ? $data->book_title : 'Unknown Book',
            'title' => $data ? $data->book_title : 'Unknown Book',
            'author' => $authorName,
            'category' => $categoryName,
            'isbn' => $detail ? ($detail->isbn ?: 'N/A') : 'N/A',
            'cover_url' => $detail ? $detail->cover_url : null,
            'accession' => $book->accession_number,
            'call_number' => $detail ? $detail->call_number : 'N/A',
            'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
            'added_on' => Carbon::now()->format('M. d, Y'),
            'due_date' => Carbon::now()->addDays((int) config('settings.library_config.loan_period', config('settings.loan_period', 5)))->format('M. d, Y')
        ];

        // Always show the book in the preview card
        $this->lastScannedBook = $bookItem;

        if (!$this->scannedMember) {
            $this->errorMessage = 'Please scan or load a student profile first before checking out books.';
            return;
        }

        // Validation 0: Book copy deleted/archived check
        if ($book->trashed()) {
            $this->errorMessage = 'Cannot add book: This copy has been archived or deleted.';
            return;
        }

        // Validation 1: Student Status Check
        if (strtolower($this->scannedMember['status']) !== 'active') {
            $this->errorMessage = 'Cannot add book: Student profile is currently inactive or blocked.';
            return;
        }

        // Validation 2: Overdue Books Block
        if ($this->stats['overdue_count'] > 0) {
            $this->errorMessage = 'Cannot add book: Student has overdue items that must be settled first.';
            return;
        }

        // Validation 3: Book copy status check
        if (strtolower($book->status) !== 'available') {
            $this->errorMessage = 'Cannot add book: This copy is currently not available (Status: ' . ucfirst($book->status) . ').';
            return;
        }

        // Validation 4: Book damage status check
        if (in_array($book->book_condition_id, [4, 5])) {
            $this->errorMessage = 'Cannot add book: This copy is marked as ' . ($book->condition ? $book->condition->status : 'damaged/lost') . '.';
            return;
        }

        // Validation 5: Double checkout scanning check
        foreach ($this->checkoutBooks as $item) {
            if ($item['book_id'] === $book->id) {
                $this->errorMessage = 'Book "' . $book->accession_number . '" is already in the checkout queue.';
                return;
            }
        }

        // Validation 6: Borrow slots limit capacity check
        $activeBorrowedCount = BorrowingTransaction::where('school_id', $this->scannedMember['id'])
            ->whereNull('return_date')
            ->count();
        $totalQueued = count($this->checkoutBooks);
        $maxCapacity = (int) config('settings.library_config.max_loan_limit', 3);
        if (($activeBorrowedCount + $totalQueued) >= $maxCapacity) {
            $this->errorMessage = "Cannot add book: Max borrowing capacity reached (limit is {$maxCapacity} active loans).";
            return;
        }

        $this->checkoutBooks[] = $bookItem;

        $this->updateStats();
    }

    public function confirmChangeMember()
    {
        $this->showConfirmChangeMember = false;
        if ($this->pendingStudentId) {
            $student = Student::with('libraryStatus')->find($this->pendingStudentId);
            if ($student) {
                $this->loadMember($student);
            }
        }
        $this->pendingStudentId = null;
        $this->pendingStudentName = '';
    }

    public function cancelChangeMember()
    {
        $this->showConfirmChangeMember = false;
        $this->pendingStudentId = null;
        $this->pendingStudentName = '';
    }

    public function clearMember()
    {
        $this->scannedMember = null;
        $this->checkoutBooks = [];
        $this->lastScannedBook = null;
        $this->errorMessage = '';
        $this->updateStats();
    }

    #[On('remove-checkout-book')]
    public function removeCheckoutBook($accession)
    {
        $this->checkoutBooks = array_filter($this->checkoutBooks, function ($item) use ($accession) {
            return $item['accession'] !== $accession;
        });
        $this->checkoutBooks = array_values($this->checkoutBooks); // reset indices

        if ($this->lastScannedBook && ($this->lastScannedBook['accession'] ?? null) === $accession) {
            $this->lastScannedBook = !empty($this->checkoutBooks) ? end($this->checkoutBooks) : null;
        }

        $this->updateStats();
    }

    #[On('add-checkout-book')]
    public function addCheckoutBook($accession)
    {
        $this->handleSearchCode($accession);
    }

    #[On('select-table-book')]
    public function selectTableBook($accession)
    {
        $this->errorMessage = '';
        $accession = trim($accession ?? '');
        if (empty($accession) || $accession === 'N/A') return;

        $item = collect($this->checkoutBooks)->firstWhere('accession', $accession);
        if ($item) {
            $this->lastScannedBook = $item;
        }
    }

    public function confirmCheckout()
    {
        if (!$this->scannedMember || empty($this->checkoutBooks)) {
            $this->errorMessage = 'No student loaded or checkout books queued.';
            return;
        }

        $studentId = $this->scannedMember['id'];

        // Backend validation safeguard: Check status again
        $student = Student::with('libraryStatus')->find($studentId);
        if (!$student) {
            $this->dispatch('checkout-failed', message: 'Student profile not found in database.');
            return;
        }

        $statusName = $student->libraryStatus ? $student->libraryStatus->status : 'Active';
        if (strtolower($statusName) !== 'active') {
            $this->dispatch('checkout-failed', message: 'Borrowing transaction blocked: Student profile status is inactive or blocked.');
            return;
        }

        // Verify overdue count again
        $overdueCount = BorrowingTransaction::where('school_id', $studentId)
            ->whereNull('return_date')
            ->where('due_date', '<', Carbon::now())
            ->count();
        if ($overdueCount > 0) {
            $this->dispatch('checkout-failed', message: 'Borrowing transaction blocked: Student has overdue items.');
            return;
        }

        // Verify unpaid fines again
        $unpaidFines = Fine::where('student_id', $studentId)
            ->where('fine_status', 'unpaid')
            ->sum('amount');
        if ($unpaidFines > 0) {
            $this->dispatch('checkout-failed', message: 'Borrowing transaction blocked: Student has outstanding unpaid fines of ₱' . number_format($unpaidFines, 2) . '.');
            return;
        }

        // Verify book availability again (Concurrent checkout check)
        $bookIds = collect($this->checkoutBooks)->pluck('book_id')->toArray();
        $unavailableBooks = Book::whereIn('id', $bookIds)
            ->where('status', '!=', 'available')
            ->pluck('accession_number')
            ->toArray();

        if (!empty($unavailableBooks)) {
            $this->dispatch('checkout-failed', message: 'Checkout failed: Book copy ' . implode(', ', $unavailableBooks) . ' is no longer available (concurrently checked out).');
            return;
        }

        try {
            DB::transaction(function () use ($studentId, $bookIds) {
                $loanPeriod = (int) config('settings.library_config.loan_period', config('settings.loan_period', 5));
                $now = Carbon::now();
                $dueDate = (clone $now)->addDays($loanPeriod);
                $librarianId = auth()->user() && auth()->user()->librarian ? auth()->user()->librarian->id : 1;

                foreach ($bookIds as $bookId) {
                    // Backend duplicate prevention: Check if transaction is already recorded
                    $exists = BorrowingTransaction::where('book_id', $bookId)
                        ->where('school_id', $studentId)
                        ->whereNull('return_date')
                        ->exists();

                    if ($exists) {
                        continue; // Skip inserting duplicate borrow transaction row
                    }

                    // 1. Insert Borrowing Transaction
                    BorrowingTransaction::create([
                        'book_id' => $bookId,
                        'school_id' => $studentId,
                        'issued_by_id' => $librarianId,
                        'borrow_type_id' => 1, // Regular Check-out
                        'issued_condition_id' => 2, // Good condition
                        'issued_date' => $now,
                        'due_date' => $dueDate,
                        'return_date' => null,
                    ]);

                    // 2. Update physical book copy status to borrowed
                    Book::where('id', $bookId)->update([
                        'status' => 'borrowed'
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Check-Out Transaction Failed: ' . $e->getMessage());
            $this->dispatch('checkout-failed', message: 'A database error occurred during checkout processing. Please try again.');
            return;
        }

        // Clear workstation queue state
        $this->checkoutBooks = [];
        $this->lastScannedBook = null;
        $this->scannedMember = null;
        $this->updateStats();

        session()->flash('success_message', 'Books successfully checked out!');

        // Dispatch checkout-completed event to show the frontend countdown success modal
        $this->dispatch('checkout-completed', redirectUrl: route('admin.circulation-desk.index'));
    }

    public function render()
    {
        return view('livewire.pages.dashboard.check-out-book');
    }
}
