<?php

namespace App\Livewire\Pages\dashboard;

use App\Models\Book;
use App\Models\BookData;
use App\Models\BookReservation;
use App\Models\BorrowingTransaction;
use App\Models\Student;
use Livewire\Component;

use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    
    public function render()
    {
        $activeMembers = Student::whereHas('libraryStatus', function ($query) {
            $query->where('status', 'like', '%Active%');
        })->count();

        if ($activeMembers === 0) {
            $activeMembers = Student::count();
        }

        $topBorrowings = \Illuminate\Support\Facades\DB::table('borrowing_transactions')
            ->join('books', 'borrowing_transactions.book_id', '=', 'books.id')
            ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
            ->select('book_details.book_data_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as borrows'))
            ->groupBy('book_details.book_data_id')
            ->orderByDesc('borrows')
            ->limit(4)
            ->get();

        $mostBorrowedTitles = collect();
        foreach ($topBorrowings as $row) {
            $bookData = BookData::with(['bookDetails.books'])->find($row->book_data_id);
            if ($bookData) {
                $bookData->borrowing_transactions_count = $row->borrows;
                $mostBorrowedTitles->push($bookData);
            }
        }

        return view('livewire.pages.admin.dashboard', [
            'totalTitles' => BookData::count(),
            'totalCopies' => Book::count(),
            'activeMembers' => $activeMembers,
            'borrowedItems' => BorrowingTransaction::whereNull('return_date')->count(),
            'overdueItems' => BorrowingTransaction::whereNull('return_date')
                ->where('due_date', '<', now())
                ->count(),
            'pendingReservations' => BookReservation::where('reservation_status_id', 1)->count(),
            'currentBorrowers' => BorrowingTransaction::with(['student.account', 'book.bookDetail.bookData'])
                ->whereNull('return_date')
                ->orderBy('issued_date', 'desc')
                ->take(5)
                ->get(),
            'mostBorrowedTitles' => $mostBorrowedTitles,
        ]);
    }
}
