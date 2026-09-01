<div x-data="{
         open: @entangle('isOpen'),
         isLoading: false,
         selectedIndex: -1,
         localQuery: '',

         resultsCount() {
             let el = document.getElementById('search-results-container');
             return el ? parseInt(el.dataset.count || 0) : 0;
         },
         selectRowAtIndex(index) {
             let el = document.querySelector('#search-row-' + index + ' button');
             if (el) {
                 el.click();
             }
         },
         scrollToActiveRow() {
             this.$nextTick(() => {
                 let el = document.getElementById('search-row-' + this.selectedIndex);
                 if (el) {
                     el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                 }
             });
         }
     }"
     @search-completed.window="isLoading = false; selectedIndex = -1;"
     x-init="
         $watch('open', value => {
             if (value) {
                 localQuery = '';
                 selectedIndex = -1;
                 $nextTick(() => { $refs.searchInput.focus(); });
             } else {
                 $dispatch('search-modal-closed');
             }
         });
     "
     @keydown.arrow-down.prevent.window="if (open && !isLoading) { selectedIndex = (selectedIndex < resultsCount() - 1) ? selectedIndex + 1 : 0; scrollToActiveRow(); }"
     @keydown.arrow-up.prevent.window="if (open && !isLoading) { selectedIndex = (selectedIndex > 0) ? selectedIndex - 1 : resultsCount() - 1; scrollToActiveRow(); }"
     @keydown.enter.prevent.window="if (open && selectedIndex >= 0 && selectedIndex < resultsCount()) { selectRowAtIndex(selectedIndex); }"
     x-show="open"
     x-cloak
     class="absolute inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto"
     role="dialog"
     aria-modal="true"
>
    <!-- Dimmed backdrop overlay covering ONLY the relative page body area -->
    <div class="absolute inset-0 bg-[#071943]/60 backdrop-blur-sm transition-opacity"
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
    ></div>

    <!-- Centered Modal Content Card (Width changed to max-w-2xl) -->
    <div class="relative w-full max-w-2xl mx-4 bg-white rounded-3xl border border-[#E2E8F0] shadow-2xl overflow-hidden z-50 transition-all transform flex flex-col max-h-[90%] pb-4 font-sans"
         x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <!-- Modal Header -->
        <div class="flex items-start justify-between px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 shrink-0">
            <div class="flex flex-col">
                <h3 class="text-base font-bold text-[#102B70] tracking-tight">Search Member or Book</h3>
                <p class="text-[11px] text-[#64748B] font-medium mt-0.5">Search by name, ID, email, code, title, author, or accession number</p>
            </div>
            <button type="button"
                    @click="open = false"
                    class="text-[#64748B] hover:text-[#0F172A] p-1.5 hover:bg-slate-200/50 rounded-xl transition-all focus:outline-none"
                    aria-label="Close modal"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Search & Filters (Fixed) -->
        <div class="px-6 pt-5 pb-3 border-b border-[#E2E8F0] bg-white flex flex-col gap-4 shrink-0">
            <!-- Search bar & Dropdown Selector -->
            <div class="flex gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#64748B]">
                        <!-- Search Magnifying Glass (Hidden when loading) -->
                        <svg wire:loading.remove wire:target="searchQuery, performSearch" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <!-- Spinner (Shown when loading search queries) -->
                        <svg wire:loading wire:target="searchQuery, performSearch" class="animate-spin-custom h-4.5 w-4.5 text-[#102B70]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="localQuery"
                           wire:model.live.debounce.300ms="searchQuery"
                           @input="isLoading = true; selectedIndex = -1;"
                           placeholder="Type to search..."
                           class="w-full pl-11 pr-10 h-11 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-[14px] font-semibold text-[#0F172A] placeholder-[#64748B] tracking-wide focus:outline-none focus:ring-2 focus:ring-[#102B70]/20 focus:border-[#102B70] transition-shadow"
                    >
                    <!-- Clear Input Button (Client-side instant toggle and focus retention) -->
                    <button type="button"
                            x-show="localQuery && localQuery.length > 0"
                            @click="localQuery = ''; $wire.searchQuery = ''; $wire.performSearch(); $nextTick(() => { $refs.searchInput.focus(); });"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#94A3B8] hover:text-[#0F172A] transition-colors"
                            aria-label="Clear search query"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Pill Tabs -->
            <div class="flex items-ceter gap-2 border-b border-[#E2E8F0] pb-3">
                <button type="button"
                        wire:click="setTab('all')"
                        @click="isLoading = true; selectedIndex = -1;"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all {{ $activeTab === 'all' ? 'bg-[#102B70] text-white shadow-sm' : 'bg-slate-50 border border-[#E2E8F0] text-[#64748B] hover:bg-slate-100 hover:text-[#0F172A]' }}">
                    All Results
                </button>
                <button type="button"
                        wire:click="setTab('members')"
                        @click="isLoading = true; selectedIndex = -1;"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all {{ $activeTab === 'members' ? 'bg-[#102B70] text-white shadow-sm' : 'bg-slate-50 border border-[#E2E8F0] text-[#64748B] hover:bg-slate-100 hover:text-[#0F172A]' }}">
                    Students ({{ $memberCount }})
                </button>
                <button type="button"
                        wire:click="setTab('books')"
                        @click="isLoading = true; selectedIndex = -1;"
                        class="px-4 py-2 rounded-full text-xs font-bold transition-all {{ $activeTab === 'books' ? 'bg-[#102B70] text-white shadow-sm' : 'bg-slate-50 border border-[#E2E8F0] text-[#64748B] hover:bg-slate-100 hover:text-[#0F172A]' }}">
                    Books ({{ $bookCount }})
                </button>
            </div>
        </div>

        <!-- Scrollable Results Section -->
        <div class="px-6 py-4 flex flex-col gap-4 overflow-y-auto flex-1 custom-scrollbar bg-slate-50/10">
            <!-- Alpine Skeletal Loading Design -->
            <div x-show="isLoading" class="flex flex-col gap-4" x-cloak>
                @if($activeTab === 'all' || $activeTab === 'members')
                    <div class="flex flex-col gap-2">
                        <span class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider pl-1">Searching Students...</span>
                        <div class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden divide-y divide-[#F1F5F9]">
                            @for($i = 0; $i < 2; $i++)
                                <div class="flex items-center justify-between p-3.5">
                                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 animate-pulse shrink-0"></div>
                                        <div class="flex-1 flex flex-col gap-1.5 min-w-0">
                                            <div class="w-24 h-4 bg-slate-100 animate-pulse rounded"></div>
                                            <div class="w-48 h-3 bg-slate-50 animate-pulse rounded"></div>
                                        </div>
                                    </div>
                                    <div class="w-20 h-7 bg-slate-100 animate-pulse rounded-xl shrink-0"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif

                @if($activeTab === 'all' || $activeTab === 'books')
                    <div class="flex flex-col gap-2 {{ $activeTab === 'all' ? 'mt-1' : '' }}">
                        <span class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider pl-1">Searching Books...</span>
                        <div class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden divide-y divide-[#F1F5F9]">
                            @for($i = 0; $i < 2; $i++)
                                <div class="flex items-center justify-between p-3.5">
                                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                        <div class="w-9 h-12 bg-slate-100 animate-pulse rounded shrink-0"></div>
                                        <div class="flex-1 flex flex-col gap-1.5 min-w-0">
                                            <div class="w-32 h-4 bg-slate-100 animate-pulse rounded"></div>
                                            <div class="w-40 h-3 bg-slate-50 animate-pulse rounded"></div>
                                        </div>
                                    </div>
                                    <div class="w-20 h-7 bg-slate-100 animate-pulse rounded-xl shrink-0"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif
            </div>

            <!-- Results Section List -->
            <div id="search-results-container" data-count="{{ count($results) }}" x-show="!isLoading" class="flex flex-col gap-4">
                @php
                    $membersList = collect($results)->where('type', 'member');
                    $booksList = collect($results)->where('type', 'book');
                    $currentIndex = 0;
                @endphp

                <!-- Members Group -->
                @if($membersList->isNotEmpty())
                    <div class="flex flex-col gap-2">
                        <span class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider pl-1">Students</span>

                        <div class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden shadow-inner divide-y divide-[#F1F5F9]">
                            @foreach($membersList as $item)
                                @php
                                    $isActive = strtolower($item['status'] ?? 'active') === 'active';
                                    $itemIndex = $currentIndex++;
                                @endphp
                                <div :class="selectedIndex === {{ $itemIndex }} ? 'bg-[#EFF6FF]/65' : 'hover:bg-[#F8FAFC]'"
                                     class="relative flex items-center justify-between p-3.5 pl-[18px] transition-all cursor-pointer"
                                     @click="selectedIndex = {{ $itemIndex }}"
                                     id="search-row-{{ $itemIndex }}"
                                >
                                    <!-- Highlight indicator line -->
                                    <div x-show="selectedIndex === {{ $itemIndex }}" class="absolute inset-y-0 left-0 w-1 bg-[#102B70] z-10"></div>
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <!-- Avatar -->
                                        <div class="w-9 h-9 rounded-full bg-slate-50 border border-[#E2E8F0] flex items-center justify-center shrink-0 text-[#102B70]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-bold text-[14px] text-[#0F172A] leading-snug truncate">{{ $item['title'] }}</h4>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-bold border {{ $isActive ? 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]' : 'bg-[#FEF2F2] text-[#B91C1C] border-[#FECACA]' }}">
                                                    {{ $item['status'] }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-[#64748B] font-medium mt-1 truncate">
                                                <span>{{ $item['code'] }}</span>
                                                <span class="mx-1 text-[#CBD5E1]">&bull;</span>
                                                <span>{{ $item['subtitle'] }}</span>
                                                @if($item['email'])
                                                    <span class="mx-1 text-[#CBD5E1]">&bull;</span>
                                                    <span class="text-[#94A3B8]">{{ $item['email'] }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Select Action -->
                                    <button type="button"
                                            @click="open = false; $dispatch('set-search-value', { code: '{{ $item['code'] }}' })"
                                            class="h-8 px-4 border border-[#102B70] text-[#102B70] hover:bg-[#102B70] hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shrink-0"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Select Student
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Books Group -->
                @if($booksList->isNotEmpty())
                    <div class="flex flex-col gap-2 {{ $membersList->isNotEmpty() ? 'mt-1' : '' }}">
                        <span class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-wider pl-1">Books</span>

                        <div class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden shadow-inner divide-y divide-[#F1F5F9]">
                            @foreach($booksList as $item)
                                @php
                                    $itemIndex = $currentIndex++;
                                @endphp
                                <div :class="selectedIndex === {{ $itemIndex }} ? 'bg-[#EFF6FF]/65' : 'hover:bg-[#F8FAFC]'"
                                     class="relative flex items-center justify-between p-3.5 pl-[18px] transition-all cursor-pointer"
                                     @click="selectedIndex = {{ $itemIndex }}"
                                     id="search-row-{{ $itemIndex }}"
                                >
                                    <!-- Highlight indicator line -->
                                    <div x-show="selectedIndex === {{ $itemIndex }}" class="absolute inset-y-0 left-0 w-1 bg-[#102B70] z-10"></div>
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <!-- Book cover container (Fixed w-9 h-12) -->
                                        <div class="w-9 h-12 bg-slate-50 border border-[#E2E8F0] rounded overflow-hidden flex items-center justify-center shrink-0">
                                            @if(!empty($item['cover_image']))
                                                <img src="{{ asset('storage/' . $item['cover_image']) }}"
                                                     alt="Cover"
                                                     clas="w-full h-full object-cover"
                                                     onerror="this.onerror=null; this.src=''; this.prentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-[#102B70] to-[#F59E0B] flex items-center justify-center\'><span class=\'text-[8px] text-white/80 font-bold uppercase tracking-wider text-center px-1 leading-tight\'>{{ $item['code_tag'] }}</span></div>';"
                                                >
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-[#102B70] to-[#F59E0B] flex items-center justify-center">
                                                    <span class="text-[8px] text-white/80 font-bold uppercase tracking-wider text-center px-1 leading-tight">{{ $item['code_tag'] }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="font-bold text-[14px] text-[#0F172A] leading-snug truncate">{{ $item['title'] }}</h4>
                                            <p class="text-xs text-[#64748B] font-medium mt-1 truncate">{{ $item['subtitle'] }}</p>
                                            <p class="text-[11px] text-[#94A3B8] font-medium mt-0.5 truncate">Code: {{ $item['barcode'] }}</p>
                                        </div>
                                    </div>

                                    <!-- Select Action -->
                                    <button type="button"
                                            @click="open = false; $dispatch('set-search-value', { code: '{{ $item['code'] }}' })"
                                            class="h-8 px-4 border border-[#102B70] text-[#102B70] hover:bg-[#102B70] hover:text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shrink-0"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        Select Book
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Lazy Loading / Load More Results Action -->
                @if(($activeTab === 'all' && ($memberCount > $perPage || $bookCount > $perPage)) ||
                    ($activeTab === 'members' && $memberCount > $perPage) ||
                    ($activeTab === 'books' && $bookCount > $perPage))
                    <div class="flex justify-center mt-2 shrink-0">
                        <button type="button"
                                wire:click="loadMore"
                                @click="isLoading = true; selectedIndex = -1;"
                                class="h-8 px-5 border border-[#E2E8F0] hover:border-[#102B70] text-[#102B70] hover:bg-[#EFF6FF] rounded-xl text-xs font-bold transition-all flex items-center gap-1 shadow-sm"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            Load more results...
                        </button>
                    </div>
                @endif

                <!-- Empty States -->
                @if(strlen(trim($searchQuery)) >= 2 && count($results) === 0)
                    <div class="py-12 text-center text-xs text-[#64748B] font-semibold bg-[#F8FAFC]/50 border border-dashed border-[#E2E8F0] rounded-2xl">
                        No members or books found matching "{{ $searchQuery }}".
                    </div>
                @elseif(strlen(trim($searchQuery)) < 2)
                    <div class="py-12 text-center text-xs text-[#94A3B8] font-semibold bg-[#F8FAFC]/50 border border-dashed border-[#E2E8F0] rounded-2xl">
                        Type 2 or more characters to search.
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer links block (Fixed) -->
        <div class="mt-auto px-6 py-4 border-t border-[#E2E8F0] flex items-center justify-center text-xs font-semibold text-[#64748B] gap-1.5 shrink-0 bg-slate-50/50">
                <span>Can't find what you're looking for?</span>
                <a href="/admin/user-management" class="text-[#102B70] hover:text-[#0B225E] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Add new member
                </a>
                <span class="text-[#94A3B8]">&bull;</span>
                <a href="/admin/books" class="text-[#102B70] hover:text-[#0B225E] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Add new book
                </a>
            </div>
        </div>
    </div>
</div>
