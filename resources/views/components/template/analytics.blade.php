@props(['start', 'end', 'previousStart', 'previousEnd', 'current', 'previous', 'buckets', 'hourly', 'programs', 'genders', 'dueCount', 'overdueCount', 'now', 'borrowerTrend', 'programTrend', 'trendProgram', 'previousAcquisitions', 'previousPayments', 'previousReservationCount', 'years', 'borrowTypes', 'topTitles', 'reservations', 'reservationCount', 'reservationStatuses', 'waitlistCount', 'copyStatuses', 'materialTypes', 'categories', 'acquisitions', 'missingAcquisitionDates', 'fines', 'payments', 'studentStatuses', 'studentImports', 'catalogImports', 'studentImportRows', 'catalogImportRows'])

<style>
    @media print {
        @page { size: letter portrait; margin: 12mm; }
        html, body { height: auto !important; overflow: visible !important; background: white !important; }
        aside, nav, .fixed, [role="navigation"], .livewire-offline, .toast-notification,
        div[x-data*="sidebarOpen"] > div:first-child, div[x-data*="sidebarOpen"] > div > div:first-child,
        .analytics-screen { display: none !important; }
        div[x-data*="sidebarOpen"], div[x-data*="sidebarOpen"] > div {
            display: block !important; height: auto !important; width: 100% !important;
            overflow: visible !important; margin-left: 0 !important;
        }
        main:not(.analytics-screen) { height: auto !important; overflow: visible !important; display: block !important; }
    }
</style>

<article class="mx-auto max-w-4xl bg-white p-6 text-sm text-slate-900">
    <h1 class="text-2xl font-bold">PGPC Library analytics</h1>
    <p>Selected: {{ $start->format('M j, Y') }} – {{ $end->format('M j, Y') }}</p>
    <p>Comparison: {{ $previousStart->format('M j, Y') }} – {{ $previousEnd->format('M j, Y') }} · Philippine time</p>
    <h2 class="mt-6 text-lg font-bold">Activity</h2>
    <p>Loans count issued transactions, including room use; returns count transactions returned in the selected period. Students who borrowed counts each student once.</p>
    <table class="mt-2 w-full text-left"><thead><tr><th>Metric</th><th>Selected period</th><th>Previous period</th><th>Change</th></tr></thead><tbody>
        @foreach (['loans' => 'Loans issued', 'returns' => 'Items returned', 'borrowers' => 'Students who borrowed'] as $key => $label)
            <tr><th class="py-1 font-medium">{{ $label }}</th><td>{{ number_format($current[$key]) }}</td><td>{{ number_format($previous[$key]) }}</td><td>{{ \App\Livewire\Pages\Dashboard\Analytics::comparison($current[$key], $previous[$key]) }}</td></tr>
        @endforeach
    </tbody></table>
    <h2 class="mt-6 text-lg font-bold">Circulation trend</h2>
    <table class="mt-2 w-full text-left"><thead><tr><th>Period</th><th>Loans</th><th>Returns</th></tr></thead><tbody>@foreach ($buckets as $bucket)<tr><th class="font-medium">{{ $bucket['label'] }}</th><td>{{ $bucket['loans'] }}</td><td>{{ $bucket['returns'] }}</td></tr>@endforeach</tbody></table>
    <h2 class="mt-6 text-lg font-bold">Today and current status</h2>
    <p>As of {{ $now->format('M j, Y, g:i A') }} Philippine time: {{ $dueCount }} outstanding due today; {{ $overdueCount }} currently overdue. The counts can overlap.</p>
    <h2 class="mt-6 text-lg font-bold">Busiest borrowing hours</h2>
    <ul>@forelse ($hourly as $hour => $total)<li>{{ \Carbon\CarbonImmutable::createFromTime($hour, 0, 0, 'Asia/Manila')->format('g:i A') }}: {{ $total }} loans</li>@empty<li>No loans in this period.</li>@endforelse</ul>
    <h2 class="mt-6 text-lg font-bold">Loans by program</h2>
    <p>All configured programs; counts use program recorded at issue. Missing historical values are shown separately.</p>
    <ul>@forelse ($programs as $row)<li>{{ $row->label }}: {{ $row->total }}</li>@empty<li>No loans in this period.</li>@endforelse</ul>
    @if (\App\Services\EmployeeRoleService::canViewStudents())
    <h2 class="mt-6 text-lg font-bold">Loans by gender</h2>
    <p>Based on current student records; historical values may have changed.</p>
    <ul>@forelse ($genders as $row)<li>{{ $row->label }}: {{ $row->total }}</li>@empty<li>No loans in this period.</li>@endforelse</ul>
    @endif
    @php
        $summaries = [
            ['Students who borrowed by day or month (students may appear in multiple periods)', $borrowerTrend], ['Loans over time: '.($trendProgram ?: 'All programs'), $programTrend], ['Loans by year at issue', $years], ['Loans by borrow type', $borrowTypes], ['Most borrowed titles (up to eight)', $topTitles],
        ];
        if (\App\Services\EmployeeRoleService::canManageCirculation()) {
            $summaries[] = ['Reservations created in the selected period', $reservations];
            $summaries[] = ['Current reservation statuses', $reservationStatuses];
        }
        if (\App\Services\EmployeeRoleService::canManageCatalog()) {
            $summaries[] = ['Current copies by status', $copyStatuses];
            $summaries[] = ['Current copies by material type', $materialTypes];
            $summaries[] = ['Current title category assignments (categories may overlap)', $categories];
            $summaries[] = ['Recorded acquisitions by selected period', $acquisitions];
            $summaries[] = ['Catalog import jobs created in period: current status', $catalogImports];
            $summaries[] = ['Catalog import rows in those jobs: current status', $catalogImportRows];
        }
        if (\App\Services\EmployeeRoleService::canViewStudents()) {
            $summaries[] = ['Current student account status', $studentStatuses];
            $summaries[] = ['Student import jobs created in period: current status', $studentImports];
            $summaries[] = ['Student import rows in those jobs: current status', $studentImportRows];
        }
        if (\App\Services\EmployeeRoleService::canManageFines()) $summaries[] = ['Payments by recorded date (₱)', $payments];
    @endphp
    @if (\App\Services\EmployeeRoleService::canManageCirculation())<p class="mt-4">{{ $reservationCount }} reservations created in the selected period; {{ $previousReservationCount }} in the previous period; {{ $waitlistCount }} currently waiting.</p>@endif
    @if (\App\Services\EmployeeRoleService::canManageCatalog())<p class="mt-4">{{ $missingAcquisitionDates }} current copies have no acquisition date; {{ $previousAcquisitions }} acquisitions were recorded in the previous period.</p>@endif
    @foreach ($summaries as [$heading, $rows])
        <h2 class="mt-6 text-lg font-bold">{{ $heading }}</h2>
        <ul>@forelse ($rows as $row)<li>{{ $row->label }}: {{ $row->total }}</li>@empty<li>No recorded data.</li>@endforelse</ul>
    @endforeach
    @if (\App\Services\EmployeeRoleService::canManageFines())
        <p class="mt-4">Payments in previous period: ₱{{ number_format($previousPayments, 2) }}, using recorded payment dates.</p>
        <h2 class="mt-6 text-lg font-bold">Current fines</h2>
        <ul>@forelse ($fines as $fine)<li>{{ $fine->label }}: {{ $fine->total }} fines, ₱{{ number_format($fine->amount, 2) }}</li>@empty<li>No fines recorded.</li>@endforelse</ul>
    @endif
</article>
