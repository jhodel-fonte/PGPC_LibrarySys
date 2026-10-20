<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Book;
use App\Models\BookData;
use App\Models\BookReservation;
use App\Models\BorrowingTransaction;
use App\Models\Category;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('livewire.layouts.admin', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    public string $trendsFilter = '7_days';
    public string $categoryFilter = 'all_time';
    public string $activityFilter = 'this_year';

    /**
     * Compute real borrowing and returning trends based on the selected filter.
     */
    protected function getBorrowingTrendsData(): array
    {
        $now = Carbon::now();

        if ($this->trendsFilter === 'this_month') {
            $daysCount = max(1, (int) $now->day);
            $startDate = (clone $now)->startOfMonth()->startOfDay();
            $endDate = (clone $now)->endOfDay();

            $trendDates = [];
            $datesList = [];
            for ($d = 1; $d <= $daysCount; $d++) {
                $dt = (clone $now)->startOfMonth()->addDays($d - 1);
                $datesList[] = $dt->toDateString();
                $trendDates[] = $dt->format('M j');
            }
        } elseif ($this->trendsFilter === '30_days') {
            $daysCount = 30;
            $startDate = (clone $now)->subDays($daysCount - 1)->startOfDay();
            $endDate = (clone $now)->endOfDay();

            $trendDates = [];
            $datesList = [];
            for ($i = $daysCount - 1; $i >= 0; $i--) {
                $dt = (clone $now)->subDays($i);
                $datesList[] = $dt->toDateString();
                $trendDates[] = $dt->format('M j');
            }
        } else { // '7_days'
            $daysCount = 7;
            $startDate = (clone $now)->subDays($daysCount - 1)->startOfDay();
            $endDate = (clone $now)->endOfDay();

            $trendDates = [];
            $datesList = [];
            for ($i = $daysCount - 1; $i >= 0; $i--) {
                $dt = (clone $now)->subDays($i);
                $datesList[] = $dt->toDateString();
                $trendDates[] = $dt->format('M j');
            }
        }

        // Aggregate actual borrowed counts in 1 fast query
        $borrowedMap = BorrowingTransaction::whereBetween('issued_date', [$startDate, $endDate])
            ->selectRaw('DATE(issued_date) as dt, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(issued_date)'))
            ->pluck('total', 'dt')
            ->toArray();

        // Aggregate actual returned counts in 1 fast query
        $returnedMap = BorrowingTransaction::whereBetween('return_date', [$startDate, $endDate])
            ->selectRaw('DATE(return_date) as dt, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(return_date)'))
            ->pluck('total', 'dt')
            ->toArray();

        $borrowedTrend = [];
        $returnedTrend = [];
        $hasAnyData = false;

        foreach ($datesList as $dateStr) {
            $b = (int) ($borrowedMap[$dateStr] ?? 0);
            $r = (int) ($returnedMap[$dateStr] ?? 0);
            if ($b > 0 || $r > 0) {
                $hasAnyData = true;
            }
            $borrowedTrend[] = $b;
            $returnedTrend[] = $r;
        }

        // If the database has 0 transactions recorded anywhere, provide clean baseline
        if (! $hasAnyData && BorrowingTransaction::count() === 0) {
            $borrowedTrend = array_fill(0, $daysCount, 0);
            $returnedTrend = array_fill(0, $daysCount, 0);
        }

        return [
            'series' => [
                ['name' => 'Borrowed', 'data' => $borrowedTrend],
                ['name' => 'Returned', 'data' => $returnedTrend],
            ],
            'labels' => $trendDates,
        ];
    }

    /**
     * Compute most borrowed book categories for the donut chart.
     */
    protected function getCategoryDonutData(): array
    {
        $now = Carbon::now();
        $query = DB::table('borrowing_transactions')
            ->join('books', 'borrowing_transactions.book_id', '=', 'books.id')
            ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
            ->join('book_datas', 'book_details.book_data_id', '=', 'book_datas.id')
            ->join('book_data_category', 'book_datas.id', '=', 'book_data_category.book_data_id')
            ->join('categories', 'book_data_category.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total');

        if ($this->categoryFilter === 'this_month') {
            $query->whereYear('borrowing_transactions.issued_date', $now->year)
                  ->whereMonth('borrowing_transactions.issued_date', $now->month);
        } elseif ($this->categoryFilter === 'this_year') {
            $query->whereYear('borrowing_transactions.issued_date', $now->year);
        }

        $categoryData = $query->get();

        if ($categoryData->isNotEmpty()) {
            if ($categoryData->count() > 5) {
                $top = $categoryData->slice(0, 5);
                $othersCount = (int) $categoryData->slice(5)->sum('total');

                $labels = $top->pluck('name')->toArray();
                $series = $top->pluck('total')->map(fn($v) => (int) $v)->toArray();

                if ($othersCount > 0) {
                    $labels[] = 'Others';
                    $series[] = $othersCount;
                }
            } else {
                $labels = $categoryData->pluck('name')->toArray();
                $series = $categoryData->pluck('total')->map(fn($v) => (int) $v)->toArray();
            }

            $total = array_sum($series);
        } else {
            // If no borrows exist for this filter, display available catalog categories with count
            $catalogCategories = DB::table('book_data_category')
                ->join('categories', 'book_data_category.category_id', '=', 'categories.id')
                ->select('categories.name', DB::raw('COUNT(*) as total'))
                ->groupBy('categories.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            if ($catalogCategories->isNotEmpty()) {
                $labels = $catalogCategories->pluck('name')->toArray();
                $series = $catalogCategories->pluck('total')->map(fn($v) => (int) $v)->toArray();
                $total = array_sum($series);
            } else {
                $labels = ['General', 'Information Technology', 'Literature'];
                $series = [0, 0, 0];
                $total = 0;
            }
        }

        return [
            'labels' => $labels,
            'series' => $series,
            'total' => $total,
        ];
    }

    /**
     * Compute monthly borrowed and returned activity for the grouped bar chart.
     */
    protected function getMonthlyActivityData(): array
    {
        $targetYear = ($this->activityFilter === 'last_year') ? Carbon::now()->subYear()->year : Carbon::now()->year;
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $driver = DB::connection()->getDriverName();
        $monthIssuedExpr = match ($driver) {
            'pgsql' => 'CAST(EXTRACT(MONTH FROM issued_date) AS INTEGER)',
            'sqlite' => 'CAST(strftime(\'%m\', issued_date) AS INTEGER)',
            default => 'MONTH(issued_date)',
        };
        $monthReturnExpr = match ($driver) {
            'pgsql' => 'CAST(EXTRACT(MONTH FROM return_date) AS INTEGER)',
            'sqlite' => 'CAST(strftime(\'%m\', return_date) AS INTEGER)',
            default => 'MONTH(return_date)',
        };

        // Aggregate borrows by month in 1 fast query
        $monthlyBorrows = BorrowingTransaction::whereYear('issued_date', $targetYear)
            ->selectRaw("{$monthIssuedExpr} as month_num, COUNT(*) as total")
            ->groupBy(DB::raw($monthIssuedExpr))
            ->pluck('total', 'month_num')
            ->toArray();

        // Aggregate returns by month in 1 fast query
        $monthlyReturns = BorrowingTransaction::whereYear('return_date', $targetYear)
            ->selectRaw("{$monthReturnExpr} as month_num, COUNT(*) as total")
            ->groupBy(DB::raw($monthReturnExpr))
            ->pluck('total', 'month_num')
            ->toArray();

        $monthlyBorrowed = [];
        $monthlyReturned = [];

        foreach (range(1, 12) as $m) {
            $monthlyBorrowed[] = (int) ($monthlyBorrows[$m] ?? 0);
            $monthlyReturned[] = (int) ($monthlyReturns[$m] ?? 0);
        }

        return [
            'series' => [
                ['name' => 'Borrowed', 'data' => $monthlyBorrowed],
                ['name' => 'Returned', 'data' => $monthlyReturned],
            ],
            'labels' => $months,
        ];
    }

    public function render()
    {
        $trendsData = $this->getBorrowingTrendsData();
        $categoryData = $this->getCategoryDonutData();
        $activityData = $this->getMonthlyActivityData();

        // Top Borrowed Book Titles
        $topBorrowings = DB::table('borrowing_transactions')
            ->join('books', 'borrowing_transactions.book_id', '=', 'books.id')
            ->join('book_details', 'books.book_detail_id', '=', 'book_details.id')
            ->select('book_details.book_data_id', DB::raw('COUNT(*) as borrows'))
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

        return view('livewire.pages.dashboard.index', [
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
            'borrowingTrendsSeries' => $trendsData['series'],
            'borrowingTrendsLabels' => $trendsData['labels'],
            'categoryDonutSeries' => $categoryData['series'],
            'categoryDonutLabels' => $categoryData['labels'],
            'categoryDonutTotal' => $categoryData['total'],
            'monthlyActivitySeries' => $activityData['series'],
            'monthlyActivityLabels' => $activityData['labels'],
        ]);
    }
}
