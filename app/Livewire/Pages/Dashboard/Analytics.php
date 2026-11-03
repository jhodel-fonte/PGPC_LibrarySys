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
    public string $trendProgram = '';
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

    public static function academicBreakdown($rows, array $categories): \Illuminate\Support\Collection
    {
        $result = collect($categories)->map(fn ($aliases, $label) => (object) ['label' => $label, 'total' => 0]);
        $lookup = [];
        foreach ($categories as $label => $aliases) {
            foreach (array_merge([$label], $aliases) as $alias) {
                $lookup[mb_strtolower(trim($alias))] = $label;
            }
        }
        foreach ($rows as $row) {
            $label = $lookup[mb_strtolower(trim($row->label))] ?? $row->label;
            if (! $result->has($label)) $result->put($label, (object) ['label' => $label, 'total' => 0]);
            $result->get($label)->total += (int) $row->total;
        }
        return $result->values();
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

    public function exportExcel(): void
    {
        abort_unless(EmployeeRoleService::canViewReports(), 403);
        \App\Jobs\ExportSpreadsheet::queueFor(auth()->user(), 'analytics', ['appliedPeriod' => $this->appliedPeriod, 'appliedStart' => $this->appliedStart, 'appliedEnd' => $this->appliedEnd, 'trendProgram' => $this->trendProgram]);
        $this->dispatch('export-queued');
        $this->dispatch('notifications-updated');
        $this->dispatch('toast', message: 'Excel export queued. Track it in notifications.', type: 'success');
    }

    public function exportSheets(): array
    {
        abort_unless(EmployeeRoleService::canViewReports(), 403);
        $data = $this->render()->getData();
        $summary = [
            ['Metric', 'Current', 'Previous'],
            ['Start date', $this->appliedStart, $data['previousStart']->toDateString()],
            ['End date', $this->appliedEnd, $data['previousEnd']->toDateString()],
        ];
        foreach ($data['current'] as $name => $value) {
            $summary[] = [ucfirst($name), $value, $data['previous'][$name]];
        }
        $summary[] = ['Due today', $data['dueCount']];
        $summary[] = ['Overdue', $data['overdueCount']];
        if (EmployeeRoleService::canManageCirculation()) {
            $summary[] = ['Reservations', $data['reservationCount'], $data['previousReservationCount']];
            $summary[] = ['Waiting list', $data['waitlistCount']];
        }
        if (EmployeeRoleService::canManageCatalog()) {
            $summary[] = ['Acquisitions', $data['acquisitions']->sum('total'), $data['previousAcquisitions']];
            $summary[] = ['Missing acquisition dates', $data['missingAcquisitionDates']];
        }
        if (EmployeeRoleService::canManageFines()) {
            $summary[] = ['Payments', (float) $data['payments']->sum('total'), (float) $data['previousPayments']];
        }
        $sheets = ['Summary' => $summary, 'Circulation trend' => [['Period', 'Loans', 'Returns']], 'Hourly loans' => [['Hour', 'Loans']]];
        foreach ($data['hourly'] as $hour => $total) {
            $sheets['Hourly loans'][] = [sprintf('%02d:00', $hour), $total];
        }
        foreach ($data['buckets'] as $bucket) {
            $sheets['Circulation trend'][] = [$bucket['label'], $bucket['loans'], $bucket['returns']];
        }
        $breakdowns = [
            'Programs' => 'programs', 'Years' => 'years', 'Genders' => 'genders',
            'Borrow types' => 'borrowTypes', 'Top titles' => 'topTitles',
            'Borrower trend' => 'borrowerTrend', 'Program trend' => 'programTrend',
            'Reservations' => 'reservations', 'Reservation statuses' => 'reservationStatuses',
            'Copy statuses' => 'copyStatuses', 'Material types' => 'materialTypes',
            'Categories' => 'categories', 'Acquisitions' => 'acquisitions',
            'Fines' => 'fines', 'Payments' => 'payments', 'Student statuses' => 'studentStatuses',
            'Student imports' => 'studentImports', 'Catalog imports' => 'catalogImports',
            'Student import rows' => 'studentImportRows', 'Catalog import rows' => 'catalogImportRows',
        ];
        foreach ($breakdowns as $title => $key) {
            $sheets[$title] = [['Label', 'Total', 'Amount']];
            foreach ($data[$key] as $row) {
                $sheets[$title][] = [$row->label, is_numeric($row->total) ? (float) $row->total : $row->total, isset($row->amount) ? (float) $row->amount : null];
            }
        }
        return $sheets;
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

        $borrowerBuckets = array_fill_keys(array_keys($buckets), []);
        $loanHour = DB::getDriverName() === 'pgsql' ? 'EXTRACT(HOUR FROM issued_date)' : (DB::getDriverName() === 'sqlite' ? "strftime('%H', issued_date)" : 'HOUR(issued_date)');
        $borrowerRows = BorrowingTransaction::where('issued_date', '>=', $from)->where('issued_date', '<', $to)
            ->selectRaw("school_id, program_at_issue, DATE(issued_date) as event_day, $loanHour as event_hour, count(*) as total")
            ->groupByRaw("school_id, program_at_issue, DATE(issued_date), $loanHour")->get();
        foreach ($borrowerRows as $row) {
            $local = CarbonImmutable::parse($row->event_day.' '.str_pad((string) (int) $row->event_hour, 2, '0', STR_PAD_LEFT).':00:00', 'UTC')->setTimezone('Asia/Manila');
            $key = $local->format($monthly ? 'Y-m' : 'Y-m-d');
            if (isset($borrowerBuckets[$key]) && $row->school_id !== null) $borrowerBuckets[$key][$row->school_id] = true;
        }
        $borrowerTrend = collect($buckets)->map(fn ($bucket, $key) => (object) ['label' => $bucket['label'], 'total' => count($borrowerBuckets[$key])])->values();

        $base = DB::table('borrowing_transactions as loans')->leftJoin('students', 'students.id', '=', 'loans.school_id')
            ->where('loans.issued_date', '>=', $from)->where('loans.issued_date', '<', $to);
        $programExpression = "COALESCE(NULLIF(TRIM(loans.program_at_issue), ''), 'Not recorded at issue')";
        $programCategories = [];
        foreach (config('settings.college.programs', []) as $code => $name) {
            $programCategories["$code - $name"] = [$code, $name];
        }
        $programs = self::academicBreakdown((clone $base)->selectRaw("$programExpression as label, count(*) as total")->groupByRaw($programExpression)->orderBy('label')->get(), $programCategories);
        $programAliases = [];
        foreach ($programCategories as $label => $aliases) {
            foreach (array_merge([$label], $aliases) as $alias) $programAliases[mb_strtolower(trim($alias))] = $label;
        }
        $programTotals = array_fill_keys(array_keys($buckets), 0);
        foreach ($borrowerRows as $row) {
            $local = CarbonImmutable::parse($row->event_day.' '.str_pad((string) (int) $row->event_hour, 2, '0', STR_PAD_LEFT).':00:00', 'UTC')->setTimezone('Asia/Manila');
            $key = $local->format($monthly ? 'Y-m' : 'Y-m-d');
            $program = $programAliases[mb_strtolower(trim($row->program_at_issue ?? ''))] ?? null;
            if (isset($programTotals[$key]) && ($this->trendProgram === '' || $this->trendProgram === $program)) $programTotals[$key] += (int) $row->total;
        }
        $programTrend = collect($buckets)->map(fn ($bucket, $key) => (object) ['label' => $bucket['label'], 'total' => $programTotals[$key]])->values();
        $genderExpression = "COALESCE(NULLIF(TRIM(students.gender), ''), 'Unspecified')";
        $genders = EmployeeRoleService::canViewStudents() ? (clone $base)->selectRaw("$genderExpression as label, count(*) as total")->groupByRaw($genderExpression)->orderByDesc('total')->get() : collect();

        $yearExpression = "COALESCE(NULLIF(TRIM(loans.year_level_at_issue), ''), 'Not recorded at issue')";
        $years = self::academicBreakdown((clone $base)->selectRaw("$yearExpression as label, count(*) as total")->groupByRaw($yearExpression)->orderBy('label')->get(), array_fill_keys(['1st Year', '2nd Year', '3rd Year', '4th Year'], []));
        $borrowTypes = (clone $base)->leftJoin('borrow_types as types', 'types.id', '=', 'loans.borrow_type_id')
            ->selectRaw("COALESCE(types.name, 'Unspecified') as label, count(*) as total")->groupBy('types.name')->orderByDesc('total')->get();
        $topTitles = DB::table('borrowing_transactions as loans')
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->join('book_details as details', 'details.id', '=', 'books.book_detail_id')
            ->join('book_datas as titles', 'titles.id', '=', 'details.book_data_id')
            ->where('loans.issued_date', '>=', $from)->where('loans.issued_date', '<', $to)
            ->selectRaw('titles.id, titles.book_title as label, count(*) as total')
            ->groupBy('titles.id', 'titles.book_title')->orderByDesc('total')->limit(8)->get();

        $reservations = $reservationStatuses = $copyStatuses = $materialTypes = $categories = $acquisitions = $fines = $studentStatuses = $studentImports = $catalogImports = $studentImportRows = $catalogImportRows = $payments = collect();
        $reservationCount = $previousReservationCount = $waitlistCount = $missingAcquisitionDates = 0;
        $previousPayments = $previousAcquisitions = 0;
        if (EmployeeRoleService::canManageCirculation()) {
            $reservationCount = DB::table('book_reservations')->whereNull('deleted_at')->where('reservation_date', '>=', $from)->where('reservation_date', '<', $to)->count();
            [$previousFrom, $previousTo] = $this->bounds($previousStart, $previousEnd);
            $previousReservationCount = DB::table('book_reservations')->whereNull('deleted_at')->where('reservation_date', '>=', $previousFrom)->where('reservation_date', '<', $previousTo)->count();
            $reservationStatuses = DB::table('book_reservations as r')->join('reservation_statuses as status', 'status.id', '=', 'r.reservation_status_id')
                ->whereNull('r.deleted_at')->selectRaw('status.status as label, count(*) as total')->groupBy('status.status')->orderByDesc('total')->get();
            $reservationHour = DB::getDriverName() === 'pgsql' ? 'EXTRACT(HOUR FROM reservation_date)' : (DB::getDriverName() === 'sqlite' ? "strftime('%H', reservation_date)" : 'HOUR(reservation_date)');
            $reservations = DB::table('book_reservations')->whereNull('deleted_at')->where('reservation_date', '>=', $from)->where('reservation_date', '<', $to)
                ->selectRaw("DATE(reservation_date) as event_day, $reservationHour as event_hour, count(*) as total")
                ->groupByRaw("DATE(reservation_date), $reservationHour")->get()
                ->groupBy(fn ($row) => CarbonImmutable::parse($row->event_day.' '.str_pad((string) (int) $row->event_hour, 2, '0', STR_PAD_LEFT).':00:00', 'UTC')->setTimezone('Asia/Manila')->format($monthly ? 'Y-m' : 'Y-m-d'))
                ->map(fn ($rows) => $rows->sum('total'));
            $reservations = collect($buckets)->map(fn ($bucket, $key) => (object) ['label' => $bucket['label'], 'total' => $reservations->get($key, 0)])->values();
            $waitlistCount = DB::table('reservation_waitlist')->where('status', 'waiting')->count();
        }
        if (EmployeeRoleService::canManageCatalog()) {
            $previousAcquisitions = DB::table('books')->whereNull('deleted_at')->whereBetween('date_acquired', [$previousStart->toDateString(), $previousEnd->toDateString()])->count();
            $copyStatuses = DB::table('books')->whereNull('deleted_at')->selectRaw("COALESCE(NULLIF(TRIM(status), ''), 'Unspecified') as label, count(*) as total")
                ->groupByRaw("COALESCE(NULLIF(TRIM(status), ''), 'Unspecified')")->orderByDesc('total')->get();
            $materialTypes = DB::table('books')->join('book_details as details', 'details.id', '=', 'books.book_detail_id')
                ->leftJoin('material_types as types', 'types.id', '=', 'details.material_type_id')->whereNull('books.deleted_at')->whereNull('details.deleted_at')
                ->selectRaw("COALESCE(types.name, 'Unspecified') as label, count(*) as total")->groupBy('types.name')->orderByDesc('total')->get();
            $categories = DB::table('book_data_category as assignment')->join('categories', 'categories.id', '=', 'assignment.category_id')
                ->join('book_datas as titles', 'titles.id', '=', 'assignment.book_data_id')->whereNull('categories.deleted_at')->whereNull('titles.deleted_at')
                ->selectRaw('categories.name as label, count(*) as total')->groupBy('categories.name')->orderByDesc('total')->limit(8)->get();
            $acquisitions = DB::table('books')->whereNull('deleted_at')->whereBetween('date_acquired', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('date_acquired as label, count(*) as total')->groupBy('date_acquired')->orderBy('date_acquired')->get()
                ->groupBy(fn ($row) => CarbonImmutable::parse($row->label)->format($monthly ? 'Y-m' : 'Y-m-d'))
                ->map(fn ($rows) => $rows->sum('total'));
            $acquisitions = collect($buckets)->map(fn ($bucket, $key) => (object) ['label' => $bucket['label'], 'total' => $acquisitions->get($key, 0)])->values();
            $missingAcquisitionDates = DB::table('books')->whereNull('deleted_at')->whereNull('date_acquired')->count();
        }
        if (EmployeeRoleService::canManageFines()) {
            $previousPayments = DB::table('fine_payments')->whereDate('payment_date', '>=', $previousStart->toDateString())->whereDate('payment_date', '<=', $previousEnd->toDateString())->sum('payment_amount');
            $fines = DB::table('fines')->selectRaw('fine_status as label, count(*) as total, sum(amount) as amount')->groupBy('fine_status')->orderBy('fine_status')->get();
            $payments = DB::table('fine_payments')->whereDate('payment_date', '>=', $start->toDateString())->whereDate('payment_date', '<=', $end->toDateString())
                ->selectRaw('DATE(payment_date) as payment_day, sum(payment_amount) as amount')->groupByRaw('DATE(payment_date)')->orderBy('payment_day')->get()
                ->groupBy(fn ($row) => CarbonImmutable::parse($row->payment_day)->format($monthly ? 'Y-m' : 'Y-m-d'))->map(fn ($rows) => $rows->sum('amount'));
            $payments = collect($buckets)->map(fn ($bucket, $key) => (object) ['label' => $bucket['label'], 'total' => $payments->get($key, 0)])->values();
        }
        if (EmployeeRoleService::canViewStudents()) {
            $studentStatuses = DB::table('students')->join('accounts', 'accounts.id', '=', 'students.account_id')->leftJoin('account_statuses as status', 'status.id', '=', 'accounts.status_id')
                ->whereNull('students.deleted_at')->whereNull('accounts.deleted_at')->selectRaw("COALESCE(status.status_name, 'Unspecified') as label, count(*) as total")
                ->groupBy('status.status_name')->orderByDesc('total')->get();
            $studentImports = DB::table('student_imports')->where('created_at', '>=', $from)->where('created_at', '<', $to)->selectRaw('status as label, count(*) as total')->groupBy('status')->get();
            $studentImportRows = DB::table('student_import_rows as rows')->join('student_imports as jobs', 'jobs.id', '=', 'rows.student_import_id')->where('jobs.created_at', '>=', $from)->where('jobs.created_at', '<', $to)->selectRaw('rows.status as label, count(*) as total')->groupBy('rows.status')->get();
        }
        if (EmployeeRoleService::canManageCatalog()) {
            $catalogImports = DB::table('catalog_imports')->where('created_at', '>=', $from)->where('created_at', '<', $to)->selectRaw('status as label, count(*) as total')->groupBy('status')->get();
            $catalogImportRows = DB::table('catalog_import_rows as rows')->join('catalog_imports as jobs', 'jobs.id', '=', 'rows.catalog_import_id')->where('jobs.created_at', '>=', $from)->where('jobs.created_at', '<', $to)->selectRaw('rows.status as label, count(*) as total')->groupBy('rows.status')->get();
        }

        $now = CarbonImmutable::now('Asia/Manila');
        [$todayStart, $todayEnd] = $this->bounds($now, $now);
        $outstanding = BorrowingTransaction::whereNull('return_date')->whereNull('lost_at');
        $due = (clone $outstanding)->where('due_date', '>=', $todayStart)->where('due_date', '<', $todayEnd);
        $overdue = (clone $outstanding)->where('due_date', '<', $now->utc());
        $dueCount = (clone $due)->count();
        $overdueCount = (clone $overdue)->count();
        $items = (clone $outstanding)->with(['student' => fn ($q) => $q->withTrashed(), 'book' => fn ($q) => $q->withTrashed(), 'book.bookDetail.bookData'])
            ->where('due_date', '<', $todayEnd)->orderBy('due_date')->limit(5)->get();


        return view('livewire.pages.dashboard.analytics', compact('start', 'end', 'previousStart', 'previousEnd', 'current', 'previous', 'buckets', 'monthly', 'hourly', 'borrowerTrend', 'programTrend', 'programCategories', 'programs', 'genders', 'dueCount', 'overdueCount', 'items', 'now', 'years', 'borrowTypes', 'topTitles', 'reservations', 'reservationCount', 'previousReservationCount', 'reservationStatuses', 'waitlistCount', 'copyStatuses', 'materialTypes', 'categories', 'acquisitions', 'previousAcquisitions', 'missingAcquisitionDates', 'fines', 'payments', 'previousPayments', 'studentStatuses', 'studentImports', 'catalogImports', 'studentImportRows', 'catalogImportRows'));
    }
}
