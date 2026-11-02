<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\BorrowingTransaction;
use App\Services\EmployeeRoleService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('livewire.layouts.admin', ['title' => 'Analytics', 'subpage' => 'Operational & Circulation Statistics'])]
class Analytics extends Component
{
    #[Url]
    public string $section = '';
    public string $period = 'month';
    public string $startDate = '';
    public string $endDate = '';
    #[Locked] public string $appliedPeriod = 'month';
    #[Locked] public string $appliedStart = '';
    #[Locked] public string $appliedEnd = '';

    public function mount(): void
    {
        abort_unless(EmployeeRoleService::canViewReports(), 403);
        $today = CarbonImmutable::now('Asia/Manila');
        $this->startDate = $this->appliedStart = $today->startOfMonth()->toDateString();
        $this->endDate = $this->appliedEnd = $today->toDateString();
    }

    public function applyPeriod(): void
    {
        $this->validate(['period' => 'required|in:month,last_month,year,custom']);
        $today = CarbonImmutable::now('Asia/Manila')->startOfDay();
        if ($this->period === 'custom') {
            $this->validate(['startDate' => 'required|date_format:Y-m-d|before_or_equal:endDate', 'endDate' => 'required|date_format:Y-m-d']);
            $start = CarbonImmutable::parse($this->startDate, 'Asia/Manila');
            $end = CarbonImmutable::parse($this->endDate, 'Asia/Manila');
            if ($end->greaterThan($today)) {
                $this->addError('endDate', 'The end date cannot be in the future.');
                return;
            }
            if ($start->diffInDays($end) > 1825) {
                $this->addError('endDate', 'Choose a range of no more than five years.');
                return;
            }
        } else {
            $start = match ($this->period) {
                'last_month' => $today->subMonthNoOverflow()->startOfMonth(),
                'year' => $today->startOfYear(),
                default => $today->startOfMonth(),
            };
            $end = $this->period === 'last_month' ? $start->endOfMonth() : $today;
        }
        $this->resetValidation();
        $this->startDate = $this->appliedStart = $start->toDateString();
        $this->endDate = $this->appliedEnd = $end->toDateString();
        $this->appliedPeriod = $this->period;
    }

    /** Inclusive local dates; shorter comparison months are capped at their last day. */
    public static function previousRange(string $period, CarbonImmutable $start, CarbonImmutable $end): array
    {
        if ($period === 'month') {
            $previousStart = $start->subMonthNoOverflow()->startOfMonth();
            return [$previousStart, $previousStart->day(min($end->day, $previousStart->daysInMonth))];
        }
        if ($period === 'last_month') {
            $previousStart = $start->subMonthNoOverflow()->startOfMonth();
            return [$previousStart, $previousStart->endOfMonth()];
        }
        if ($period === 'year') {
            return [$start->subYearNoOverflow(), $end->subYearNoOverflow()];
        }
        $days = (int) $start->diffInDays($end) + 1;
        return [$start->subDays($days), $start->subDay()];
    }

    private function bounds(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return [$start->startOfDay()->utc(), $end->addDay()->startOfDay()->utc()];
    }

    private function metrics(CarbonImmutable $start, CarbonImmutable $end): array
    {
        [$from, $to] = $this->bounds($start, $end);
        $loans = BorrowingTransaction::where('issued_date', '>=', $from)->where('issued_date', '<', $to);
        return [
            'loans' => (clone $loans)->count(),
            'borrowers' => (clone $loans)->distinct()->count('school_id'),
            'returns' => BorrowingTransaction::where('return_date', '>=', $from)->where('return_date', '<', $to)->count(),
        ];
    }

    public static function comparison(int $current, int $previous): string
    {
        if ($previous === 0) return $current === 0 ? 'No activity in either period' : 'No previous activity';
        $change = round(($current - $previous) / $previous * 100, 1);
        return ($change > 0 ? '+' : '').$change.'% vs previous period';
    }

    public function render()
    {
        $start = CarbonImmutable::parse($this->appliedStart, 'Asia/Manila')->startOfDay();
        $end = CarbonImmutable::parse($this->appliedEnd, 'Asia/Manila')->startOfDay();
        [$previousStart, $previousEnd] = self::previousRange($this->appliedPeriod, $start, $end);
        [$from, $to] = $this->bounds($start, $end);
        $current = $this->metrics($start, $end);
        $previous = $this->metrics($previousStart, $previousEnd);

        // Aggregate by local day in PHP so UTC timestamps stay correct across calendar boundaries.
        $monthly = $start->diffInDays($end) > 45;
        $buckets = [];
        $hourly = array_fill(0, 24, 0);
        for ($day = $monthly ? $start->startOfMonth() : $start; $day <= $end; $day = $monthly ? $day->addMonth() : $day->addDay()) {
            $buckets[$day->format($monthly ? 'Y-m' : 'Y-m-d')] = ['label' => $day->format($monthly ? 'M Y' : 'M j'), 'loans' => 0, 'returns' => 0];
        }
        foreach (['issued_date' => 'loans', 'return_date' => 'returns'] as $column => $metric) {
            // Group UTC dates and hours to avoid transferring every transaction to PHP.
            $hourSql = DB::getDriverName() === 'pgsql' ? "EXTRACT(HOUR FROM $column)" : (DB::getDriverName() === 'sqlite' ? "strftime('%H', $column)" : "HOUR($column)");
            $rows = BorrowingTransaction::where($column, '>=', $from)->where($column, '<', $to)
                ->selectRaw("DATE($column) as event_day, $hourSql as event_hour, count(*) as total")
                ->groupByRaw("DATE($column), $hourSql")->get();
            foreach ($rows as $row) {
                $local = CarbonImmutable::parse($row->event_day.' '.str_pad((string) (int) $row->event_hour, 2, '0', STR_PAD_LEFT).':00:00', 'UTC')->setTimezone('Asia/Manila');
                $key = $local->format($monthly ? 'Y-m' : 'Y-m-d');
                if (isset($buckets[$key])) $buckets[$key][$metric] += (int) $row->total;
                if ($metric === 'loans') $hourly[(int) $local->hour] += (int) $row->total;
            }
        }

        $base = DB::table('borrowing_transactions as loans')->leftJoin('students', 'students.id', '=', 'loans.school_id')
            ->where('loans.issued_date', '>=', $from)->where('loans.issued_date', '<', $to);
        $programExpression = "COALESCE(NULLIF(TRIM(loans.program_at_issue), ''), 'Not recorded at issue')";
        $programs = (clone $base)->selectRaw("$programExpression as label, count(*) as total")->groupByRaw($programExpression)->orderByDesc('total')->limit(8)->get();
        $genderExpression = "COALESCE(NULLIF(TRIM(students.gender), ''), 'Unspecified')";
        $genders = (clone $base)->selectRaw("$genderExpression as label, count(*) as total")->groupByRaw($genderExpression)->orderByDesc('total')->get();

        $now = CarbonImmutable::now('Asia/Manila');
        [$todayStart, $todayEnd] = $this->bounds($now, $now);
        $outstanding = BorrowingTransaction::whereNull('return_date');
        $due = (clone $outstanding)->where('due_date', '>=', $todayStart)->where('due_date', '<', $todayEnd);
        $overdue = (clone $outstanding)->where('due_date', '<', $now->utc());
        $dueCount = (clone $due)->count();
        $overdueCount = (clone $overdue)->count();
        $items = (clone $outstanding)->with(['student' => fn ($q) => $q->withTrashed(), 'book' => fn ($q) => $q->withTrashed(), 'book.bookDetail.bookData'])
            ->where('due_date', '<', $todayEnd)->orderBy('due_date')->limit(5)->get();
        arsort($hourly);
        $hourly = array_slice(array_filter($hourly), 0, 4, true);

        return view('livewire.pages.dashboard.analytics', compact('start', 'end', 'previousStart', 'previousEnd', 'current', 'previous', 'buckets', 'monthly', 'hourly', 'programs', 'genders', 'dueCount', 'overdueCount', 'items', 'now'));
    }
}
