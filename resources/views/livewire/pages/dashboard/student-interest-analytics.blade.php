<div class="min-h-full bg-[#F8FAFC]">
    <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-col gap-4 border-b border-[#DDE5EF] pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#64748B]">Recommendation system</p>
                <h1 class="mt-1 text-2xl font-bold text-[#0F172A] sm:text-3xl">Student interests</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#475569]">See the books and subjects students have explored, and how their activity helps shape recommendations.</p>
            </div>
            <a href="{{ route(request()->routeIs('admin.*') ? 'admin.analytics.ai.evaluation' : 'librarian.analytics.ai.evaluation') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-[#CBD5E1] bg-white px-5 text-sm font-semibold text-[#102B70] hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]">Technical evaluation <span aria-hidden="true" class="ml-2">→</span></a>
        </header>

        <section aria-labelledby="overview-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2"><h2 id="overview-heading" class="text-lg font-bold text-[#0F172A]">Library activity at a glance</h2><span class="text-xs font-semibold text-[#64748B]">All recorded history · Real student activity</span></div>
            <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-[#DDE5EF] bg-white p-5"><dt class="text-sm font-medium text-[#475569]">Students with activity</dt><dd class="mt-2 text-3xl font-bold tabular-nums text-[#102B70]">{{ number_format($students) }}</dd><p class="mt-1 text-xs text-[#64748B]">Viewed or borrowed at least one book</p></div>
                <div class="rounded-lg border border-[#DDE5EF] bg-white p-5"><dt class="text-sm font-medium text-[#475569]">Students who borrowed</dt><dd class="mt-2 text-3xl font-bold tabular-nums text-[#102B70]">{{ number_format($borrowers) }}</dd><p class="mt-1 text-xs text-[#64748B]">Recorded in recommendation history</p></div>
                <div class="rounded-lg border border-[#DDE5EF] bg-white p-5"><dt class="text-sm font-medium text-[#475569]">Books explored</dt><dd class="mt-2 text-3xl font-bold tabular-nums text-[#102B70]">{{ number_format($books) }}</dd><p class="mt-1 text-xs text-[#64748B]">Distinct ISBNs viewed or borrowed</p></div>
            </dl>
        </section>

        <section class="rounded-lg border border-[#DDE5EF] bg-white p-5 sm:p-6" aria-labelledby="activity-chart-heading">
            <h2 id="activity-chart-heading" class="text-lg font-bold text-[#0F172A]">Recorded book activity</h2>
            <p class="mt-1 text-sm text-[#64748B]">The highest recorded interaction for each student and book.</p>
            @if ($viewOnly + $borrowed > 0)
                @php($viewPercent = round($viewOnly / ($viewOnly + $borrowed) * 100, 1))
                <div class="mt-5 flex flex-wrap items-center gap-6">
                    <div aria-hidden="true" class="h-36 w-36 shrink-0 rounded-full" style="background: conic-gradient(#2856A5 0% {{ $viewPercent }}%, #0F766E {{ $viewPercent }}% 100%)"></div>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center gap-3"><span aria-hidden="true" class="h-3 w-3 rounded-sm bg-[#2856A5]"></span><span class="text-[#334155]">Viewed only</span><strong class="tabular-nums text-[#0F172A]">{{ number_format($viewOnly) }} ({{ $viewPercent }}%)</strong></li>
                        <li class="flex items-center gap-3"><span aria-hidden="true" class="h-3 w-3 rounded-sm bg-[#0F766E]"></span><span class="text-[#334155]">Borrowed</span><strong class="tabular-nums text-[#0F172A]">{{ number_format($borrowed) }} ({{ round(100 - $viewPercent, 1) }}%)</strong></li>
                    </ul>
                </div>
                <p class="mt-4 text-xs leading-5 text-[#64748B]">These slices count student–book records, not individual students or total view and loan events. A borrowed book is counted as borrowed even if it was also viewed.</p>
            @else
                <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">No student book activity has been recorded yet.</p>
            @endif
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            @foreach ([['Subjects students explore', 'Categories of books viewed or borrowed by the most students.', $categories, 'name'], ['Books students explore', 'Titles viewed or borrowed by the most students.', $titles, 'book_title']] as [$heading, $description, $rows, $label])
                <section class="rounded-lg border border-[#DDE5EF] bg-white p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-[#0F172A]">{{ $heading }}</h2>
                    <p class="mt-1 text-sm text-[#64748B]">{{ $description }}</p>
                    @if ($rows->isEmpty())
                        <p class="mt-6 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">No results to show yet. Items appear after at least three students have explored them.</p>
                    @else
                        @php($maximum = $rows->max('students'))
                        <ol class="mt-6 space-y-4" aria-label="{{ $heading }} by number of students">
                            @foreach ($rows as $row)
                                <li>
                                    <div class="flex items-start justify-between gap-4 text-sm"><span class="font-medium text-[#334155]">{{ $row->{$label} ?: 'Untitled' }}</span><span class="shrink-0 font-semibold tabular-nums text-[#102B70]">{{ number_format($row->students) }} {{ $row->students == 1 ? 'student' : 'students' }}</span></div>
                                    <div aria-hidden="true" class="mt-2 h-2 overflow-hidden rounded-full bg-[#E8EDF4]"><div class="h-full rounded-full bg-[#2856A5]" style="width: {{ round($row->students / $maximum * 100, 2) }}%"></div></div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            @endforeach
        </div>

        <section class="rounded-lg border border-[#BFDBFE] bg-[#EFF6FF] p-5 sm:p-6" aria-labelledby="how-it-works-heading">
            <h2 id="how-it-works-heading" class="font-bold text-[#0F172A]">How this helps recommendations</h2>
            <p class="mt-2 max-w-4xl text-sm leading-6 text-[#334155]">When a student views or borrows a book, the recommender can use its title, subjects, author and other catalog details to find related books. It also considers patterns learned from library activity and the student's program and year level.</p>
        </section>
        <p class="text-xs leading-5 text-[#64748B]">These are all-time counts from saved recommendation history, not total view events or proof of recommendation clicks. A student's highest recorded interaction per ISBN is kept; demo data is excluded. Graphs only show entries with at least three students. Categories can overlap, and titles without current catalog metadata may be missing from the graphs.</p>
    </div>
</div>
