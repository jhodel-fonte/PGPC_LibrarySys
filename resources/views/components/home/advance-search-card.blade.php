@props([
    'actionUrl' => route('opac.advanced'),
    'hasResults' => null,
])

@php
    $hasSearched = $hasResults ?? (
        request()->filled('title') ||
        request()->filled('author') ||
        request()->filled('subject') ||
        request()->filled('isbn') ||
        (request()->filled('type') && request('type') !== 'all') ||
        (request()->filled('availability') && request('availability') !== 'all') ||
        request()->filled('year_from') ||
        request()->filled('year_to') ||
        (request()->filled('location') && request('location') !== 'all')
    );
@endphp

<div
    class="w-full max-w-[1380px] mx-auto select-none"
    x-data="{
        isCollapsed: {{ $hasSearched ? 'true' : 'false' }},
        title: @js(request('title', '')),
        author: @js(request('author', '')),
        subject: @js(request('subject', '')),
        isbn: @js(request('isbn', '')),
        type: @js(request('type', 'all')),
        availability: @js(request('availability', 'all')),
        year_from: @js(request('year_from', '')),
        year_to: @js(request('year_to', '')),
        location: @js(request('location', 'all')),
        matchType: @js(request('match', 'all')),

        clearForm() {
            // Reset local reactive state first (covers the case where the
            // form is still open and hasn't been submitted yet).
            this.title = '';
            this.author = '';
            this.subject = '';
            this.isbn = '';
            this.type = 'all';
            this.availability = 'all';
            this.year_from = '';
            this.year_to = '';
            this.location = 'all';
            this.matchType = 'all';
            this.isCollapsed = false;

            // If we're on a results page (query params already applied),
            // resetting local state alone changes nothing on screen because
            // this is a GET form — the old params are still in the URL and
            // nothing gets re-submitted. Navigate to the clean action URL
            // so the filters are actually cleared and results refresh.
            if (window.location.search.length > 1) {
                window.location.href = '{{ $actionUrl }}';
            }
        }
    }"
>
    <!-- Top Navigation: Back to OPAC -->
    <div class="mb-5">
        <a
            href="{{ route('opac.index') }}"
            class="inline-flex items-center gap-2 text-[14px] sm:text-[14.5px] font-semibold text-[#102B70] hover:text-[#0B225E] transition-colors group cursor-pointer"
        >
            <svg width="18" height="18" class="h-4.5 w-4.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to OPAC</span>
        </a>
    </div>

    <!-- Advanced Search Card Container -->
    <div class="rounded-[14px] border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.06)] transition-all">

        <!-- 1. Card Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4" :class="isCollapsed ? 'mb-0' : 'mb-6 sm:mb-8'">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-[24px] sm:text-[26px] font-bold text-[#0B2454] leading-tight tracking-tight">
                        Search the Library Collection
                    </h2>
                </div>
                <p class="text-[15px] sm:text-[16px] text-[#64748B] mt-1.5 leading-normal font-normal">
                    Enter one or more search criteria to narrow your results.
                </p>
            </div>

            @if($hasSearched)
                <!-- Collapse / Expand Toggle Button -->
                <button
                    type="button"
                    @click="isCollapsed = !isCollapsed"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D7E0EC] bg-slate-50 hover:bg-slate-100 hover:border-slate-300 text-[#102B70] text-[13.5px] sm:text-sm font-semibold transition-all shadow-2xs self-start sm:self-auto cursor-pointer"
                    :title="isCollapsed ? 'Modify / Refine search filters' : 'Collapse search filters'"
                >
                    <span x-text="isCollapsed ? 'Modify / Refine Filters' : 'Hide Search Form'"></span>
                    <svg
                        class="w-4 h-4 transition-transform duration-200"
                        :class="isCollapsed ? 'rotate-0' : 'rotate-180'"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            @endif
        </div>

        <!-- Advanced Search Form -->
        <form
            x-ref="searchForm"
            x-show="!isCollapsed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            action="{{ $actionUrl }}"
            method="GET"
            class="space-y-6 sm:space-y-8"
        >
            <!-- 2. Main Form Grid (2 Equal Columns on Desktop / Tablet, 1 Column on Mobile) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-5 sm:gap-y-6">

                <!-- Row 1, Col 1: Title -->
                <div>
                    <label for="adv_title" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                        Title
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="adv_title"
                            name="title"
                            x-model="title"
                            placeholder="e.g. Introduction to Algorithms, Database Systems"
                            class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-4 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium"
                        >
                    </div>
                </div>

                <!-- Row 1, Col 2: Author -->
                <div>
                    <label for="adv_author" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                        Author
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="adv_author"
                            name="author"
                            x-model="author"
                            placeholder="e.g. Tanenbaum, Andrew S. or Donald Knuth"
                            class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-4 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium"
                        >
                    </div>
                </div>

                <!-- Row 2, Col 1: Subject / Keyword -->
                <div>
                    <label for="adv_subject" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                        Subject / Keyword
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="adv_subject"
                            name="subject"
                            x-model="subject"
                            placeholder="e.g. Artificial Intelligence, Data Structures, Physics"
                            class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-4 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium"
                        >
                    </div>
                </div>

                <!-- Row 2, Col 2: ISBN -->
                <div>
                    <label for="adv_isbn" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                        ISBN
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="adv_isbn"
                            name="isbn"
                            x-model="isbn"
                            placeholder="e.g. 978-0-13-110362-7 or 0-13-110362-8"
                            class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-4 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium"
                        >
                    </div>
                </div>

                <!-- Row 3, Col 1: Resource Type -->
                <div>
                    <label for="adv_type" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                        Resource Type
                    </label>
                    <div class="relative flex items-center">
                        <!-- Left Icon: Document / Book icon -->
                        <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        <select
                            id="adv_type"
                            name="type"
                            x-model="type"
                            class="h-[50px] w-full appearance-none rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-10 text-[15px] text-[#1E3559] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium cursor-pointer"
                        >
                            <option value="all">All Resources</option>
                            <option value="books">Books</option>
                            <option value="theses">Theses</option>
                            <option value="journals">Journals</option>
                            <option value="reports">Reports</option>
                            <option value="multimedia">Multimedia</option>
                        </select>
                        <span class="absolute right-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="15" height="15" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-[13px] sm:text-[13.5px] text-[#7B8CA6] mt-2 leading-tight font-normal">
                        Books, Theses, Journals, Reports, Multimedia, and more.
                    </p>
                </div>

                <!-- Row 3, Col 2: Availability -->
                <div>
                    <label for="adv_availability" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                        Availability
                    </label>
                    <div class="relative flex items-center">
                        <!-- Left Icon: Calendar / Status icon -->
                        <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <select
                            id="adv_availability"
                            name="availability"
                            x-model="availability"
                            class="h-[50px] w-full appearance-none rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-10 text-[15px] text-[#1E3559] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium cursor-pointer"
                        >
                            <option value="all">Any Availability</option>
                            <option value="available">Available</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="reserved">Reserved</option>
                            <option value="reference_only">Reference Only</option>
                        </select>
                        <span class="absolute right-4 pointer-events-none text-[#8A9AB2]">
                            <svg width="15" height="15" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-[13px] sm:text-[13.5px] text-[#7B8CA6] mt-2 leading-tight font-normal">
                        Available, Checked Out, Reserved, Reference Only.
                    </p>
                </div>

            </div>

            <div class="border-t border-[#E3E9F1] pt-5 sm:pt-6"></div>
            <div>
                <h3 class="text-[17px] sm:text-[18px] font-bold text-[#0B2454] leading-tight mb-3.5">
                    Additional Filters
                </h3>

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-5 sm:gap-y-6">

                    <div>
                        <label class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                            Publication Year
                        </label>
                        <div class="flex items-center gap-3">
                            <!-- From Year -->
                            <div
                                class="relative flex-1"
                                x-data="{
                                    open: false,
                                    pickerDecadeStart: Math.floor((parseInt(year_from) || new Date().getFullYear()) / 12) * 12,
                                    get yearsList() {
                                        return Array.from({ length: 12 }, (_, i) => this.pickerDecadeStart + i);
                                    },
                                    selectYear(yr) {
                                        year_from = yr;
                                        this.open = false;
                                    },
                                    prevDecade() {
                                        if (this.pickerDecadeStart > 1900) this.pickerDecadeStart -= 12;
                                    },
                                    nextDecade() {
                                        if (this.pickerDecadeStart + 12 <= {{ (int)date('Y') + 10 }}) this.pickerDecadeStart += 12;
                                    }
                                }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                            >
                                <div class="relative flex items-center">
                                    <input
                                        type="number"
                                        name="year_from"
                                        x-model="year_from"
                                        placeholder="e.g. 2018"
                                        min="1900"
                                        max="{{ date('Y') }}"
                                        class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-4 pr-10 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium text-left"
                                    >
                                    <button
                                        type="button"
                                        @click="open = !open; if(open) pickerDecadeStart = Math.floor((parseInt(year_from) || new Date().getFullYear()) / 12) * 12;"
                                        class="absolute right-2 h-8 w-8 flex items-center justify-center rounded-md text-[#8A9AB2] hover:text-[#102B70] hover:bg-slate-100 transition-colors cursor-pointer"
                                        title="Pick publication year"
                                    >
                                        <svg width="17" height="17" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Year Picker Dropdown -->
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                    class="absolute left-0 top-full mt-1.5 z-50 w-[240px] sm:w-[260px] rounded-xl bg-white border border-[#D7E0EC] p-3 shadow-[0_10px_25px_rgba(7,26,61,0.12)]"
                                    style="display: none;"
                                >
                                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                        <button
                                            type="button"
                                            @click="prevDecade()"
                                            class="h-7 w-7 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 hover:text-[#102B70] transition-colors cursor-pointer"
                                            title="Previous years"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <span class="text-[13px] font-bold text-[#102B70]" x-text="pickerDecadeStart + ' – ' + (pickerDecadeStart + 11)"></span>
                                        <button
                                            type="button"
                                            @click="nextDecade()"
                                            class="h-7 w-7 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 hover:text-[#102B70] transition-colors cursor-pointer"
                                            title="Next years"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-4 gap-1.5 text-center">
                                        <template x-for="yr in yearsList" :key="yr">
                                            <button
                                                type="button"
                                                @click="selectYear(yr)"
                                                :disabled="yr > {{ (int)date('Y') }}"
                                                class="py-1.5 px-1 text-[12.5px] rounded-lg font-semibold transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                                :class="parseInt(year_from) === yr ? 'bg-[#102B70] text-white shadow-xs font-bold' : (yr === new Date().getFullYear() ? 'bg-[#EFF6FF] text-[#102B70] font-bold hover:bg-[#DBEAFE]' : 'text-slate-700 hover:bg-slate-100 hover:text-[#102B70]')"
                                                x-text="yr"
                                            ></button>
                                        </template>
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11.5px]">
                                        <button
                                            type="button"
                                            @click="selectYear({{ (int)date('Y') }})"
                                            class="text-[#102B70] font-semibold hover:underline cursor-pointer"
                                        >
                                            This Year ({{ date('Y') }})
                                        </button>
                                        <button
                                            type="button"
                                            @click="year_from = ''; open = false;"
                                            class="text-slate-400 hover:text-rose-600 font-medium transition-colors cursor-pointer"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <span class="text-[15px] text-[#7B8CA6] font-medium px-1.5 shrink-0">
                                to
                            </span>

                            <!-- To Year -->
                            <div
                                class="relative flex-1"
                                x-data="{
                                    open: false,
                                    pickerDecadeStart: Math.floor((parseInt(year_to) || new Date().getFullYear()) / 12) * 12,
                                    get yearsList() {
                                        return Array.from({ length: 12 }, (_, i) => this.pickerDecadeStart + i);
                                    },
                                    selectYear(yr) {
                                        year_to = yr;
                                        this.open = false;
                                    },
                                    prevDecade() {
                                        if (this.pickerDecadeStart > 1900) this.pickerDecadeStart -= 12;
                                    },
                                    nextDecade() {
                                        if (this.pickerDecadeStart + 12 <= {{ (int)date('Y') + 10 }}) this.pickerDecadeStart += 12;
                                    }
                                }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                            >
                                <div class="relative flex items-center">
                                    <input
                                        type="number"
                                        name="year_to"
                                        x-model="year_to"
                                        placeholder="e.g. 2026"
                                        min="1900"
                                        max="{{ date('Y') }}"
                                        class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-4 pr-10 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium text-left"
                                    >
                                    <button
                                        type="button"
                                        @click="open = !open; if(open) pickerDecadeStart = Math.floor((parseInt(year_to) || new Date().getFullYear()) / 12) * 12;"
                                        class="absolute right-2 h-8 w-8 flex items-center justify-center rounded-md text-[#8A9AB2] hover:text-[#102B70] hover:bg-slate-100 transition-colors cursor-pointer"
                                        title="Pick publication year"
                                    >
                                        <svg width="17" height="17" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Year Picker Dropdown -->
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                    class="absolute left-0 top-full mt-1.5 z-50 w-[240px] sm:w-[260px] rounded-xl bg-white border border-[#D7E0EC] p-3 shadow-[0_10px_25px_rgba(7,26,61,0.12)]"
                                    style="display: none;"
                                >
                                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                                        <button
                                            type="button"
                                            @click="prevDecade()"
                                            class="h-7 w-7 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 hover:text-[#102B70] transition-colors cursor-pointer"
                                            title="Previous years"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <span class="text-[13px] font-bold text-[#102B70]" x-text="pickerDecadeStart + ' – ' + (pickerDecadeStart + 11)"></span>
                                        <button
                                            type="button"
                                            @click="nextDecade()"
                                            class="h-7 w-7 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 hover:text-[#102B70] transition-colors cursor-pointer"
                                            title="Next years"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-4 gap-1.5 text-center">
                                        <template x-for="yr in yearsList" :key="yr">
                                            <button
                                                type="button"
                                                @click="selectYear(yr)"
                                                :disabled="yr > {{ (int)date('Y') }}"
                                                class="py-1.5 px-1 text-[12.5px] rounded-lg font-semibold transition-all cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                                :class="parseInt(year_to) === yr ? 'bg-[#102B70] text-white shadow-xs font-bold' : (yr === new Date().getFullYear() ? 'bg-[#EFF6FF] text-[#102B70] font-bold hover:bg-[#DBEAFE]' : 'text-slate-700 hover:bg-slate-100 hover:text-[#102B70]')"
                                                x-text="yr"
                                            ></button>
                                        </template>
                                    </div>

                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11.5px]">
                                        <button
                                            type="button"
                                            @click="selectYear({{ (int)date('Y') }})"
                                            class="text-[#102B70] font-semibold hover:underline cursor-pointer"
                                        >
                                            This Year ({{ date('Y') }})
                                        </button>
                                        <button
                                            type="button"
                                            @click="year_to = ''; open = false;"
                                            class="text-slate-400 hover:text-rose-600 font-medium transition-colors cursor-pointer"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Location -->
                    <div>
                        <label for="adv_location" class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                            Location
                        </label>
                        <div class="relative flex items-center">
                            <!-- Left Icon: Map Pin -->
                            <span class="absolute left-4 pointer-events-none text-[#8A9AB2]">
                                <svg width="18" height="18" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <select
                                id="adv_location"
                                name="location"
                                x-model="location"
                                class="h-[50px] w-full appearance-none rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-10 text-[15px] text-[#1E3559] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium cursor-pointer"
                            >
                                <option value="all">School Library</option>

                            </select>
                            <span class="absolute right-4 pointer-events-none text-[#8A9AB2]">
                                <svg width="15" height="15" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                        <p class="text-[13px] sm:text-[13.5px] text-[#7B8CA6] mt-2 leading-tight font-normal">
                            Filter by specific library branch or location.
                        </p>
                    </div>

                </div>
            </div>

            <!-- 5. Bottom Divider -->
            <div class="border-t border-[#E3E9F1] pt-5 sm:pt-6"></div>

            <!-- 6. Bottom Area: Search Options + Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-5 pt-1">

                <!-- Left: Search Options -->
                <div>
                    <h4 class="text-[15px] sm:text-[16px] font-bold text-[#0B2454] mb-2.5 leading-tight">
                        Search options
                    </h4>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-7">
                        <!-- Option 1: Match all criteria -->
                        <label class="flex items-start gap-2.5 cursor-pointer group">
                            <input
                                type="radio"
                                name="match"
                                value="all"
                                x-model="matchType"
                                class="mt-0.5 h-4.5 w-4.5 text-[#F9C000] accent-[#F9C000] focus:ring-[#F9C000] focus:ring-offset-0 border-[#D7E0EC] cursor-pointer"
                            >
                            <div>
                                <span class="block text-[14.5px] sm:text-[15px] font-semibold text-[#132B53] leading-snug group-hover:text-[#0B2454]">
                                    Match all criteria
                                </span>
                                <span class="block text-[12.5px] sm:text-[13px] text-[#7B8CA6] leading-tight mt-0.5 font-normal">
                                    Results must match all the criteria above.
                                </span>
                            </div>
                        </label>

                        <!-- Option 2: Match any criteria -->
                        <label class="flex items-start gap-2.5 cursor-pointer group">
                            <input
                                type="radio"
                                name="match"
                                value="any"
                                x-model="matchType"
                                class="mt-0.5 h-4.5 w-4.5 text-[#F9C000] accent-[#F9C000] focus:ring-[#F9C000] focus:ring-offset-0 border-[#D7E0EC] cursor-pointer"
                            >
                            <div>
                                <span class="block text-[14.5px] sm:text-[15px] font-semibold text-[#132B53] leading-snug group-hover:text-[#0B2454]">
                                    Match any criteria
                                </span>
                                <span class="block text-[12.5px] sm:text-[13px] text-[#7B8CA6] leading-tight mt-0.5 font-normal">
                                    Results may match any of the criteria above.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Right: Action Buttons ([ Clear ] [ Search Catalog ]) -->
                <div class="flex items-center gap-3 shrink-0 self-stretch sm:self-auto justify-end">
                    <!-- Clear Button -->
                    <button
                        type="button"
                        @click="clearForm()"
                        class="h-[48px] px-7 rounded-lg border border-[#BCC9DA] bg-white text-[15px] font-semibold text-[#173664] hover:bg-slate-50 hover:border-slate-400 active:scale-[0.98] transition-all cursor-pointer whitespace-nowrap shadow-2xs"
                    >
                        Clear
                    </button>

                    <!-- Search Catalog Button (Primary Strongest Element) -->
                    <button
                        type="submit"
                        class="h-[48px] px-7 sm:px-8 rounded-lg bg-[#F9C000] hover:bg-[#E9B500] active:scale-[0.98] text-[#071A3D] text-[15.5px] font-bold shadow-xs hover:shadow transition-all flex items-center justify-center gap-2.5 cursor-pointer whitespace-nowrap"
                    >
                        <svg width="18" height="18" class="h-4.5 w-4.5 text-[#071A3D] stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Search Catalog</span>
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>