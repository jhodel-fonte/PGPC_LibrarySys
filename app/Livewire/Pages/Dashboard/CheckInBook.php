<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BorrowingTransaction;
use Carbon\Carbon;

#[Layout('livewire.layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Return Items', 'activepageRoute' => 'admin.circulation-desk.index'])]
class CheckInBook extends Component
{
    public $scannedMember = null;
    public $borrowedBooks = [];
    public $returnedBooks = [];
    public $lastReturnedBook = null;
    public $errorMessage = '';
    public $showConfirmChangeMember = false;
    public $pendingStudentId = null;
    public $pendingStudentName = '';
    public $stats = [
        'total' => 0,
        'returned' => 0,
        'remaining' => 0,
        'overdue' => 0,
        'total_fine' => 0.00,
        'return_date' => ''
    ];

    public function mount()
    {
        \App\Services\EmployeeRoleService::authorizeReturn('Unauthorized action. You do not have permission to process item returns.');

        $this->updateStats();
    }

    public function getQrConfig()
    {
        return config('settings', []);
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

    protected function findBook(string $code, array $preferredBookIds = []): ?Book
    {
        if (empty($code)) return null;

        $lowerCode = strtolower($code);
        $cleanAlnum = preg_replace('/[^a-zA-Z0-9]/', '', $code);

        $query = Book::withTrashed()->with([
            'bookDetail' => fn ($q) => $q->withTrashed(),
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
            $bookDetail = BookDetail::withTrashed()->whereRaw("LOWER(REPLACE(REPLACE(isbn, '-', ''), ' ', '')) = ?", [strtolower($cleanIsbn)])
                ->orWhereRaw("LOWER(isbn) = ?", [$lowerCode])
                ->first();

            if ($bookDetail) {
                // If preferred book IDs provided (e.g. books currently checked out by student), match one of those first
                if (!empty($preferredBookIds)) {
                    $preferred = (clone $query)->where('book_detail_id', $bookDetail->id)
                        ->whereIn('id', $preferredBookIds)
                        ->first();
                    if ($preferred) {
                        return $preferred;
                    }
                }

                // Check for borrowed or existing copy
                $copy = (clone $query)->where('book_detail_id', $bookDetail->id)->first();
                if ($copy) {
                    return $copy;
                }
            }
        }

        // 4. Match by Call Number
        $bookDetail = BookDetail::withTrashed()->whereRaw('LOWER(call_number) = ?', [$lowerCode])->first();
        if ($bookDetail) {
            if (!empty($preferredBookIds)) {
                $preferred = (clone $query)->where('book_detail_id', $bookDetail->id)
                    ->whereIn('id', $preferredBookIds)
                    ->first();
                if ($preferred) {
                    return $preferred;
                }
            }

            $copy = (clone $query)->where('book_detail_id', $bookDetail->id)->first();
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
            if ($this->scannedMember && !empty($this->returnedBooks) && $this->scannedMember['id'] !== $student->id) {
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
        $borrowedIds = array_filter(array_column($this->borrowedBooks, 'book_id'));
        $book = $this->findBook($code, $borrowedIds);

        if ($book) {
            $this->processBookReturn($book);
            $this->dispatch('clear-search-input');
            return;
        }

        // 3. Fallback: If it matches a member ID format but wasn't found in DB,
        // we still display their information on the side card as "Unregistered Member"
        $isMemberFormat = false;
        $config = $this->getQrConfig();
        if (!empty($config['accepted_formats'])) {
            $memberConf = $config['accepted_formats']['member'] ?? [];
            if (!empty($memberConf['patterns'])) {
                foreach ($memberConf['patterns'] as $pattern) {
                    if (@preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $code)) {
                        $isMemberFormat = true;
                        break;
                    }
                }
            }
        } else {
            $isMemberFormat = preg_match('/^(?:LIB-|SA-|20\d{2}-)/i', $code);
        }

        if ($isMemberFormat) {
            if ($this->scannedMember && !empty($this->returnedBooks)) {
                $this->pendingStudentId = 'unregistered';
                $this->pendingStudentName = "Unregistered Member ($code)";
                $this->showConfirmChangeMember = true;
                $this->dispatch('clear-search-input');
                return;
            }
            $this->scannedMember = [
                'id' => null,
                'name' => 'Unregistered Member',
                'school_id' => $code,
                'course' => 'Not Registered',
                'status' => 'Inactive'
            ];
            $this->borrowedBooks = [];
            $this->updateStats();
            $this->errorMessage = 'This member ID is not registered in the database.';
            $this->dispatch('clear-search-input');
            return;
        }

        // Set standard error message for unrecognized inputs
        $this->errorMessage = 'No student profile or borrowed book found matching code: "' . $code . '"';
        $this->dispatch('clear-search-input');
    }

    public function loadMember($student)
    {
        $this->errorMessage = '';
        $this->scannedMember = [
            'id' => $student->id,
            'name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'school_id' => $student->school_id_number,
            'course' => trim(($student->program ?? '') . ' - ' . ($student->year_level ?? '')),
            'status' => $student->libraryStatus ? $student->libraryStatus->status : 'Active'
        ];

        // Fetch their currently borrowed books (not yet returned)
        $transactions = BorrowingTransaction::with([
                'book' => fn ($q) => $q->withTrashed(),
                'book.bookDetail' => fn ($q) => $q->withTrashed(),
                'book.bookDetail.bookData',
                'book.bookDetail.bookData.authors',
                'book.bookDetail.bookData.categories',
            ])
            ->where('school_id', $student->id)
            ->whereNull('return_date')
            ->get();

        $this->borrowedBooks = $transactions->map(function ($t) {
            $book = $t->book;
            $detail = $book ? $book->bookDetail : null;
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

            $isPendingReturn = $book ? in_array($book->id, $this->returnedBooks) : false;

            // Check if overdue
            $isOverdue = false;
            $overdueDays = 0;
            if ($t->due_date && Carbon::now()->isAfter($t->due_date)) {
                $isOverdue = true;
                $overdueDays = Carbon::now()->diffInDays($t->due_date);
            }

            return [
                'transaction_id' => $t->id,
                'book_id' => $book ? $book->id : null,
                'book' => $data ? $data->book_title : 'Unknown Book',
                'item_type' => $detail?->catalog_kind ?: 'book',
                'author' => $authorName,
                'category' => $categoryName,
                'isbn' => $detail ? ($detail->isbn ?: 'N/A') : 'N/A',
                'cover_url' => $detail ? $detail->cover_url : null,
                'accession' => $book ? $book->accession_number : 'N/A',
                'call_number' => $detail ? $detail->call_number : 'N/A',
                'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
                'borrowed_on' => $t->issued_date?->format('M d, Y') ?? 'N/A',
                'due_date' => $t->due_date?->format('M d, Y') ?? 'N/A',
                'status' => $isPendingReturn ? 'Returned' : ($isOverdue ? 'Overdue' : 'Borrowed'),
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
            ];
        })->toArray();

        $this->updateStats();
    }

    #[On('return-book')]
    public function returnBookByAccession($accession)
    {
        $this->errorMessage = '';
        $accession = $this->normalizeCode($accession ?? '');
        if (empty($accession) || $accession === 'N/A') return;

        $borrowedIds = array_filter(array_column($this->borrowedBooks, 'book_id'));
        $book = $this->findBook($accession, $borrowedIds);

        if ($book) {
            $this->processBookReturn($book);
        } else {
            $this->errorMessage = 'Item with code/accession "' . $accession . '" could not be found.';
        }
    }

    #[On('select-table-book')]
    public function selectTableBook($accession)
    {
        $this->errorMessage = '';
        $accession = trim($accession ?? '');
        if (empty($accession) || $accession === 'N/A') return;

        $item = collect($this->borrowedBooks)->firstWhere('accession', $accession);
        if ($item) {
            $isPendingReturn = !empty($item['book_id']) ? in_array($item['book_id'], $this->returnedBooks) : false;

            $this->lastReturnedBook = [
                'book_id' => $item['book_id'],
                'title' => $item['book'],
                'author' => $item['author'],
                'category' => $item['category'] ?? 'General',
                'isbn' => $item['isbn'] ?? 'N/A',
                'cover_url' => $item['cover_url'] ?? null,
                'accession' => $item['accession'],
                'call_number' => $item['call_number'] ?? 'N/A',
                'code' => $item['code'] ?? 'BOOK',
                'borrowed_on' => $item['borrowed_on'],
                'due_date' => $item['due_date'],
                'is_returned' => $isPendingReturn,
            ];
        }
    }

    public function processBookReturn($book)
    {
        $this->errorMessage = '';

        // Check if book is already in the pending returned books list
        if (in_array($book->id, $this->returnedBooks)) {
            $this->errorMessage = 'Item "' . $book->accession_number . '" is already scanned for return.';
            return;
        }

        // Find active borrow transaction for this book copy (globally, so return works regardless of who is loaded)
        $transaction = BorrowingTransaction::with([
                'student',
                'book' => fn ($q) => $q->withTrashed(),
                'book.bookDetail' => fn ($q) => $q->withTrashed(),
                'book.bookDetail.bookData',
                'book.bookDetail.bookData.authors',
                'book.bookDetail.bookData.categories',
            ])
            ->where('book_id', $book->id)
            ->whereNull('return_date')
            ->first();

        if (!$transaction) {
            $this->errorMessage = 'Item "' . $book->accession_number . '" is not currently marked as borrowed.';
            return;
        }

        // Auto load/switch member to the student who borrowed this book
        if (!$this->scannedMember) {
            if ($transaction->student) {
                $this->returnedBooks = []; // Reset queue for new student session
                $this->loadMember($transaction->student);
            }
        } else if ($this->scannedMember['id'] !== $transaction->school_id) {
            $this->errorMessage = 'This item was borrowed by another student.';
            return;
        }

        // Add to returned books in temporary queue
        $this->returnedBooks[] = $book->id;

        // Get details of the returned book
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

        $this->lastReturnedBook = [
            'book_id' => $book->id,
            'title' => $data ? $data->book_title : 'Unknown Book',
            'author' => $authorName,
            'category' => $categoryName,
            'isbn' => $detail ? ($detail->isbn ?: 'N/A') : 'N/A',
            'cover_url' => $detail ? $detail->cover_url : null,
            'accession' => $book->accession_number,
            'call_number' => $detail ? $detail->call_number : 'N/A',
            'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
            'borrowed_on' => $transaction->issued_date?->format('M d, Y') ?? 'N/A',
            'due_date' => $transaction->due_date?->format('M d, Y') ?? 'N/A',
            'is_returned' => true,
        ];

        // Re-load the member to update their borrowed books list
        $studentModel = Student::find($this->scannedMember['id']);
        if ($studentModel) {
            $this->loadMember($studentModel);
        }

        $this->dispatch('clear-search-input');
    }

    #[On('undo-return')]
    public function undoReturn($accession = null, $accessionNumber = null)
    {
        $this->errorMessage = '';
        $targetAccession = $this->normalizeCode($accession ?? $accessionNumber ?? '');
        if (empty($targetAccession)) return;

        $book = $this->findBook($targetAccession);
        if ($book) {
            // Update lastReturnedBook state if it matches
            if ($this->lastReturnedBook && ($this->lastReturnedBook['book_id'] === $book->id || ($this->lastReturnedBook['accession'] ?? '') === $book->accession_number)) {
                $this->lastReturnedBook['is_returned'] = false;
            }

            // Remove from temporary list
            $this->returnedBooks = array_values(array_diff($this->returnedBooks, [$book->id]));

            // Re-load member
            if ($this->scannedMember && !empty($this->scannedMember['id'])) {
                $studentModel = Student::find($this->scannedMember['id']);
                if ($studentModel) {
                    $this->loadMember($studentModel);
                }
            }
        }
    }

    public function reviewReturn()
    {
        \App\Services\EmployeeRoleService::authorizeReturn('Unauthorized action. You do not have permission to process item returns.');
        if (!$this->scannedMember || empty($this->returnedBooks)) {
            return;
        }

        // Get the transaction IDs corresponding to the book IDs in our returnedBooks queue
        $transactionIds = BorrowingTransaction::whereIn('book_id', $this->returnedBooks)
            ->where('school_id', $this->scannedMember['id'])
            ->whereNull('return_date')
            ->pluck('id')
            ->toArray();

        if (count($transactionIds) !== count(array_unique($this->returnedBooks))) {
            $this->errorMessage = 'Some selected items are no longer borrowed by this student. Refresh the student record and scan them again.';
            return;
        }

        // Save session data
        session([
            'confirm_return_student_id' => $this->scannedMember['id'],
            'confirm_return_transaction_ids' => $transactionIds
        ]);

        // Redirect to confirm return page
        $prefix = \App\Services\EmployeeRoleService::getRoutePrefix();
        return $this->redirect(route($prefix . 'circulation-desk.return.confirm'), navigate: true);
    }

    public function clearMember()
    {
        $this->errorMessage = '';
        $this->scannedMember = null;
        $this->borrowedBooks = [];
        $this->returnedBooks = [];
        $this->lastReturnedBook = null;
        $this->updateStats();
    }

    public function updateStats()
    {
        if (!$this->scannedMember) {
            $this->stats = [
                'total' => 0,
                'returned' => 0,
                'remaining' => 0,
                'overdue' => 0,
                'total_fine' => 0.00,
                'return_date' => Carbon::now()->format('M d, Y')
            ];
            return;
        }

        $studentId = $this->scannedMember['id'] ?? null;

        $totalBorrowed = 0;
        $overdueCount = 0;
        $totalFine = 0.00;

        if ($studentId) {
            // Count active borrowed books in database (which includes the ones in returnedBooks queue)
            $totalBorrowed = BorrowingTransaction::where('school_id', $studentId)
                ->whereNull('return_date')
                ->count();

            // Get overdue transactions in database
            $overdueTransactions = BorrowingTransaction::where('school_id', $studentId)
                ->whereNull('return_date')
                ->where('due_date', '<', Carbon::now())
                ->with(['book' => fn ($q) => $q->withTrashed(), 'book.bookDetail' => fn ($q) => $q->withTrashed()])
                ->get();

            // Count only those that are not yet scanned in returnedBooks queue
            foreach ($overdueTransactions as $ot) {
                if (!in_array($ot->book_id, $this->returnedBooks)) {
                    $overdueCount++;
                } else {
                    // For the books that ARE scanned, calculate their fine
                    $due = Carbon::parse($ot->due_date);
                    if ($due->isPast()) {
                        $totalFine += app(\App\Services\FineService::class)->overdueAmount($due, $ot->book?->bookDetail);
                    }
                }
            }
        }

        $returnedCount = count($this->returnedBooks);

        $this->stats = [
            'total' => $totalBorrowed,
            'returned' => $returnedCount,
            'remaining' => max(0, $totalBorrowed - $returnedCount),
            'overdue' => $overdueCount,
            'total_fine' => $totalFine,
            'return_date' => Carbon::now()->format('M d, Y')
        ];
    }

    // public function showAlert()
    // {
    //     $this->dispatch('open-modal',
    //         title: 'Delete Borrow Transaction',
    //         message: 'This action is permanent and cannot be undone.',
    //         type: 'warning',
    //         options: [
    //             'confirmButtonText' => 'Delete Now',
    //             'cancelButtonText' => 'Cancel',
    //             'confirmEvent' => 'delete-transaction-id',
    //             'confirmParams' => ['id' => 1]
    //         ]
    //     );
    // }

    public function confirmChangeMember()
    {
        $this->showConfirmChangeMember = false;
        $this->returnedBooks = []; // Reset queue for new student session
        $this->lastReturnedBook = null;

        if ($this->pendingStudentId === 'unregistered') {
            $code = str_replace('Unregistered Member (', '', $this->pendingStudentName);
            $code = rtrim($code, ')');

            $this->scannedMember = [
                'id' => null,
                'name' => 'Unregistered Member',
                'school_id' => $code,
                'course' => 'Not Registered',
                'status' => 'Inactive'
            ];
            $this->borrowedBooks = [];
            $this->updateStats();
            $this->errorMessage = 'This member ID is not registered in the database.';
        } else {
            $student = Student::find($this->pendingStudentId);
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
        $this->errorMessage = 'Change student operation was cancelled. Scanned returns preserved.';
    }

    public function render()
    {
        return view('livewire.pages.dashboard.check-in-book');
    }
}
