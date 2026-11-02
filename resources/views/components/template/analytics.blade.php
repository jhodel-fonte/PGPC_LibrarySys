@props(['start', 'end', 'previousStart', 'previousEnd', 'current', 'previous', 'buckets', 'hourly', 'programs', 'genders', 'dueCount', 'overdueCount', 'now'])

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
    <p>Program recorded at issue; missing historical values are shown separately. Showing up to eight.</p>
    <ul>@forelse ($programs as $row)<li>{{ $row->label }}: {{ $row->total }}</li>@empty<li>No loans in this period.</li>@endforelse</ul>
    <h2 class="mt-6 text-lg font-bold">Loans by gender</h2>
    <p>Based on current student records; historical values may have changed.</p>
    <ul>@forelse ($genders as $row)<li>{{ $row->label }}: {{ $row->total }}</li>@empty<li>No loans in this period.</li>@endforelse</ul>
</article>
