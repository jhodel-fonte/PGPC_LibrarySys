@props([
    'actionUrl' => route('opac.advanced'),
])

<div class="w-full max-w-[1380px] mx-auto select-none">
    <!-- Advanced Search Card Container -->
    <div class="rounded-[14px] border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.06)] transition-all">

        <!-- 1. Card Header -->
        <div class="mb-6 sm:mb-8">
            <h2 class="text-[24px] sm:text-[26px] font-bold text-[#0B2454] leading-tight tracking-tight">
                Search the Library Collection
            </h2>
            <p class="text-[15px] sm:text-[16px] text-[#64748B] mt-1.5 leading-normal font-normal">
                Enter one or more search criteria to narrow your results.
            </p>
        </div>

        <!-- Advanced Search Form -->
        <form
            action="{{ $actionUrl }}"
            method="GET"
            x-data="{
                matchType: '{{ request('match', 'all') }}',
                resetForm() {
                    this.$el.reset();
                    this.matchType = 'all';
                    window.location.href = '{{ route('opac.advanced') }}';
                }
            }"
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
                            value="{{ request('title') }}"
                            placeholder="Enter book or resource title"
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
                            value="{{ request('author') }}"
                            placeholder="Enter author name"
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
                            value="{{ request('subject') }}"
                            placeholder="Enter subject or keyword"
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
                            value="{{ request('isbn') }}"
                            placeholder="Enter ISBN"
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
                            class="h-[50px] w-full appearance-none rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-10 text-[15px] text-[#1E3559] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium cursor-pointer"
                        >
                            <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>All Resources</option>
                            <option value="books" {{ request('type') === 'books' ? 'selected' : '' }}>Books</option>
                            <option value="theses" {{ request('type') === 'theses' ? 'selected' : '' }}>Theses</option>
                            <option value="journals" {{ request('type') === 'journals' ? 'selected' : '' }}>Journals</option>
                            <option value="reports" {{ request('type') === 'reports' ? 'selected' : '' }}>Reports</option>
                            <option value="multimedia" {{ request('type') === 'multimedia' ? 'selected' : '' }}>Multimedia</option>
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
                            class="h-[50px] w-full appearance-none rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-10 text-[15px] text-[#1E3559] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium cursor-pointer"
                        >
                            <option value="all" {{ request('availability', 'all') === 'all' ? 'selected' : '' }}>Any Availability</option>
                            <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="checked_out" {{ request('availability') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="reserved" {{ request('availability') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="reference_only" {{ request('availability') === 'reference_only' ? 'selected' : '' }}>Reference Only</option>
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

            <!-- 3. Divider before Additional Filters -->
            <div class="border-t border-[#E3E9F1] pt-5 sm:pt-6"></div>

            <!-- 4. Additional Filters Heading -->
            <div>
                <h3 class="text-[17px] sm:text-[18px] font-bold text-[#0B2454] leading-tight mb-3.5">
                    Additional Filters
                </h3>

                <!-- Additional Filters 2-Column Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 lg:gap-x-12 gap-y-5 sm:gap-y-6">

                    <!-- Col 1: Publication Year (From Year to To Year) -->
                    <div>
                        <label class="block text-[15px] sm:text-[15.5px] font-semibold text-[#132B53] mb-2">
                            Publication Year
                        </label>
                        <div class="flex items-center gap-3">
                            <!-- From Year -->
                            <div class="relative flex-1 flex items-center">
                                <input
                                    type="number"
                                    name="year_from"
                                    value="{{ request('year_from') }}"
                                    placeholder="From Year"
                                    min="1900"
                                    max="{{ date('Y') }}"
                                    class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-4 pr-10 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium text-left"
                                >
                                <span class="absolute right-3.5 pointer-events-none text-[#8A9AB2]">
                                    <svg width="17" height="17" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            </div>

                            <span class="text-[15px] text-[#7B8CA6] font-medium px-1.5 shrink-0">
                                to
                            </span>

                            <!-- To Year -->
                            <div class="relative flex-1 flex items-center">
                                <input
                                    type="number"
                                    name="year_to"
                                    value="{{ request('year_to') }}"
                                    placeholder="To Year"
                                    min="1900"
                                    max="{{ date('Y') }}"
                                    class="h-[50px] w-full rounded-lg border border-[#D7E0EC] bg-white pl-4 pr-10 text-[15px] text-[#1E3559] placeholder:text-[15px] placeholder-[#8A9AB2] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium text-left"
                                >
                                <span class="absolute right-3.5 pointer-events-none text-[#8A9AB2]">
                                    <svg width="17" height="17" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
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
                                class="h-[50px] w-full appearance-none rounded-lg border border-[#D7E0EC] bg-white pl-11 pr-10 text-[15px] text-[#1E3559] focus:border-[#234A85] focus:outline-none focus:ring-3 focus:ring-[#234A85]/10 transition-all font-medium cursor-pointer"
                            >
                                <option value="all" {{ request('location', 'all') === 'all' ? 'selected' : '' }}>All Library Locations</option>
                                <option value="Main Library – 1st Floor" {{ request('location') === 'Main Library – 1st Floor' ? 'selected' : '' }}>Main Library – 1st Floor</option>
                                <option value="Main Library – 2nd Floor" {{ request('location') === 'Main Library – 2nd Floor' ? 'selected' : '' }}>Main Library – 2nd Floor</option>
                                <option value="Main Library – 3rd Floor" {{ request('location') === 'Main Library – 3rd Floor' ? 'selected' : '' }}>Main Library – 3rd Floor</option>
                                <option value="Reference Section – 2nd Floor" {{ request('location') === 'Reference Section – 2nd Floor' ? 'selected' : '' }}>Reference Section – 2nd Floor</option>
                                <option value="Periodicals Section – 1st Floor" {{ request('location') === 'Periodicals Section – 1st Floor' ? 'selected' : '' }}>Periodicals Section – 1st Floor</option>
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
                                {{ request('match', 'all') === 'all' ? 'checked' : '' }}
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
                                {{ request('match') === 'any' ? 'checked' : '' }}
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
                        @click="resetForm()"
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
