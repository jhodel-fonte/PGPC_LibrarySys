<div class="min-h-full bg-slate-50" x-data="{ selectedPeriod: @js($period), openChart() { this.$refs.expandedChart.showModal() }, scrollToSection(section) { document.getElementById('analytics-' + ({gender: 'demographics'}[section] || section))?.scrollIntoView({ behavior: 'smooth' }); } }" x-init="$nextTick(() => { const section = new URLSearchParams(location.search).get('section'); if (section) scrollToSection(section); })" @scroll-to-section.window="scrollToSection($event.detail)">
    @php
        $prefix = \App\Services\EmployeeRoleService::getRoutePrefix();
        $labels = array_column($buckets, 'label');
        $chartWidth = max(1000, count($labels) * 55);
        $series = [
            ['name' => 'Loans issued', 'data' => array_column($buckets, 'loans')],
            ['name' => 'Returns', 'data' => array_column($buckets, 'returns')],
        ];
        $range = $start->format('M j, Y').' – '.$end->format('M j, Y');
        $previousRange = $previousStart->format('M j, Y').' – '.$previousEnd->format('M j, Y');
    @endphp
    <main class="analytics-screen mx-auto max-w-[1500px] space-y-7 p-4 sm:p-6 lg:p-8 print:hidden">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-[#102B70]">Library analytics</h1><p class="mt-1 text-sm text-slate-600">Understand circulation and see what needs attention today.</p></div>
            <div class="flex gap-3 text-sm font-semibold"><a class="text-[#102B70] underline underline-offset-4" href="{{ route($prefix.'analytics.ai') }}">AI evaluation</a><button type="button" class="text-[#102B70] underline underline-offset-4" onclick="window.print()">Print report</button></div>
        </header>

        <form wire:submit="applyPeriod" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4" aria-label="Analytics date range">
            <label class="grid gap-1 text-sm font-semibold text-slate-700">Date range
                <select wire:model="period" x-model="selectedPeriod" class="rounded-lg border-slate-300 text-sm"><option value="month">This month to date</option><option value="last_month">Last month</option><option value="year">This year to date</option><option value="custom">Custom range</option></select>
            </label>
            <label x-show="selectedPeriod === 'custom'" x-cloak class="grid gap-1 text-sm font-semibold text-slate-700">From<input type="date" wire:model="startDate" class="rounded-lg border-slate-300 text-sm"></label>
            <label x-show="selectedPeriod === 'custom'" x-cloak class="grid gap-1 text-sm font-semibold text-slate-700">To<input type="date" wire:model="endDate" class="rounded-lg border-slate-300 text-sm"></label>
            <button type="submit" class="rounded-lg bg-[#102B70] px-4 py-2 text-sm font-semibold text-white hover:bg-[#173d90]" wire:loading.attr="disabled">Apply range</button>
            <span wire:loading wire:target="applyPeriod" role="status" class="text-sm text-slate-600">Updating analytics…</span>
            @error('period') <p class="w-full text-sm text-red-700">{{ $message }}</p> @enderror
            @error('startDate') <p class="w-full text-sm text-red-700">{{ $message }}</p> @enderror
            @error('endDate') <p class="w-full text-sm text-red-700">{{ $message }}</p> @enderror
            <p class="w-full text-xs text-slate-600">Showing {{ $range }} · Compared with {{ $previousRange }} · Philippine time. Shorter comparison months end on their last day.</p>
        </form>

        <section id="analytics-overview" class="scroll-mt-24" aria-labelledby="overview-heading">
            <h2 id="overview-heading" class="mb-3 text-lg font-bold text-slate-900">Activity in this period</h2>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (['loans' => 'Loans issued', 'returns' => 'Books returned', 'borrowers' => 'Active borrowers'] as $key => $label)
                    <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm font-medium text-slate-600">{{ $label }}</p><p class="mt-2 text-3xl font-bold tabular-nums text-[#102B70]">{{ number_format($current[$key]) }}</p><p class="mt-2 text-sm text-slate-700">{{ \App\Livewire\Pages\Dashboard\Analytics::comparison($current[$key], $previous[$key]) }}</p><p class="mt-1 text-xs text-slate-600">Previous: {{ number_format($previous[$key]) }}</p></div>
                @endforeach
            </div>
        </section>

        <section id="analytics-circulation" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-4 sm:p-6" aria-labelledby="trend-heading">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="trend-heading" class="text-lg font-bold text-slate-900">Circulation trend</h2><p class="mt-1 text-sm text-slate-600">{{ $monthly ? 'Monthly' : 'Daily' }} loans issued and returns · {{ $range }}</p></div><button type="button" @click="openChart()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-[#102B70] hover:bg-slate-50">Expand chart</button></div>
            @if ($current['loans'] + $current['returns'] === 0)
                <p class="py-12 text-center text-sm text-slate-600">No loans or returns in this period.</p>
            @else
                <div class="mx-auto mt-5 max-w-4xl overflow-x-auto"><div style="min-width: {{ $chartWidth * 0.7 }}px"><x-widget.graph.main :series="$series" :labels="$labels" :palette="['#102B70', '#0284c7']" line grid legend :height="240" :svg-width="$chartWidth" /></div></div>
            @endif
            <details class="mt-4 text-sm"><summary class="cursor-pointer font-semibold text-[#102B70]">View trend data</summary><div class="mt-3 max-h-72 overflow-auto"><table class="w-full text-left"><thead><tr class="border-b border-slate-200"><th class="py-2">Period</th><th class="py-2">Loans</th><th class="py-2">Returns</th></tr></thead><tbody>@foreach ($buckets as $bucket)<tr class="border-b border-slate-100"><th scope="row" class="py-2 font-medium">{{ $bucket['label'] }}</th><td>{{ $bucket['loans'] }}</td><td>{{ $bucket['returns'] }}</td></tr>@endforeach</tbody></table></div></details>
        </section>

        <section id="analytics-operations" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-4 sm:p-6" aria-labelledby="operations-heading">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="operations-heading" class="text-lg font-bold text-slate-900">Today and current status</h2><p class="mt-1 text-sm text-slate-600">As of {{ $now->format('M j, Y, g:i A') }} Philippine time · These figures are independent of the selected range.</p></div>
                @if (\App\Services\EmployeeRoleService::canManageCirculation())<a class="text-sm font-semibold text-[#102B70] underline underline-offset-4" href="{{ route($prefix.'circulation-desk.borrowed-books') }}">View borrowed books</a>@endif
            </div>
            <div class="mt-4 flex flex-wrap gap-x-10 gap-y-3"><p class="text-sm text-slate-700"><strong class="text-2xl text-[#102B70]">{{ number_format($dueCount) }}</strong> outstanding due today</p><p class="text-sm text-slate-700"><strong class="text-2xl text-amber-800">{{ number_format($overdueCount) }}</strong> currently overdue</p></div>
            <p class="mt-2 text-xs text-slate-600">A loan due today can also be overdue once its due time has passed.</p>
            <div class="mt-4 divide-y divide-slate-100">@forelse ($items as $item)<div class="flex flex-wrap justify-between gap-2 py-3 text-sm"><div><p class="font-semibold text-slate-900">{{ $item->book?->bookDetail?->bookData?->book_title ?? 'Book record unavailable' }}</p><p class="text-slate-600">{{ $item->student ? trim($item->student->first_name.' '.$item->student->last_name) : 'Student record unavailable' }}</p></div><span class="text-slate-700">Due {{ $item->due_date->copy()->setTimezone('Asia/Manila')->format('M j, g:i A') }}</span></div>@empty<p class="py-4 text-sm text-slate-600">No outstanding books due today or overdue.</p>@endforelse</div>
            @if ($items->count() === 5)<p class="mt-2 text-xs text-slate-600">Showing up to five items, earliest due first.</p>@endif
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section id="analytics-programs" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-4 sm:p-6"><h2 class="text-lg font-bold text-slate-900">Loans by program</h2><p class="mt-1 text-sm text-slate-600">{{ $range }} · Program at issue when available; otherwise the student's current program.</p><div class="mt-4 divide-y divide-slate-100">@forelse ($programs as $row)<div class="flex justify-between gap-4 py-2 text-sm"><span class="break-words text-slate-700">{{ $row->label }}</span><strong class="shrink-0 tabular-nums text-[#102B70]">{{ number_format($row->total) }}</strong></div>@empty<p class="py-4 text-sm text-slate-600">No loans in this period.</p>@endforelse</div>@if ($programs->count() === 8)<p class="mt-3 text-xs text-slate-600">Showing up to eight programs.</p>@endif</section>
            <section id="analytics-demographics" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-4 sm:p-6"><h2 class="text-lg font-bold text-slate-900">Loans by gender</h2><p class="mt-1 text-sm text-slate-600">{{ $range }} · Based on current student records; historical values may have changed.</p><div class="mt-4 divide-y divide-slate-100">@forelse ($genders as $row)<div class="flex justify-between gap-4 py-2 text-sm"><span class="break-words text-slate-700">{{ $row->label }}</span><strong class="shrink-0 tabular-nums text-[#102B70]">{{ number_format($row->total) }}</strong></div>@empty<p class="py-4 text-sm text-slate-600">No loans in this period.</p>@endforelse</div></section>
        </div>
        <section class="rounded-xl border border-slate-200 bg-white p-4 sm:p-6"><h2 class="text-lg font-bold text-slate-900">Busiest borrowing hours</h2><p class="mt-1 text-sm text-slate-600">Loans issued in {{ $range }}, grouped by Philippine local time.</p><div class="mt-4 divide-y divide-slate-100">@forelse ($hourly as $hour => $total)<div class="flex justify-between py-2 text-sm"><span>{{ \Carbon\CarbonImmutable::createFromTime($hour, 0, 0, 'Asia/Manila')->format('g:i A') }} – {{ \Carbon\CarbonImmutable::createFromTime($hour, 0, 0, 'Asia/Manila')->addHour()->format('g:i A') }}</span><strong>{{ number_format($total) }} loans</strong></div>@empty<p class="py-4 text-sm text-slate-600">No loans in this period.</p>@endforelse</div></section>
    </main>

    <dialog x-ref="expandedChart" aria-labelledby="expanded-heading" class="m-auto w-[min(96vw,1100px)] max-w-none rounded-xl border border-slate-200 bg-white p-5 shadow-xl backdrop:bg-slate-900/60 print:hidden">
        <div class="flex items-center justify-between gap-3"><div><h2 id="expanded-heading" class="text-lg font-bold text-slate-900">Circulation trend</h2><p class="text-sm text-slate-600">{{ $range }}</p></div><form method="dialog"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-[#102B70]">Close</button></form></div>
        @if ($current['loans'] + $current['returns'] === 0)<p class="py-12 text-center text-sm text-slate-600">No loans or returns in this period.</p>@else<div class="mt-5 overflow-x-auto"><div style="min-width: {{ $chartWidth * 0.7 }}px"><x-widget.graph.main :series="$series" :labels="$labels" :palette="['#102B70', '#0284c7']" line grid legend :height="280" :svg-width="$chartWidth" /></div></div>@endif
        <div class="mt-4 max-h-64 overflow-auto"><table class="w-full text-left text-sm"><caption class="text-left font-semibold">Trend data</caption><thead><tr class="border-b border-slate-200"><th class="py-2">Period</th><th>Loans</th><th>Returns</th></tr></thead><tbody>@foreach ($buckets as $bucket)<tr class="border-b border-slate-100"><th scope="row" class="py-2 font-medium">{{ $bucket['label'] }}</th><td>{{ $bucket['loans'] }}</td><td>{{ $bucket['returns'] }}</td></tr>@endforeach</tbody></table></div>
    </dialog>
    <div class="hidden print:block"><x-template.analytics :start="$start" :end="$end" :previous-start="$previousStart" :previous-end="$previousEnd" :current="$current" :previous="$previous" :buckets="$buckets" :hourly="$hourly" :programs="$programs" :genders="$genders" :due-count="$dueCount" :overdue-count="$overdueCount" :now="$now" /></div>
</div>
