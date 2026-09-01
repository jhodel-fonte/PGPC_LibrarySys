<?php

namespace Tests\Unit\Circulation;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookData;
use App\Models\BorrowingTransaction;
use App\Models\LibraryStatus;
use App\Models\BookCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class getStudentBorrow extends TestCase
{
    use RefreshDatabase;

    public function test_can_query_active_borrow_count_for_student(): void
    {
        LibraryStatus::create(['id' => 1, 'status' => 'Active']);
        BookCondition::create(['id' => 2, 'status' => 'Good']);

        $student = Student::create([
            'school_id_number' => '2026-7777',
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'library_status_id' => 1,
            'program' => 'BSA',
            'year_level' => '2'
        ]);

        $bookData = BookData::create(['book_title' => 'Sample Book']);
        $bookDetail = BookDetail::create(['book_data_id' => $bookData->id]);
        $book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => 2,
            'accession_number' => 'ACC-777',
            'code' => 'BAR-777',
            'status' => 'borrowed'
        ]);

        BorrowingTransaction::create([
            'book_id' => $book->id,
            'school_id' => $student->id,
            'issued_by_id' => 1,
            'borrow_type_id' => 1,
            'issued_condition_id' => 2,
            'issued_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7),
            'return_date' => null
        ]);

        $activeCount = BorrowingTransaction::where('school_id', $student->id)
            ->whereNull('return_date')
            ->count();

        $this->assertEquals(1, $activeCount);
    }
}

