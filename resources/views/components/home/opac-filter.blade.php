@props([
    'availabilities' => [],
    'resourceTypes' => [],
    'subjects' => [],
    'selectedAvailabilities' => [],
    'selectedType' => 'all',
    'selectedSubjects' => [],
    'yearFrom' => null,
    'yearTo' => null,
    'search' => '',
    'formId' => 'opacFilterForm',
])

@php
    $selectedAvailabilities = (array) $selectedAvailabilities;
    $selectedSubjects = (array) $selectedSubjects;

    // Fallbacks if empty
    if (empty($availabilities)) {
        $availabilities = [
            ['id' => 'available', 'label' => 'Available', 'count' => 128, 'color' => 'bg-emerald-500'],
            ['id' => 'checked_out', 'label' => 'Checked Out', 'count' => 42, 'color' => 'bg-rose-500'],
            ['id' => 'reserved', 'label' => 'Reserved', 'count' => 18, 'color' => 'bg-amber-500'],
            ['id' => 'reference_only', 'label' => 'Reference Only', 'count' => 16, 'color' => 'bg-blue-500'],
        ];
    }
    if (empty($resourceTypes)) {
        $resourceTypes = [
            ['id' => 'books', 'label' => 'Books', 'count' => 128],
            ['id' => 'theses', 'label' => 'Theses', 'count' => 32],
            ['id' => 'journals', 'label' => 'Journals', 'count' => 24],
            ['id' => 'reports', 'label' => 'Reports', 'count' => 12],
            ['id' => 'multimedia', 'label' => 'Multimedia', 'count' => 8],
        ];
    }
    if (empty($subjects)) {
        $subjects = [
            ['id' => 'cs', 'label' => 'Computer Science', 'count' => 84],
            ['id' => 'prog', 'label' => 'Programming', 'count' => 62],
            ['id' => 'algo', 'label' => 'Algorithms', 'count' => 38],
            ['id' => 'db', 'label' => 'Database Systems', 'count' => 29],
            ['id' => 'se', 'label' => 'Software Engineering', 'count' => 25],
        ];
    }
@endphp

<!-- Filter Sidebar Card -->
<form
    id="{{ $formId }}"
    action="{{ route('opac.index') }}"
    method="GET"
    class="w-full rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs select-none"
>
    <!-- Hidden Inputs to preserve search and type from hero bar -->
    <input type="hidden" name="search" value="{{ $search }}">
    <input type="hidden" name="type" value="{{ $selectedType }}">

    <!-- Header / Filter Title -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <h3 class="text-[15px] font-bold text-[#0B2454] flex items-center gap-2">
            <svg width="16" height="16" class="h-4 w-4 shrink-0 text-[#0B2454]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>Filters</span>
        </h3>
        <a
            href="{{ route('opac.index', array_filter(['search' => $search, 'type' => $selectedType])) }}"
            class="text-[12px] font-semibold text-slate-400 hover:text-[#0B2454] transition-colors"
        >
            Reset
        </a>
    </div>

    <!-- 1. Availability Filter Group -->
    <div x-data="{ open: true }" class="pt-4 pb-1 border-b border-slate-100">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between text-left font-bold text-[14px] text-[#0B2454] focus:outline-none cursor-pointer"
        >
            <span>Availability</span>
            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" class="mt-3 space-y-2.5 pb-3">
            @foreach ($availabilities as $item)
                @php
                    $isChecked = in_array($item['id'], $selectedAvailabilities);
                @endphp
                <label class="flex items-center justify-between text-[13.5px] cursor-pointer group">
                    <div class="flex items-center gap-2.5">
                        <input
                            type="checkbox"
                            name="availability[]"
                            value="{{ $item['id'] }}"
                            {{ $isChecked ? 'checked' : '' }}
                            @change="$el.form.submit()"
                            class="rounded border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-4 w-4 cursor-pointer"
                        >
                        <span class="h-2 w-2 rounded-full {{ $item['color'] }} shrink-0"></span>
                        <span class="text-slate-700 group-hover:text-[#0B2454] transition-colors font-medium {{ $isChecked ? 'font-bold text-[#0B2454]' : '' }}">
                            {{ $item['label'] }}
                        </span>
                    </div>
                    <span class="text-[12px] text-slate-400 tabular-nums font-semibold">
                        {{ $item['count'] }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 2. Resource Type Filter Group -->
    <div x-data="{ open: true }" class="pt-4 pb-1 border-b border-slate-100">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between text-left font-bold text-[14px] text-[#0B2454] focus:outline-none cursor-pointer"
        >
            <span>Resource Type</span>
            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" class="mt-3 space-y-2.5 pb-3">
            @foreach ($resourceTypes as $item)
                @php
                    $isTypeSelected = ($selectedType === $item['id']);
                @endphp
                <label class="flex items-center justify-between text-[13.5px] cursor-pointer group">
                    <div class="flex items-center gap-2.5">
                        <input
                            type="radio"
                            name="type"
                            value="{{ $item['id'] }}"
                            {{ $isTypeSelected ? 'checked' : '' }}
                            @change="$el.form.submit()"
                            class="rounded-full border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-4 w-4 cursor-pointer"
                        >
                        <span class="text-slate-700 group-hover:text-[#0B2454] transition-colors font-medium {{ $isTypeSelected ? 'font-bold text-[#0B2454]' : '' }}">
                            {{ $item['label'] }}
                        </span>
                    </div>
                    <span class="text-[12px] text-slate-400 tabular-nums font-semibold">
                        {{ $item['count'] }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <!-- 3. Subject Quick Filter Group -->
    <div x-data="{ open: true, query: '' }" class="pt-4 pb-1 border-b border-slate-100">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between text-left font-bold text-[14px] text-[#0B2454] focus:outline-none cursor-pointer"
        >
            <span>Subject</span>
            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" class="mt-3 pb-3">
            <!-- Search Subject Input Box -->
            <div class="relative flex items-center mb-3">
                <svg width="16" height="16" class="absolute left-3 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    x-model="query"
                    placeholder="Search subject..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2 pl-9 pr-3 text-[13px] text-[#0B2454] placeholder:text-slate-400 focus:bg-white focus:border-[#0B2454] focus:outline-none focus:ring-1 focus:ring-[#0B2454]/20 transition-all font-medium"
                >
            </div>

            <!-- Subject Checklist -->
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                @foreach ($subjects as $subj)
                    @php
                        $isSubjSelected = in_array((string)$subj['id'], array_map('strval', $selectedSubjects));
                    @endphp
                    <label
                        x-show="!query || '{{ strtolower($subj['label']) }}'.includes(query.toLowerCase())"
                        class="flex items-center justify-between text-[13px] cursor-pointer group"
                    >
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="subject[]"
                                value="{{ $subj['id'] }}"
                                {{ $isSubjSelected ? 'checked' : '' }}
                                @change="$el.form.submit()"
                                class="rounded border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-3.5 w-3.5 cursor-pointer"
                            >
                            <span class="text-slate-600 group-hover:text-[#0B2454] transition-colors {{ $isSubjSelected ? 'font-bold text-[#0B2454]' : '' }}">
                                {{ $subj['label'] }}
                            </span>
                        </div>
                        <span class="text-[11.5px] text-slate-400 tabular-nums font-medium">
                            {{ $subj['count'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 4. Publication Year Filter Group -->
    <div x-data="{ open: true }" class="pt-4 pb-2">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between text-left font-bold text-[14px] text-[#0B2454] focus:outline-none cursor-pointer"
        >
            <span>Publication Year</span>
            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" class="mt-3 pb-3">
            <div class="flex items-center gap-2">
                <input
                    type="number"
                    name="year_from"
                    value="{{ $yearFrom }}"
                    placeholder="From"
                    min="1900"
                    max="{{ date('Y') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2 px-2.5 text-center text-[13px] text-[#0B2454] placeholder:text-slate-400 focus:bg-white focus:border-[#0B2454] focus:outline-none focus:ring-1 focus:ring-[#0B2454]/20 font-medium"
                >
                <span class="text-slate-400 text-sm font-bold">—</span>
                <input
                    type="number"
                    name="year_to"
                    value="{{ $yearTo }}"
                    placeholder="To"
                    min="1900"
                    max="{{ date('Y') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2 px-2.5 text-center text-[13px] text-[#0B2454] placeholder:text-slate-400 focus:bg-white focus:border-[#0B2454] focus:outline-none focus:ring-1 focus:ring-[#0B2454]/20 font-medium"
                >
            </div>
            <button
                type="submit"
                class="mt-2.5 w-full py-1.5 rounded-lg bg-slate-100 hover:bg-[#0B2454] hover:text-white text-[12px] font-semibold text-slate-600 transition-colors cursor-pointer"
            >
                Apply Years
            </button>
        </div>
    </div>

    <!-- Clear All Filters Button -->
    <div class="pt-3 border-t border-slate-100">
        <a
            href="{{ route('opac.index') }}"
            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-200 text-[13px] font-semibold text-slate-600 hover:text-[#0B2454] hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer"
        >
            <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Clear all filters</span>
        </a>
    </div>

</form>
