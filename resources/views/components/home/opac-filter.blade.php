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
    $selectedAvailabilities = array_values(array_map('strval', (array) $selectedAvailabilities));
    $selectedSubjects = array_values(array_map('strval', (array) $selectedSubjects));
    $selectedType = (string) ($selectedType ?: 'all');
@endphp

<!-- Filter Sidebar Card -->
<form
    id="{{ $formId }}"
    action="{{ route('opac.index') }}"
    method="GET"
    x-data="{
        selectedAvailabilities: {{ Js::from($selectedAvailabilities) }},
        selectedType: {{ Js::from($selectedType) }},
        selectedSubjects: {{ Js::from($selectedSubjects) }},
        yearFrom: {{ Js::from($yearFrom ? (string)$yearFrom : '') }},
        yearTo: {{ Js::from($yearTo ? (string)$yearTo : '') }},
        query: '',

        submitFilter() {
            window.dispatchEvent(new CustomEvent('opac-filter-trigger', {
                detail: new FormData(this.$el)
            }));
        },
        resetFilter() {
            this.selectedAvailabilities = [];
            this.selectedType = 'all';
            this.selectedSubjects = [];
            this.yearFrom = '';
            this.yearTo = '';
            this.query = '';
            window.dispatchEvent(new CustomEvent('opac-filter-reset'));
        },
        syncFilters(detail) {
            if (!detail) return;
            if (Array.isArray(detail.selectedAvailabilities)) {
                this.selectedAvailabilities = detail.selectedAvailabilities.map(String);
            }
            if (detail.selectedType !== undefined) {
                this.selectedType = detail.selectedType || 'all';
            }
            if (Array.isArray(detail.selectedSubjects)) {
                this.selectedSubjects = detail.selectedSubjects.map(String);
            }
            if (detail.yearFrom !== undefined) {
                this.yearFrom = detail.yearFrom ? String(detail.yearFrom) : '';
            }
            if (detail.yearTo !== undefined) {
                this.yearTo = detail.yearTo ? String(detail.yearTo) : '';
            }
        },
        onReset() {
            this.selectedAvailabilities = [];
            this.selectedType = 'all';
            this.selectedSubjects = [];
            this.yearFrom = '';
            this.yearTo = '';
            this.query = '';
        }
    }"
    @opac-updated.window="syncFilters($event.detail)"
    @opac-filter-reset.window="onReset()"
    @submit.prevent="submitFilter()"
    class="w-full rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs select-none"
>
    @if (!empty($search))
        <input type="hidden" name="search" value="{{ $search }}">
    @endif

    <!-- Header / Filter Title -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <h3 class="text-[15px] font-bold text-[#0B2454] flex items-center gap-2">
            <span>Filters</span>
        </h3>
        <button
            type="button"
            @click="resetFilter()"
            class="text-[12px] font-semibold text-slate-400 hover:text-[#0B2454] transition-colors cursor-pointer"
        >
            Reset
        </button>
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
            @forelse ($availabilities as $item)
                <label class="flex items-center justify-between text-[13.5px] cursor-pointer group">
                    <div class="flex items-center gap-2.5">
                        <input
                            type="checkbox"
                            name="availability[]"
                            value="{{ $item['id'] }}"
                            x-model="selectedAvailabilities"
                            class="rounded border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-4 w-4 cursor-pointer"
                        >
                        <span class="h-2 w-2 rounded-full {{ $item['color'] }} shrink-0"></span>
                        <span
                            class="group-hover:text-[#0B2454] transition-colors font-medium"
                            :class="selectedAvailabilities.includes('{{ $item['id'] }}') ? 'font-bold text-[#0B2454]' : 'text-slate-700'"
                        >
                            {{ $item['label'] }}
                        </span>
                    </div>
                    <span class="text-[12px] text-slate-400 tabular-nums font-semibold">
                        {{ $item['count'] }}
                    </span>
                </label>
            @empty
                <p class="text-[12px] text-slate-400 italic py-1">Unable to load availability filters.</p>
            @endforelse
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
            <label class="flex items-center justify-between text-[13.5px] cursor-pointer group">
                <div class="flex items-center gap-2.5">
                    <input
                        type="radio"
                        name="type"
                        value="all"
                        x-model="selectedType"
                        class="rounded-full border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-4 w-4 cursor-pointer"
                    >
                    <span
                        class="group-hover:text-[#0B2454] transition-colors font-medium"
                        :class="selectedType === 'all' ? 'font-bold text-[#0B2454]' : 'text-slate-700'"
                    >
                        All Resources
                    </span>
                </div>
            </label>
            @forelse ($resourceTypes as $item)
                <label class="flex items-center justify-between text-[13.5px] cursor-pointer group">
                    <div class="flex items-center gap-2.5">
                        <input
                            type="radio"
                            name="type"
                            value="{{ $item['id'] }}"
                            x-model="selectedType"
                            class="rounded-full border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-4 w-4 cursor-pointer"
                        >
                        <span
                            class="group-hover:text-[#0B2454] transition-colors font-medium"
                            :class="selectedType === '{{ $item['id'] }}' ? 'font-bold text-[#0B2454]' : 'text-slate-700'"
                        >
                            {{ $item['label'] }}
                        </span>
                    </div>
                    <span class="text-[12px] text-slate-400 tabular-nums font-semibold">
                        {{ $item['count'] }}
                    </span>
                </label>
            @empty
                <p class="text-[12px] text-slate-400 italic py-1">Unable to load resource types.</p>
            @endforelse
        </div>
    </div>

    <!-- 3. Subject Quick Filter Group -->
    <div x-data="{ open: true }" class="pt-4 pb-1 border-b border-slate-100">
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
                @forelse ($subjects as $subj)
                    <label
                        x-show="!query || {{ Js::from(strtolower($subj['label'])) }}.includes(query.toLowerCase())"
                        class="flex items-center justify-between text-[13px] cursor-pointer group"
                    >
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="subject[]"
                                value="{{ $subj['id'] }}"
                                x-model="selectedSubjects"
                                class="rounded border-slate-300 text-[#0B2454] focus:ring-[#0B2454]/20 h-3.5 w-3.5 cursor-pointer"
                            >
                            <span
                                class="group-hover:text-[#0B2454] transition-colors font-medium"
                                :class="selectedSubjects.includes('{{ (string)$subj['id'] }}') ? 'font-bold text-[#0B2454]' : 'text-slate-600'"
                            >
                                {{ $subj['label'] }}
                            </span>
                        </div>
                        <span class="text-[11.5px] text-slate-400 tabular-nums font-medium">
                            {{ $subj['count'] }}
                        </span>
                    </label>
                @empty
                    <p class="text-[12px] text-slate-400 italic py-1">Unable to load subjects.</p>
                @endforelse
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
                    x-model="yearFrom"
                    placeholder="From"
                    min="1900"
                    max="{{ date('Y') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2 px-2.5 text-center text-[13px] text-[#0B2454] placeholder:text-slate-400 focus:bg-white focus:border-[#0B2454] focus:outline-none focus:ring-1 focus:ring-[#0B2454]/20 font-medium"
                >
                <span class="text-slate-400 text-sm font-bold">—</span>
                <input
                    type="number"
                    name="year_to"
                    x-model="yearTo"
                    placeholder="To"
                    min="1900"
                    max="{{ date('Y') }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2 px-2.5 text-center text-[13px] text-[#0B2454] placeholder:text-slate-400 focus:bg-white focus:border-[#0B2454] focus:outline-none focus:ring-1 focus:ring-[#0B2454]/20 font-medium"
                >
            </div>
        </div>
    </div>

    <!-- 5. Action Buttons: [ Apply Filters ] and [ Reset ] -->
    <div class="pt-4 mt-2 border-t border-slate-100 space-y-2">
        <button
            type="submit"
            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-[#0B2454] hover:bg-[#071943] active:scale-[0.98] text-white text-[13.5px] font-bold shadow-xs hover:shadow transition-all cursor-pointer"
        >
            <span>Apply Filters</span>
        </button>

        <button
            type="button"
            @click="resetFilter()"
            class="w-full flex items-center justify-center gap-1.5 py-2 px-4 rounded-xl border border-slate-200 bg-white text-[12.5px] font-semibold text-slate-500 hover:text-[#0B2454] hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer"
        >
            <svg width="13" height="13" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Reset filters</span>
        </button>
    </div>

</form>
