<div class="min-h-full bg-slate-50" x-data="{ selectedPeriod: @js($period), openChart() { this.$refs.expandedChart.showModal() }, scrollToSection(section) { document.getElementById('analytics-' + ({gender: 'demographics'}[section] || section))?.scrollIntoView({ behavior: 'smooth' }); } }" x-init="$nextTick(() => { const section = new URLSearchParams(location.search).get('section'); if (section) scrollToSection(section); })" @scroll-to-section.window="scrollToSection($event.detail)">
    @php
        $prefix = \App\Services\EmployeeRoleService::getRoutePrefix();
        $labels = array_column($buckets, 'label');
        $chartWidth = 560;
        $series = [
            ['name' => 'Loans issued', 'data' => array_column($buckets, 'loans')],
            ['name' => 'Returns', 'data' => array_column($buckets, 'returns')],
        ];
        $range = $start->format('M j, Y').' – '.$end->format('M j, Y');
        $previousRange = $previousStart->format('M j, Y').' – '.$previousEnd->format('M j, Y');
    @endphp
    <main class="analytics-screen mx-auto max-w-[1500px] space-y-7 p-4 sm:p-6 lg:p-8 print:hidden">
        <header class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div><h1 class="text-2xl font-bold text-[#102B70]">Library analytics</h1><p class="mt-1 text-sm text-slate-600">Activity, comparisons and today’s priorities.</p></div>
                <form wire:submit="applyPeriod" class="flex flex-wrap items-end gap-3" aria-label="Analytics date range">
                    <label class="grid gap-1 text-sm font-semibold text-slate-700">Date range
                        <select wire:model="period" x-model="selectedPeriod" class="min-h-11 rounded-lg border-slate-300 text-sm"><option value="month">This month to date</option><option value="last_month">Last month</option><option value="year">This year to date</option><option value="custom">Custom range</option></select>
                    </label>
                    <label x-show="selectedPeriod === 'custom'" x-cloak class="grid gap-1 text-sm font-semibold text-slate-700">From<input type="date" wire:model="startDate" class="min-h-11 rounded-lg border-slate-300 text-sm"></label>
                    <label x-show="selectedPeriod === 'custom'" x-cloak class="grid gap-1 text-sm font-semibold text-slate-700">To<input type="date" wire:model="endDate" class="min-h-11 rounded-lg border-slate-300 text-sm"></label>
                    <button type="submit" class="min-h-11 rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white hover:bg-[#173d90] disabled:opacity-60" wire:loading.attr="disabled" wire:target="applyPeriod">Apply range</button>
            <x-export-confirmation description="The file contains analytics summaries and breakdowns for the selected date range." />
                    <button type="button" class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-[#102B70] hover:bg-slate-50" onclick="window.print()">Print report</button>
                    <span wire:loading wire:target="applyPeriod" role="status" class="sr-only">Updating analytics…</span>
                    @error('period') <p class="w-full text-sm text-red-700">{{ $message }}</p> @enderror
                    @error('startDate') <p class="w-full text-sm text-red-700">{{ $message }}</p> @enderror
                    @error('endDate') <p class="w-full text-sm text-red-700">{{ $message }}</p> @enderror
                </form>
            </div>
            <div class="mt-5 border-t border-slate-100 pt-4 text-sm text-slate-600">
                <p><strong class="text-slate-800">{{ $range }}</strong> compared with {{ $previousRange }} · Philippine time</p>
            </div>
        </header>

        <nav aria-label="Analytics sections" class="flex flex-wrap gap-2 text-sm font-semibold">
            <a class="rounded-lg bg-white px-3 py-2 text-[#102B70] hover:bg-blue-50" href="#analytics-overview">Overview</a><a class="rounded-lg bg-white px-3 py-2 text-[#102B70] hover:bg-blue-50" href="#analytics-circulation">Circulation</a>
            @if (\App\Services\EmployeeRoleService::canManageCirculation())<a class="rounded-lg bg-white px-3 py-2 text-[#102B70] hover:bg-blue-50" href="#analytics-reservations">Reservations</a>@endif
            @if (\App\Services\EmployeeRoleService::canManageCatalog())<a class="rounded-lg bg-white px-3 py-2 text-[#102B70] hover:bg-blue-50" href="#analytics-collection">Collection</a>@endif
            @if (\App\Services\EmployeeRoleService::canManageFines())<a class="rounded-lg bg-white px-3 py-2 text-[#102B70] hover:bg-blue-50" href="#analytics-finance">Finance</a>@endif
        </nav>

        <section id="analytics-overview" class="scroll-mt-24" aria-labelledby="overview-heading">
            <h2 id="overview-heading" class="mb-1 text-lg font-bold text-slate-900">Activity in this period</h2><p class="mb-4 text-sm text-slate-600">Loans count issued transactions, including room use; returns count transactions returned in the selected period. Students who borrowed counts each student once.</p>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (['loans' => 'Loans issued', 'returns' => 'Items returned', 'borrowers' => 'Students who borrowed'] as $key => $label)
                    <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm font-medium text-slate-600">{{ $label }}</p><p class="mt-2 text-3xl font-bold tabular-nums text-[#102B70]">{{ number_format($current[$key]) }}</p><p class="mt-2 text-sm text-slate-700">{{ \App\Livewire\Pages\Dashboard\Analytics::comparison($current[$key], $previous[$key]) }}</p><p class="mt-1 text-xs text-slate-600">Previous: {{ number_format($previous[$key]) }}</p>
</div>
                @endforeach
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-2 items-start">
        <section id="analytics-circulation" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-4 sm:p-6" aria-labelledby="trend-heading">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="trend-heading" class="text-lg font-bold text-slate-900">Circulation trend</h2><p class="mt-1 text-sm text-slate-600">{{ $monthly ? 'Monthly' : 'Daily' }} loans issued and returns · {{ $range }}</p></div><button type="button" @click="openChart()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-[#102B70] hover:bg-slate-50">Expand chart</button></div>
            @if ($current['loans'] + $current['returns'] === 0)
                <p class="py-12 text-center text-sm text-slate-600">No loans or returns in this period.</p>
            @else
                <div class="mx-auto mt-5 max-w-4xl"><div><x-widget.graph.main :series="$series" :labels="$labels" :colors="['#102B70', '#0284c7']" line grid legend :height="240" :svg-width="$chartWidth" /></div></div>
            @endif
            <details class="mt-4 text-sm"><summary class="cursor-pointer font-semibold text-[#102B70]">View trend data</summary><div class="mt-3 max-h-72 overflow-auto"><table class="w-full text-left"><thead><tr class="border-b border-slate-200"><th class="py-2">Period</th><th class="py-2">Loans</th><th class="py-2">Returns</th></tr></thead><tbody>@foreach ($buckets as $bucket)<tr class="border-b border-slate-100"><th scope="row" class="py-2 font-medium">{{ $bucket['label'] }}</th><td>{{ $bucket['loans'] }}</td><td>{{ $bucket['returns'] }}</td></tr>@endforeach</tbody></table></div></details>
        </section>

        <section id="analytics-operations" class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-4 sm:p-6" aria-labelledby="operations-heading">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="operations-heading" class="text-lg font-bold text-slate-900">Today and current status</h2><p class="mt-1 text-sm text-slate-600">As of {{ $now->format('M j, Y, g:i A') }} Philippine time · These figures are independent of the selected range.</p></div>
                @if (\App\Services\EmployeeRoleService::canManageCirculation())<a class="text-sm font-semibold text-[#102B70] underline underline-offset-4" href="{{ route($prefix.'circulation-desk.borrowed-books') }}">View borrowed books</a>@endif
            </div>
            <div class="mt-4 flex flex-wrap gap-x-10 gap-y-3"><p class="text-sm text-slate-700"><strong class="text-2xl text-[#102B70]">{{ number_format($dueCount) }}</strong> outstanding due today</p><p class="text-sm text-slate-700"><strong class="text-2xl text-amber-800">{{ number_format($overdueCount) }}</strong> currently overdue</p></div>
            <p class="mt-2 text-xs text-slate-600">A loan due today can also be overdue once its due time has passed.</p>
            @php $statusMax = max(1, $dueCount, $overdueCount); @endphp
            <div class="mt-4 space-y-3 text-sm" aria-label="Current due and overdue loan counts">
                @foreach (['Due today' => $dueCount, 'Overdue' => $overdueCount] as $statusLabel => $statusValue)
                    <div><div class="flex justify-between"><span>{{ $statusLabel }}</span><strong>{{ number_format($statusValue) }}</strong></div><div class="mt-1 h-4 rounded bg-slate-100" aria-hidden="true"><div class="h-4 rounded {{ $statusLabel === 'Overdue' ? 'bg-amber-700' : 'bg-[#2450a4]' }}" style="width: {{ 100 * $statusValue / $statusMax }}%"></div></div></div>
                @endforeach
            </div>
            <div class="mt-4 divide-y divide-slate-100">@forelse ($items as $item)<div class="flex flex-wrap justify-between gap-2 py-3 text-sm"><div><p class="font-semibold text-slate-900">{{ $item->book?->bookDetail?->bookData?->book_title ?? 'Book record unavailable' }}</p><p class="text-slate-600">{{ \App\Services\EmployeeRoleService::canViewStudents() ? ($item->student ? trim($item->student->first_name.' '.$item->student->last_name) : 'Student record unavailable') : 'Borrower details restricted' }}</p></div><span class="text-slate-700">Due {{ $item->due_date->copy()->setTimezone('Asia/Manila')->format('M j, g:i A') }}</span></div>@empty<p class="py-4 text-sm text-slate-600">No outstanding books due today or overdue.</p>@endforelse</div>
            @if ($items->count() === 5)<p class="mt-2 text-xs text-slate-600">Showing up to five items, earliest due first.</p>@endif
        </section>

        </div>

        <div class="grid items-start gap-5 xl:grid-cols-2">
            <x-analytics.trend title="Students who borrowed over time" :description="$range.' · Distinct students within each '.($monthly ? 'month' : 'day').'; totals across points can overlap.'" :rows="$borrowerTrend" />
            <x-analytics.trend title="Loans by program over time" :description="$range.' · Program recorded at issue; all programs includes missing and older values.'" :rows="$programTrend">
                <label class="mt-3 grid max-w-sm gap-1 text-sm font-semibold text-slate-700">Program
                    <select wire:model.live="trendProgram" class="min-h-11 rounded-lg border-slate-300 text-sm"><option value="">All programs</option>@foreach ($programCategories as $label => $aliases)<option value="{{ $label }}">{{ $label }}</option>@endforeach</select>
                </label>
            </x-analytics.trend>
        </div>

        <h2 class="text-xl font-bold text-slate-900">Borrowing breakdown</h2>
        <div class="!mt-3 grid gap-5 lg:grid-cols-2">
            <x-analytics.bars id="analytics-programs" title="Loans by program" :description="$range.' · Includes unrecorded and older values; counts use program recorded at issue.'" :rows="$programs" chart="donut" />
            <x-analytics.bars title="Loans by year level" :description="$range.' · Includes unrecorded and other values; counts use year recorded at issue.'" :rows="$years" chart="donut" />
            <x-analytics.bars title="Loans by borrow type" :description="$range.' · Includes room-use and other recorded types.'" :rows="$borrowTypes" chart="donut" />
            <x-analytics.bars title="Most borrowed titles" :description="$range.' · Loan transactions; up to eight titles.'" :rows="$topTitles" />
            @if (\App\Services\EmployeeRoleService::canViewStudents())
                <x-analytics.bars id="analytics-demographics" title="Loans by gender" :description="$range.' · Uses current student records; historical values may have changed.'" :rows="$genders" chart="donut" />
                <x-analytics.bars title="Student account status" description="Current student accounts; excludes archived students and accounts." :rows="$studentStatuses" chart="donut" />
            @endif
            <section class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
                <h3 class="text-lg font-bold text-slate-900">Borrowing by hour</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $range }} · All 24 hours, Philippine time.</p>
                <div class="mt-4"><div><x-widget.graph.main :series="[['name' => 'Loans issued', 'data' => array_values($hourly)]]" :labels="array_map(fn ($hour) => \Carbon\CarbonImmutable::createFromTime($hour, 0, 0, 'Asia/Manila')->format('g A'), array_keys($hourly))" :colors="['#2450a4']" :height="190" :svg-width="560" line grid markers /></div></div>
                <details class="mt-3 text-sm"><summary class="cursor-pointer font-semibold text-[#102B70]">View hourly counts</summary><ul class="mt-2 max-h-56 overflow-auto">@foreach ($hourly as $hour => $total)<li class="flex justify-between border-b border-slate-100 py-1"><span>{{ \Carbon\CarbonImmutable::createFromTime($hour, 0, 0, 'Asia/Manila')->format('g:i A') }}</span><span>{{ $total }}</span></li>@endforeach</ul></details>
            </section>
        </div>

        @if (\App\Services\EmployeeRoleService::canManageCirculation())
            <h2 id="analytics-reservations" class="scroll-mt-24 text-xl font-bold text-slate-900">Reservations</h2>
            <p class="!mt-1 text-sm text-slate-600">{{ number_format($reservationCount) }} reservations created · {{ \App\Livewire\Pages\Dashboard\Analytics::comparison($reservationCount, $previousReservationCount) }} (previous: {{ number_format($previousReservationCount) }}) · {{ number_format($waitlistCount) }} currently waiting.</p>
            <div class="!mt-4 grid gap-5 lg:grid-cols-2">
                <x-analytics.trend title="Reservations created" :description="$range.' · Philippine time; excludes archived reservations.'" :rows="$reservations" />
                <x-analytics.bars title="Reservation statuses" description="Current statuses, including fulfilled, cancelled and expired; independent of the selected range." :rows="$reservationStatuses" chart="donut" />
                <section class="rounded-xl border border-slate-200 bg-white p-5"><h3 class="text-lg font-bold text-slate-900">Current waitlist</h3><p class="mt-1 text-sm text-slate-600">Independent of the selected range.</p><p class="mt-3 text-2xl font-bold tabular-nums text-[#102B70]">{{ number_format($waitlistCount) }}</p><p class="text-sm text-slate-600">entries waiting for a copy</p></section>
            </div>
        @endif

        @if (\App\Services\EmployeeRoleService::canManageCatalog())
            <h2 id="analytics-collection" class="scroll-mt-24 text-xl font-bold text-slate-900">Collection</h2>
            <div class="!mt-3 grid gap-5 lg:grid-cols-2">
                <x-analytics.bars title="Copies by status" description="Current physical copies; excludes archived copies." :rows="$copyStatuses" />
                <x-analytics.bars title="Copies by material type" description="Current physical copies with active catalog details." :rows="$materialTypes" />
                <x-analytics.bars title="Title category assignments" description="Current active titles; categories can overlap. Showing up to eight." :rows="$categories" />
                <x-analytics.trend title="Recorded acquisitions" :description="$range.' · '.number_format($missingAcquisitionDates).' current copies have no acquisition date.'" :rows="$acquisitions"><p class="mt-3 text-sm text-slate-700">Selected: <strong>{{ number_format($acquisitions->sum('total')) }}</strong> · Previous: <strong>{{ number_format($previousAcquisitions) }}</strong> · {{ \App\Livewire\Pages\Dashboard\Analytics::comparison((int) $acquisitions->sum('total'), $previousAcquisitions) }}</p></x-analytics.trend>
            </div>
        @endif

        @if (\App\Services\EmployeeRoleService::canManageFines())
            <h2 id="analytics-finance" class="scroll-mt-24 text-xl font-bold text-slate-900">Finance</h2>
            <div class="!mt-3 grid items-start gap-5 lg:grid-cols-2">
            <x-analytics.trend title="Payments recorded" :description="$range.' · Grouped by the date stored on each payment record.'" :rows="$payments" :money="true">
                <p class="mt-3 text-sm text-slate-700">Selected: <strong>₱{{ number_format($payments->sum('total'), 2) }}</strong> · Previous: <strong>₱{{ number_format($previousPayments, 2) }}</strong></p>
            </x-analytics.trend>
            <div>
            <x-analytics.bars title="Current fines by status" description="Number of fines in each current status; independent of the selected period." :rows="$fines" chart="donut" />
            <div class="mt-4"><x-analytics.bars title="Current fine amounts" description="Current recorded amounts by status; not a historical payments report." :rows="$fines->map(fn ($fine) => (object) ['label' => $fine->label, 'total' => $fine->amount])" :money="true" /></div>
            </div>
            </div>
        @endif

        @if (\App\Services\EmployeeRoleService::canViewStudents() || \App\Services\EmployeeRoleService::canManageCatalog())
            <h2 class="text-xl font-bold text-slate-900">Import jobs</h2>
            <p class="!mt-1 text-sm text-slate-600">Jobs created in {{ $range }}, grouped by their current status. Counts are jobs, not rows.</p>
            <div class="!mt-4 grid gap-5 lg:grid-cols-2">
                @if (\App\Services\EmployeeRoleService::canViewStudents())<x-analytics.bars title="Student import jobs" :description="$range" :rows="$studentImports" />@endif
                @if (\App\Services\EmployeeRoleService::canManageCatalog())<x-analytics.bars title="Catalog import jobs" :description="$range" :rows="$catalogImports" />@endif
                @if (\App\Services\EmployeeRoleService::canViewStudents())<x-analytics.bars title="Student import rows" description="Current row statuses for jobs created in the selected period." :rows="$studentImportRows" />@endif
                @if (\App\Services\EmployeeRoleService::canManageCatalog())<x-analytics.bars title="Catalog import rows" description="Current row statuses for jobs created in the selected period." :rows="$catalogImportRows" />@endif
            </div>
        @endif
    </main>

    <dialog x-ref="expandedChart" aria-labelledby="expanded-heading" class="m-auto w-[min(96vw,1100px)] max-w-none rounded-xl border border-slate-200 bg-white p-5 shadow-xl backdrop:bg-slate-900/60 print:hidden">
        <div class="flex items-center justify-between gap-3"><div><h2 id="expanded-heading" class="text-lg font-bold text-slate-900">Circulation trend</h2><p class="text-sm text-slate-600">{{ $range }}</p></div><form method="dialog"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-[#102B70]">Close</button></form></div>
        @if ($current['loans'] + $current['returns'] === 0)<p class="py-12 text-center text-sm text-slate-600">No loans or returns in this period.</p>@else<div class="mt-5"><div><x-widget.graph.main :series="$series" :labels="$labels" :colors="['#102B70', '#0284c7']" line grid legend :height="280" :svg-width="$chartWidth" /></div></div>@endif
        <div class="mt-4 max-h-64 overflow-auto"><table class="w-full text-left text-sm"><caption class="text-left font-semibold">Trend data</caption><thead><tr class="border-b border-slate-200"><th class="py-2">Period</th><th>Loans</th><th>Returns</th></tr></thead><tbody>@foreach ($buckets as $bucket)<tr class="border-b border-slate-100"><th scope="row" class="py-2 font-medium">{{ $bucket['label'] }}</th><td>{{ $bucket['loans'] }}</td><td>{{ $bucket['returns'] }}</td></tr>@endforeach</tbody></table></div>
    </dialog>
    <div class="hidden print:block"><x-template.analytics :start="$start" :end="$end" :previous-start="$previousStart" :previous-end="$previousEnd" :current="$current" :previous="$previous" :buckets="$buckets" :hourly="$hourly" :programs="$programs" :genders="$genders" :due-count="$dueCount" :overdue-count="$overdueCount" :now="$now" :borrower-trend="$borrowerTrend" :program-trend="$programTrend" :trend-program="$trendProgram" :previous-acquisitions="$previousAcquisitions" :previous-payments="$previousPayments" :previous-reservation-count="$previousReservationCount" :years="$years" :borrow-types="$borrowTypes" :top-titles="$topTitles" :reservations="$reservations" :reservation-count="$reservationCount" :reservation-statuses="$reservationStatuses" :waitlist-count="$waitlistCount" :copy-statuses="$copyStatuses" :material-types="$materialTypes" :categories="$categories" :acquisitions="$acquisitions" :missing-acquisition-dates="$missingAcquisitionDates" :fines="$fines" :payments="$payments" :student-statuses="$studentStatuses" :student-imports="$studentImports" :catalog-imports="$catalogImports" :student-import-rows="$studentImportRows" :catalog-import-rows="$catalogImportRows" /></div>
</div>
