<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Notifications\Alerts;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookReservation;
use App\Models\BorrowingTransaction;
use App\Models\UserHistory;
use App\Models\Fine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('livewire.layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Borrow Items', 'activepageRoute' => 'admin.circulation-desk.index'])]
class CheckOutBook extends Component
{
    public $scannedMember = null;
    public $checkoutBooks = [];
    public string $loanLocation = 'take_home';
    public $lastScannedBook = null;
    public $errorMessage = '';
    public $noticeMessage = '';
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
        \App\Services\EmployeeRoleService::authorizeBorrow('Unauthorized action. You do not have permission to process item borrowings.');

        $this->updateStats();

        $reservationId = request()->query('reservation');
        if ($reservationId !== null) {
            if (is_scalar($reservationId) && ctype_digit((string) $reservationId)) {
                $this->handleSearchCode('RES-' . $reservationId);
            } else {
                $this->errorMessage = 'The reservation could not be found. Search for it to continue.';
            }
        }
    }

    public function getQrConfig()
    {
        return config('settings', []);
    }

    public function updateStats()
    {
        $loanPeriod = (int) config('settings.library_config.loan_period', config('settings.loan_period', 5));
        $this->stats['total'] = count($this->checkoutBooks);
        $this->stats['due_date'] = $this->loanLocation === 'inside_library'
            ? now('Asia/Manila')->setTime(19, 0)->format('M. d, Y h:i A')
            : Carbon::now()->addDays($loanPeriod)->format('M. d, Y');

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

    public function updatedLoanLocation(): void
    {
        $this->updateStats();
        foreach ($this->checkoutBooks as &$item) {
            $issue = Book::find($item['book_id'])?->bookDetail?->periodicalIssue;
            $item['due_date'] = $this->loanLocation === 'inside_library'
                ? now('Asia/Manila')->setTime(19, 0)->format('M. d, Y h:i A')
                : now()->addDays($issue?->loan_days ?? (int) config('settings.library_config.loan_period', 5))->format('M. d, Y');
        }
        unset($item);
        if ($this->lastScannedBook) {
            $this->lastScannedBook = collect($this->checkoutBooks)->firstWhere('book_id', $this->lastScannedBook['book_id']) ?? $this->lastScannedBook;
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
            ->whereNull('deleted_at')
            ->whereHas('account', fn ($q) => $q->whereNull('deleted_at'))
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
            $student = Student::with(['libraryStatus', 'account'])
                ->whereNull('deleted_at')
                ->whereHas('account', fn ($q) => $q->whereNull('deleted_at'))
                ->find((int) $code);
            if ($student) {
                return $student;
            }
        }

        // 3. Match via linked Account (username or email)
        $student = Student::with(['libraryStatus', 'account'])
            ->whereNull('deleted_at')
            ->whereHas('account', function ($q) use ($code, $lowerCode) {
                $q->whereNull('deleted_at')
                  ->where(function ($sub) use ($code, $lowerCode) {
                      $sub->where('username', $code)
                          ->orWhereRaw('LOWER(username) = ?', [$lowerCode])
                          ->orWhereRaw('LOWER(email) = ?', [$lowerCode]);
                  });
            })
            ->first();

        return $student;
    }

    protected function findBook(string $code): ?Book
    {
        if (empty($code)) return null;

        $lowerCode = strtolower($code);
        $cleanAlnum = preg_replace('/[^a-zA-Z0-9]/', '', $code);

        $query = Book::with([
            'bookDetail.bookData.authors',
            'bookDetail.bookData.categories',
            'condition'
        ])->whereNull('deleted_at')
          ->whereHas('bookDetail', fn ($bd) => $bd->whereNull('deleted_at')->whereHas('bookData', fn ($b) => $b->whereNull('deleted_at')));

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

    protected function findReservation(string $code): ?BookReservation
    {
        $reference = strtoupper(preg_replace('/\s+/', '', $code));
        if (!preg_match('/^(?:#?RES-|PGPC-RES-?)(\d+)(?:-[A-Z0-9-]+)?$/', $reference, $matches)) {
            return null;
        }

        return BookReservation::with([
            'student.libraryStatus',
            'student.account',
            'book.bookDetail.bookData.authors',
            'book.bookDetail.bookData.categories',
            'book.condition',
        ])->find((int) $matches[1]);
    }

    protected function processReservationCheckout(BookReservation $reservation): void
    {
        if ((int) $reservation->reservation_status_id !== 2) {
            $this->errorMessage = 'Reservation #' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT) . ' is not approved for pickup.';
            return;
        }

        if (!$reservation->student || !$reservation->book) {
            $this->errorMessage = 'This reservation is missing its student or physical book copy.';
            return;
        }

        if ($reservation->due_date && $reservation->due_date->isPast()) {
            $this->errorMessage = 'This reservation has passed its pickup deadline and cannot be issued.';
            return;
        }

        if ($this->scannedMember && !empty($this->checkoutBooks) && $this->scannedMember['id'] !== $reservation->student_id) {
            $this->errorMessage = 'Clear the current checkout queue before loading a reservation for another student.';
            return;
        }

        if (!$this->scannedMember || $this->scannedMember['id'] !== $reservation->student_id) {
            $this->loadMember($reservation->student);
        }

        $this->processBookCheckout($reservation->book, $reservation);

        if ($this->errorMessage === '') {
            $this->noticeMessage = 'Reservation #' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT) . ' loaded for ' . $this->scannedMember['name'] . '.';
        }
    }
    #[On('search-code')]
    public function handleSearchCode($code)
    {
        $this->errorMessage = '';
        $this->noticeMessage = '';

        $code = $this->normalizeCode($code);
        if (empty($code)) return;

        // 1. Check if the code is an approved reservation reference.
        $reservation = $this->findReservation($code);
        if ($reservation) {
            $this->processReservationCheckout($reservation);
            $this->dispatch('clear-search-input');
            return;
        }
        if (preg_match('/^(?:#?RES-|PGPC-RES-?)/i', $code)) {
            $this->errorMessage = 'The reservation could not be found. Search for it to continue.';
            return;
        }
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
        if ($student->account && method_exists($student->account, 'isApprovedStudent') && ! $student->account->isApprovedStudent()) {
            $statusName = 'Pending Verification';
        }

        $this->scannedMember = [
            'id' => $student->id,
            'name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'school_id' => $student->school_id_number,
            'course' => trim(($student->program ?? '') . ' - ' . ($student->year_level ?? '')),
            'status' => $statusName,
            'reservations_blocked' => (bool) $student->account?->reservations_blocked,
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

    public function processBookCheckout($book, ?BookReservation $reservation = null)
    {
        $this->errorMessage = '';

        // Get book metadata relations
        $detail = $book->bookDetail;
        $data = $detail ? $detail->bookData : null;
        if ($detail?->periodicalIssue && ! $detail->periodicalIssue->borrowable && $this->loanLocation !== 'inside_library') {
            $this->errorMessage = 'This issue is for library use only. Select inside-library use.';
            return;
        }
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
            'item_type' => $detail?->catalog_kind ?: 'book',
            'author' => $authorName,
            'category' => $categoryName,
            'isbn' => $detail ? ($detail->isbn ?: 'N/A') : 'N/A',
            'cover_url' => $detail ? $detail->cover_url : null,
            'accession' => $book->accession_number,
            'call_number' => $detail ? $detail->call_number : 'N/A',
            'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
            'added_on' => Carbon::now()->format('M. d, Y'),
            'due_date' => $this->loanLocation === 'inside_library'
                ? now('Asia/Manila')->setTime(19, 0)->format('M. d, Y h:i A')
                : Carbon::now()->addDays($detail?->periodicalIssue?->loan_days ?? (int) config('settings.library_config.loan_period', config('settings.loan_period', 5)))->format('M. d, Y'),
            'reservation_id' => $reservation?->id
        ];

        // Always show the book in the preview card
        $this->lastScannedBook = $bookItem;

        if (!$this->scannedMember) {
            $this->errorMessage = 'Please scan or load a student profile first before borrowing items.';
            return;
        }

        // Validation 0: Book copy deleted/archived check
        if ($book->trashed()) {
            $this->errorMessage = 'Cannot add item: This copy has been archived or deleted.';
            return;
        }

        // Validation 1: Student Status Check
        if (strtolower($this->scannedMember['status']) !== 'active') {
            $this->errorMessage = 'Cannot add item: Student profile is currently inactive or blocked.';
            return;
        }

        // Validation 2: Overdue Books Block
        if ($this->stats['overdue_count'] > 0) {
            $this->errorMessage = 'Cannot add item: Student has overdue items that must be settled first.';
            return;
        }

        // Validation 3: Book copy status check
        $isReservedForThisCheckout = $reservation && $reservation->book_id === $book->id && $reservation->student_id === $this->scannedMember['id'];
        if (strtolower($book->status) !== 'available' && !($isReservedForThisCheckout && strtolower($book->status) === 'reserved')) {
            $this->errorMessage = 'Cannot add item: This copy is currently not available (Status: ' . ucfirst($book->status) . ').';
            return;
        }

        // Validation 4: Book damage status check
        if (in_array($book->book_condition_id, [4, 5])) {
            $this->errorMessage = 'Cannot add item: This copy is marked as ' . ($book->condition ? $book->condition->status : 'damaged/lost') . '.';
            return;
        }

        // Validation 5: Double checkout scanning check
        foreach ($this->checkoutBooks as $item) {
            if ($item['book_id'] === $book->id) {
                $this->errorMessage = 'Item "' . $book->accession_number . '" is already in the borrow queue.';
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
            $this->errorMessage = "Cannot add item: Max borrowing capacity reached (limit is {$maxCapacity} active loans).";
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
        \App\Services\EmployeeRoleService::authorizeBorrow('Unauthorized action. You do not have permission to process item borrowings.');
        if (! in_array($this->loanLocation, ['take_home', 'inside_library'], true)) {
            $this->dispatch('checkout-failed', message: 'Please select a valid loan type.');
            return;
        }
        if ($this->loanLocation === 'inside_library' && now('Asia/Manila')->hour >= 19) {
            $this->dispatch('checkout-failed', message: 'Inside-library loans cannot be issued at or after 7:00 PM.');
            return;
        }
        if (!$this->scannedMember || empty($this->checkoutBooks)) {
            $this->errorMessage = 'No student loaded or checkout books queued.';
            return;
        }

        $studentId = $this->scannedMember['id'];

        // Backend validation safeguard: Check status again
        $student = Student::with(['libraryStatus', 'account'])->find($studentId);
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

        // Verify each queued copy, allowing a reserved copy only for its linked reservation.
        $bookIds = collect($this->checkoutBooks)->pluck('book_id')->toArray();
        foreach ($this->checkoutBooks as $item) {
            $book = Book::find($item['book_id']);
            $isReservationCopy = !empty($item['reservation_id']) && $book?->status === 'reserved';
            if (!$book || ($book->status !== 'available' && !$isReservationCopy)) {
                $this->dispatch('checkout-failed', message: 'Checkout failed: A queued book copy is no longer available.');
                return;
            }
        }
        try {
            $borrowedItems = [];
            DB::transaction(function () use ($studentId, $bookIds, &$borrowedItems) {
                $checkoutStudent = Student::lockForUpdate()->findOrFail($studentId);
                $loanPeriod = (int) config('settings.library_config.loan_period', config('settings.loan_period', 5));
                $now = Carbon::now();
                $insideLibrary = $this->loanLocation === 'inside_library';
                $borrowType = \App\Models\BorrowType::firstOrCreate(['name' => $insideLibrary ? 'Room Use Only' : 'Regular Check-out']);
                $dueDate = (clone $now)->addDays($loanPeriod);
                $librarianId = auth()->user() && auth()->user()->librarian ? auth()->user()->librarian->id : 1;

                foreach ($this->checkoutBooks as $item) {
                    $bookId = $item['book_id'];
                    $book = Book::lockForUpdate()->findOrFail($bookId);
                    $reservationId = $item['reservation_id'] ?? null;
                    $issue = $book->bookDetail?->periodicalIssue;
                    if ($issue && ! $issue->borrowable && ! $insideLibrary) {
                        throw new \DomainException('This issue is for library use only. Select inside-library use.');
                    }
                    $dueDate = $insideLibrary
                        ? $now->copy()->setTimezone('Asia/Manila')->setTime(19, 0)->setTimezone(config('app.timezone'))
                        : (clone $now)->addDays($issue?->loan_days ?? $loanPeriod);

                    if ($reservationId) {
                        $reservation = BookReservation::lockForUpdate()->findOrFail($reservationId);
                        $isValidReservation = $reservation->student_id === $studentId
                            && $reservation->book_id === $bookId
                            && (int) $reservation->reservation_status_id === 2
                            && (!$reservation->due_date || !$reservation->due_date->isPast());

                        if (!$isValidReservation || $book->status !== 'reserved') {
                            throw new \RuntimeException('The linked reservation is no longer available for checkout.');
                        }
                    } elseif ($book->status !== 'available') {
                        throw new \RuntimeException('A queued book copy is no longer available.');
                    }

                    $exists = BorrowingTransaction::where('book_id', $bookId)
                        ->whereNull('return_date')
                        ->exists();

                    if ($exists) {
                        throw new \DomainException('A queued copy already has an active loan. Check the copy’s loan record before trying again.');
                    }

                    $transaction = BorrowingTransaction::create([
                        'book_id' => $bookId,
                        'school_id' => $studentId,
                        'program_at_issue' => $checkoutStudent->program,
                        'year_level_at_issue' => $checkoutStudent->year_level,
                        'issued_by_id' => $librarianId,
                        'book_reservation_id' => $reservationId,
                        'borrow_type_id' => $borrowType->id,
                        'issued_condition_id' => 2,
                        'issued_date' => $now,
                        'due_date' => $dueDate,
                        'return_date' => null,
                    ]);
                    $isbn = trim((string) $book->bookDetail?->isbn);
                    if (! $insideLibrary && $isbn !== '') {
                        UserHistory::upsertHighestRating(
                            (int) $checkoutStudent->account_id,
                            $isbn,
                            UserHistory::RATING_BORROWED
                        );
                    }
                    $borrowedItems[] = [
                        'title' => $item['title'] ?? $item['book'] ?? 'Borrowed book',
                        'was_reserved' => (bool) $reservationId,
                        'due_date' => $dueDate->copy()->setTimezone('Asia/Manila')->format($insideLibrary ? 'M d, Y h:i A' : 'M d, Y'),
                        'details' => [
                            'Loan reference' => 'LOAN-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
                            'Accession number' => $item['accession'] ?? null,
                            'Call number' => $item['call_number'] ?? null,
                            'ISBN' => $item['isbn'] ?? null,
                            'Author' => $item['author'] ?? null,
                            'Borrowed on' => $now->copy()->setTimezone('Asia/Manila')->format('M d, Y'),
                            'Loan type' => $borrowType->name,
                            'Due date' => $dueDate->copy()->setTimezone('Asia/Manila')->format($insideLibrary ? 'M d, Y h:i A' : 'M d, Y'),
                            'Reservation status' => $reservationId ? 'Fulfilled (book borrowed)' : null,
                        ],
                    ];

                    if ($reservationId) {
                        BookReservation::whereKey($reservationId)->update([
                            'reservation_status_id' => 3,
                            'fulfilled_date' => $now,
                        ]);
                    }

                    $book->update(['status' => 'borrowed']);
                }
            });
        } catch (\DomainException $e) {
            $this->dispatch('checkout-failed', message: $e->getMessage());
            return;
        } catch (\Throwable $e) {
            Log::error('Check-Out Transaction Failed: ' . $e->getMessage());
            $this->dispatch('checkout-failed', message: 'A database error occurred during checkout processing. Please try again.');
            return;
        }

        if ($account = $student->account) {
            foreach ($borrowedItems as $item) {
                try {
                    $account->notify((new Alerts(
                        title: 'Book borrowed',
                        message: $item['was_reserved'] ? 'Your reserved book has been borrowed and the reservation is fulfilled. Please return it by ' . $item['due_date'] . '.' : 'Your book has been checked out. Please return it by ' . $item['due_date'] . '.',
                        type: 'success',
                        actionUrl: \App\Support\EmailUrl::route('student.borrow'),
                        actionText: 'View loans',
                        bookTitle: $item['title'],
                        dueDate: $item['due_date'],
                        channels: ['database', 'mail'],
                        sendSms: true,
                        smsMessage: 'PGPC Library: ' . ($item['was_reserved'] ? 'Your reservation is fulfilled; ' : '') . $item['title'] . ' was borrowed. Return by ' . $item['due_date'] . '.',
                        details: $item['details'],
                    ))->onConnection('database')->afterCommit());
                } catch (\Throwable $e) {
                    Log::warning('Could not queue checkout notification: ' . $e->getMessage());
                }
            }
        }

        // Clear workstation queue state
        $this->checkoutBooks = [];
        $this->lastScannedBook = null;
        $this->scannedMember = null;
        $this->updateStats();

        session()->flash('success_message', 'Items successfully borrowed!');

        // Dispatch checkout-completed event to show the frontend countdown success modal
        $prefix = \App\Services\EmployeeRoleService::getRoutePrefix();
        $this->dispatch('checkout-completed', redirectUrl: route($prefix . 'circulation-desk.index'));
    }

    public function render()
    {
        return view('livewire.pages.dashboard.check-out-book');
    }
}
