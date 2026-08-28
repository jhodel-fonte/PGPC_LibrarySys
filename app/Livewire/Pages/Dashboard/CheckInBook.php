<?php

namespace App\Livewire\Pages\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Student;
use App\Models\Book;
use App\Models\BorrowingTransaction;
use Carbon\Carbon;

#[Layout('layouts.admin', ['title' => 'Circulation Desk', 'subpage' => 'Check-In / Return', 'activepageRoute' => 'admin.circulation-desk.index'])]
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
        'return_date' => ''
    ];

    public function mount()
    {
        // Restore returning session state if librarian cancels/returns from confirmation page
        if (session()->has('confirm_return_student_id')) {
            $studentId = session('confirm_return_student_id');
            $transactionIds = session('confirm_return_transaction_ids', []);

            $student = Student::with('libraryStatus')->find($studentId);
            if ($student) {
                // Restore scannedMember details
                $this->scannedMember = [
                    'id' => $student->id,
                    'name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'school_id' => $student->school_id_number,
                    'course' => trim(($student->program ?? '') . ' - ' . ($student->year_level ?? '')),
                    'status' => $student->libraryStatus ? $student->libraryStatus->status : 'Active'
                ];

                // Restore returnedBooks from transaction book IDs
                $this->returnedBooks = BorrowingTransaction::whereIn('id', $transactionIds)
                    ->pluck('book_id')
                    ->toArray();

                // Fetch their currently borrowed books (not yet returned)
                $transactions = BorrowingTransaction::with(['book.bookDetail.bookData', 'book.bookDetail.bookData.authors'])
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

                    $isPendingReturn = in_array($book->id, $this->returnedBooks);

                    return [
                        'transaction_id' => $t->id,
                        'book_id' => $book ? $book->id : null,
                        'book' => $data ? $data->book_title : 'Unknown Book',
                        'author' => $authorName,
                        'accession' => $book ? $book->accession_number : 'N/A',
                        'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
                        'borrowed_on' => $t->issued_date->format('M d, Y'),
                        'due_date' => $t->due_date->format('M d, Y'),
                        'status' => $isPendingReturn ? 'Returned' : 'Borrowed'
                    ];
                })->toArray();
            }

            // Forget session state to prevent infinite restoration loops
            session()->forget(['confirm_return_student_id', 'confirm_return_transaction_ids']);
        }

        $this->updateStats();
    }

    public function getQrConfig()
    {
        return config('pgpc', []);
    }

    #[On('search-code')]
    public function handleSearchCode($code)
    {
        $this->errorMessage = ''; // Clear previous error

        $code = trim($code);
        if (empty($code)) return;

        // Security Hardening: Protect against XSS, script tags, and long buffer injection payloads
        $code = strip_tags($code);
        $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
        $code = substr($code, 0, 50); // Cap input length to prevent huge injection payloads

        // 1. Check if the code matches a member ID (Student table)
        $student = Student::with('libraryStatus')->where('school_id_number', $code)->first();

        if ($student) {
            // Check if we need to confirm switching student due to active returns session
            if ($this->scannedMember && $this->scannedMember['school_id'] !== $student->school_id_number && count($this->returnedBooks) > 0) {
                $this->showConfirmChangeMember = true;
                $this->pendingStudentId = $student->id;
                $this->pendingStudentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
                $this->dispatch('clear-search-input');
                return;
            }

            $this->loadMember($student);
            $this->dispatch('clear-search-input');
            return;
        }

        // 2. Check if the code matches a book (barcode or accession_number)
        $book = Book::where('barcode', $code)
            ->orWhere('accession_number', $code)
            ->first();

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
            $memberConf = $config['accepted_formats']['member'];
            if (!empty($memberConf['patterns'])) {
                foreach ($memberConf['patterns'] as $pattern) {
                    if (preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $code)) {
                        $isMemberFormat = true;
                        break;
                    }
                }
            }
        } else {
            $isMemberFormat = preg_match('/^(?:LIB-|SA-|20\d{2}-)/i', $code);
        }

        if ($isMemberFormat) {
            // Check if we need to confirm switching student
            if ($this->scannedMember && $this->scannedMember['school_id'] !== $code && count($this->returnedBooks) > 0) {
                $this->showConfirmChangeMember = true;
                $this->pendingStudentId = 'unregistered';
                $this->pendingStudentName = 'Unregistered Member (' . $code . ')';
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
            return;
        }

        // Set standard error message for unrecognized inputs
        $this->errorMessage = 'No student profile or borrowed book found matching code: "' . $code . '"';
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
        $transactions = BorrowingTransaction::with(['book.bookDetail.bookData', 'book.bookDetail.bookData.authors'])
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

            $isPendingReturn = in_array($book->id, $this->returnedBooks);

            return [
                'transaction_id' => $t->id,
                'book_id' => $book ? $book->id : null,
                'book' => $data ? $data->book_title : 'Unknown Book',
                'author' => $authorName,
                'accession' => $book ? $book->accession_number : 'N/A',
                'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
                'borrowed_on' => $t->issued_date->format('M d, Y'),
                'due_date' => $t->due_date->format('M d, Y'),
                'status' => $isPendingReturn ? 'Returned' : 'Borrowed'
            ];
        })->toArray();

        $this->updateStats();
    }

    public function processBookReturn($book)
    {
        $this->errorMessage = '';

        // Check if book is already in the pending returned books list
        if (in_array($book->id, $this->returnedBooks)) {
            $this->errorMessage = 'Book "' . $book->accession_number . '" is already scanned for return.';
            return;
        }

        // Find active borrow transaction for this book copy (globally, so return works regardless of who is loaded)
        $transaction = BorrowingTransaction::with(['student', 'book.bookDetail.bookData', 'book.bookDetail.bookData.authors'])
            ->where('book_id', $book->id)
            ->whereNull('return_date')
            ->first();

        if (!$transaction) {
            $this->errorMessage = 'Book "' . $book->accession_number . '" is not currently marked as borrowed.';
            return;
        }

        // Auto load/switch member to the student who borrowed this book
        if (!$this->scannedMember) {
            if ($transaction->student) {
                $this->returnedBooks = []; // Reset queue for new student session
                $this->loadMember($transaction->student);
            }
        } else if ($this->scannedMember['id'] !== $transaction->school_id) {
            $this->errorMessage = 'This book was borrowed by other student.';
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

        $this->lastReturnedBook = [
            'title' => $data ? $data->book_title : 'Unknown Book',
            'author' => $authorName,
            'accession' => $book->accession_number,
            'call_number' => $detail ? $detail->call_number : 'N/A',
            'code' => $data ? ($data->subtitle ?: 'BOOK') : 'BOOK',
            'borrowed_on' => $transaction->issued_date->format('M d, Y'),
            'due_date' => $transaction->due_date->format('M d, Y')
        ];

        // Re-load the member to update their borrowed books list
        $studentModel = Student::find($this->scannedMember['id']);
        if ($studentModel) {
            $this->loadMember($studentModel);
        }

        $this->dispatch('clear-search-input');
    }

    public function undoReturn($accessionNumber)
    {
        $this->errorMessage = '';
        $book = Book::where('accession_number', $accessionNumber)->first();
        if ($book) {
            // Reset lastReturnedBook if it matches
            if ($this->lastReturnedBook && $this->lastReturnedBook['accession'] === $accessionNumber) {
                $this->lastReturnedBook = null;
            }

            // Remove from temporary list
            $this->returnedBooks = array_diff($this->returnedBooks, [$book->id]);

            // Re-load member
            if ($this->scannedMember) {
                $studentModel = Student::find($this->scannedMember['id']);
                if ($studentModel) {
                    $this->loadMember($studentModel);
                }
            }
        }
    }

    public function reviewReturn()
    {
        if (!$this->scannedMember || empty($this->returnedBooks)) {
            return;
        }

        // Get the transaction IDs corresponding to the book IDs in our returnedBooks queue
        $transactionIds = BorrowingTransaction::whereIn('book_id', $this->returnedBooks)
            ->where('school_id', $this->scannedMember['id'])
            ->whereNull('return_date')
            ->pluck('id')
            ->toArray();

        // Save session data
        session([
            'confirm_return_student_id' => $this->scannedMember['id'],
            'confirm_return_transaction_ids' => $transactionIds
        ]);

        // Redirect to confirm return page
        return $this->redirect(route('admin.circulation-desk.return.confirm'), navigate: true);
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
                'return_date' => Carbon::now()->format('M d, Y')
            ];
            return;
        }

        $studentId = $this->scannedMember['id'] ?? null;

        $totalBorrowed = 0;
        $overdueCount = 0;

        if ($studentId) {
            // Count active borrowed books in database (which includes the ones in returnedBooks queue)
            $totalBorrowed = BorrowingTransaction::where('school_id', $studentId)
                ->whereNull('return_date')
                ->count();

            // Get overdue transactions in database
            $overdueTransactions = BorrowingTransaction::where('school_id', $studentId)
                ->whereNull('return_date')
                ->where('due_date', '<', Carbon::now())
                ->get();

            // Count only those that are not yet scanned in returnedBooks queue
            foreach ($overdueTransactions as $ot) {
                if (!in_array($ot->book_id, $this->returnedBooks)) {
                    $overdueCount++;
                }
            }
        }

        $returnedCount = count($this->returnedBooks);

        $this->stats = [
            'total' => $totalBorrowed,
            'returned' => $returnedCount,
            'remaining' => max(0, $totalBorrowed - $returnedCount),
            'overdue' => $overdueCount,
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
