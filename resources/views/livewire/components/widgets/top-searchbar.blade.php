@php
    if ($isStaff) {
        if ($canViewBooks && $canViewStudents) {
            $searchPlaceholder = 'Search books, authors, call numbers, patrons...';
        } elseif ($canViewBooks) {
            $searchPlaceholder = 'Search books, authors, call numbers...';
        } elseif ($canViewStudents) {
            $searchPlaceholder = 'Search patrons, students...';
        } else {
            $searchPlaceholder = 'No search permissions available...';
        }
    } else {
        $searchPlaceholder = 'Search books, authors, call numbers, subjects...';
    }
@endphp

<div
    x-data="{
        showDropdown: false,
        localQuery: $wire.get('query') || '',
        init() {
            this.$watch('$wire.query', value => {
                this.localQuery = value || '';
            });
        },
        openDropdown() {
            if (this.localQuery && this.localQuery.trim().length >= 2) {
                this.showDropdown = true;
            }
        },
        closeDropdown() {
            this.showDropdown = false;
        },
        handleInput(e) {
            this.localQuery = e.target.value;
            this.showDropdown = this.localQuery.trim().length >= 2;
        },
        clearSearch() {
            this.localQuery = '';
            this.showDropdown = false;
            $wire.clearQuery();
            this.$refs.searchInput?.focus();
        }
    }"
    @click.outside="closeDropdown()"
    @keydown.escape.window="closeDropdown()"
    class="w-full relative select-none font-sans"
>
    <!-- Search Form -->
    <form wire:submit.prevent="performSearch" class="relative group w-full">
        <!-- Search Icon (Left) -->
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#102B70] transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
            </svg>
        </div>

        <!-- Input Field -->
        <input
            x-ref="searchInput"
            wire:model.live.debounce.300ms="query"
            :value="localQuery"
            @focus="openDropdown()"
            @input="handleInput($event)"
            @keydown.escape="closeDropdown()"
            type="text"
            id="top-search-query"
            name="query"
            autocomplete="off"
            spellcheck="false"
            role="combobox"
            :aria-expanded="showDropdown"
            aria-autocomplete="list"
            aria-label="Search catalog"
            class="block w-full h-10 pl-10 pr-24 bg-slate-50 border border-[#CBD5E1] rounded-xl text-sm font-semibold text-[#0F172A] placeholder:text-slate-400 placeholder:text-sm placeholder:font-normal outline-none focus:bg-white focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all shadow-xs"
            placeholder="{{ $searchPlaceholder }}"
        >

        <!-- Right Side Controls: Clear Button & Search Action Button -->
        <div class="absolute inset-y-0 right-1.5 flex items-center gap-1">
            <!-- Clear Button (Shown only when query is filled) -->
            <button
                type="button"
                x-show="localQuery && localQuery.trim().length > 0"
                x-cloak
                @click="clearSearch()"
                class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                title="Clear search"
                aria-label="Clear search"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </form>

    <!-- Live Unified Dropdown (Controlled by Alpine) -->
    <div
        x-show="showDropdown && localQuery && localQuery.trim().length >= 2"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-[0.99]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-[0.99]"
        style="display: none;"
        class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-[0_16px_40px_rgba(16,43,112,0.14)] border border-[#E2E8F0] max-h-[460px] overflow-y-auto z-50 divide-y divide-slate-100 font-sans custom-scrollbar"
    >
        <!-- Loading State Indicator -->
        <div wire:loading.flex wire:target="query" class="p-4 items-center justify-center gap-2.5 text-sm font-semibold text-[#102B70] bg-slate-50/50">
            <svg class="animate-spin h-4 w-4 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Searching library collection...</span>
        </div>

        <!-- Loaded Results Container -->
        <div wire:loading.remove wire:target="query" class="divide-y divide-slate-100">
            <!-- 1. Books / Catalog Section (Shown only if permitted) -->
            @if($canViewBooks && $books->isNotEmpty())
                <div class="px-4 py-2.5 bg-slate-50/80 flex items-center justify-between text-xs font-bold text-[#64748B] uppercase tracking-wider">
                    <span>Books in Catalog</span>
                    <span class="text-xs text-slate-500 font-semibold">{{ $books->count() }} found</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($books as $book)
                        @php
                            $targetDetailId = $book->book_detail_id ?? $book->id;
                            $detailUrl = $isStaff
                                ? route($routePrefix . 'book-management.index', ['detail' => $targetDetailId])
                                : route('opac.book.detail', $targetDetailId);
                            $isAvail = (int) ($book->available_copies ?? 0) > 0;
                        @endphp
                        <a
                            href="{{ $detailUrl }}"
                            wire:navigate
                            @if($isStaff)
                                @click="closeDropdown(); $dispatch('open-book-details', { id: {{ $targetDetailId }}, bookId: {{ $book->id ?? $targetDetailId }} })"
                            @else
                                @click="closeDropdown()"
                            @endif
                            class="flex items-center gap-3.5 px-4 py-3 hover:bg-[#EFF6FF]/60 transition-colors group cursor-pointer"
                        >
                            @if(!empty($book->cover_url))
                                <img src="{{ $book->cover_url }}" alt="{{ $book->book_title }}" class="w-9 h-12 object-cover rounded shadow-xs border border-slate-200 shrink-0">
                            @else
                                <div class="w-9 h-12 rounded bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-[#102B70]">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-[#0F172A] group-hover:text-[#102B70] truncate transition-colors leading-tight">
                                    {{ $book->book_title }}
                                </h4>
                                <p class="text-xs text-[#64748B] truncate mt-0.5">
                                    {{ $book->authors ?: 'Unknown Author' }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if(!empty($book->call_number))
                                        <span class="font-mono text-xs bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-semibold">
                                            {{ $book->call_number }}
                                        </span>
                                    @endif
                                    <span class="text-xs font-semibold {{ $isAvail ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $book->status_label ?? ($isAvail ? 'Available' : 'Unavailable') }}
                                    </span>
                                </div>
                            </div>

                            <span class="text-[11px] font-semibold text-[#102B70] bg-[#EFF6FF] px-2 py-0.5 rounded-md border border-[#BFDBFE] shrink-0">
                                {{ $isStaff ? 'Details' : 'OPAC' }}
                            </span>

                            <svg class="w-4 h-4 text-slate-300 group-hover:text-[#102B70] group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- 2. Patrons / Students Section (Shown only if staff has permission to view students/users) -->
            @if($isStaff && $canViewStudents && $students->isNotEmpty())
                <div class="px-4 py-2.5 bg-slate-50/80 flex items-center justify-between text-xs font-bold text-[#64748B] uppercase tracking-wider">
                    <span>Students</span>
                    <span class="text-xs text-slate-500 font-semibold">{{ $students->count() }} found</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($students as $student)
                        @php
                            $sName = "{$student->first_name} {$student->last_name}";
                            $sInitials = strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1));
                            $sEncId = $student->account?->encrypted_id ?? $student->account_id;
                            $studentUrl = route($routePrefix . 'user-details', $sEncId);
                        @endphp
                        <a
                            href="{{ $studentUrl }}"
                            wire:navigate
                            @click="closeDropdown()"
                            class="flex items-center gap-3.5 px-4 py-3 hover:bg-[#EFF6FF]/60 transition-colors group cursor-pointer"
                        >
                            <div class="w-9 h-9 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $sInitials }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-[#0F172A] group-hover:text-[#102B70] truncate transition-colors leading-tight">
                                    {{ $sName }}
                                </h4>
                                <div class="flex items-center gap-1.5 text-xs text-[#64748B] truncate mt-0.5">
                                    <span class="font-mono text-xs">{{ $student->school_id_number }}</span>
                                    @if($student->program)
                                        <span>•</span>
                                        <span class="truncate">{{ $student->program }}</span>
                                    @endif
                                </div>
                            </div>

                            <span class="text-xs font-semibold text-[#102B70] bg-[#EFF6FF] px-2.5 py-1 rounded-md border border-[#BFDBFE] shrink-0">
                                Profile
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- 3. No Results State -->
            @if((!$canViewBooks || $books->isEmpty()) && (!$canViewStudents || $students->isEmpty()))
                <div class="p-6 text-center">
                    <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                    @if($isStaff && !$canViewBooks && !$canViewStudents)
                        <p class="text-sm font-bold text-[#0F172A]">No Search Permissions</p>
                        <p class="text-xs text-[#64748B] mt-1">Your account does not have permission to view library catalog records or patron profiles.</p>
                    @else
                        <p class="text-sm font-bold text-[#0F172A]">No matching records found for "<span x-text="localQuery"></span>"</p>
                        <p class="text-xs text-[#64748B] mt-1">
                            @if($isStaff)
                                @if($canViewBooks && $canViewStudents)
                                    Try searching by title, author, call number, or patron ID.
                                @elseif($canViewBooks)
                                    Try searching by title, author, ISBN, or call number.
                                @elseif($canViewStudents)
                                    Try searching by patron name or school ID number.
                                @endif
                            @else
                                Try searching by title, author, call number, or subject.
                            @endif
                        </p>
                    @endif
                </div>
            @endif

            <!-- 4. Footer View All Results -->
            <div class="p-2.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-center">
                <button
                    type="button"
                    wire:click="performSearch"
                    @click="closeDropdown()"
                    class="text-sm font-bold text-[#102B70] hover:text-[#0B225E] hover:underline flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <span>
                        @if($isStaff && $canViewBooks)
                            Press Enter or click here to view in Book Management
                        @elseif($isStaff && $canViewStudents)
                            Press Enter or click here to view in User Management
                        @else
                            Press Enter or click here to view all catalog results
                        @endif
                    </span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.25">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
