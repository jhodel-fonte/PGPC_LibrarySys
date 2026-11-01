<div x-data="{
        open: @entangle('isOpen'),
        isLoading: false,
        selectedIndex: -1,
        localQuery: '',
        resultsCount() { return Number(this.$refs.results?.dataset.count || 0); },
        moveSelection(direction) {
            const count = this.resultsCount();
            if (!count) return;
            this.selectedIndex = this.selectedIndex < 0 ? (direction > 0 ? 0 : count - 1) : (this.selectedIndex + direction + count) % count;
            this.$nextTick(() => document.getElementById('search-row-' + this.selectedIndex)?.scrollIntoView({ block: 'nearest' }));
        }
    }"
    x-on:open-search-modal.window="open = true; isLoading = true; selectedIndex = -1; localQuery = ''; $nextTick(() => $refs.searchInput?.focus())"
    x-on:search-completed.window="isLoading = false; selectedIndex = -1"
    x-init="$watch('open', value => { if (!value) $dispatch('search-modal-closed') })"
    x-on:keydown.escape.window="if (open) open = false"
    x-on:keydown.window="
        if (open && !isLoading && $event.target === $refs.searchInput) {
            if ($event.key === 'ArrowDown' || $event.key === 'ArrowUp') {
                $event.preventDefault(); moveSelection($event.key === 'ArrowDown' ? 1 : -1);
            } else if ($event.key === 'Enter' && selectedIndex >= 0) {
                $event.preventDefault(); document.querySelector('#search-row-' + selectedIndex + ' button')?.click();
            }
        }
    "
    x-show="open" x-cloak x-trap.noscroll="open"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5"
    role="dialog" aria-modal="true" aria-labelledby="circulation-search-title">

    <div class="absolute inset-0 bg-[#071943]/60" @click="open = false"></div>
    <div class="relative flex w-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl" style="max-width: 56rem; max-height: calc(100dvh - 1.5rem)">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6">
            <div class="min-w-0">
                <h3 id="circulation-search-title" class="text-lg font-bold text-[#102B70]">Find a student or item</h3>
                <p class="mt-1 text-sm text-slate-600">Search by name, ID, title, author, accession number{{ $service === 'check-out' ? ', or reservation reference' : '' }}.</p>
            </div>
            <button type="button" @click="open = false" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-[#102B70]" aria-label="Close search">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="shrink-0 space-y-3 border-b border-slate-200 px-4 py-4 sm:px-6">
            <label for="circulation-modal-search" class="sr-only">Search students and items</label>
            <div class="relative">
                <input id="circulation-modal-search" x-ref="searchInput" type="search" x-model="localQuery" wire:model.live.debounce.300ms="searchQuery"
                    @input="isLoading = true; selectedIndex = -1" placeholder="Search by name, ID, title, or code"
                    class="workstation-modal-search h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-base text-slate-900 placeholder-slate-500 focus:border-[#102B70] focus:outline-none focus:ring-2 focus:ring-blue-100">
                <span x-show="isLoading" x-cloak role="status" class="absolute inset-y-0 right-4 flex items-center text-xs font-semibold text-[#102B70]">Searching…</span>
            </div>
            <div class="flex flex-wrap gap-2" aria-label="Filter search results">
                @foreach(['all' => 'All', 'members' => 'Students', 'books' => 'Items'] as $tab => $label)
                    <button type="button" wire:click="setTab('{{ $tab }}')" @click="isLoading = true; selectedIndex = -1"
                        aria-pressed="{{ $activeTab === $tab ? 'true' : 'false' }}"
                        class="min-h-11 rounded-lg px-4 text-sm font-semibold {{ $activeTab === $tab ? 'bg-[#102B70] text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }}">{{ $label }}</button>
                @endforeach
                @if($service === 'check-out')
                    <button type="button" wire:click="setTab('reservations')" @click="isLoading = true; selectedIndex = -1"
                        aria-pressed="{{ $activeTab === 'reservations' ? 'true' : 'false' }}"
                        class="min-h-11 rounded-lg px-4 text-sm font-semibold {{ $activeTab === 'reservations' ? 'bg-[#102B70] text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }}">Reservations</button>
                @endif
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6" aria-live="polite">
            <div x-show="isLoading" x-cloak role="status" class="space-y-3" aria-label="Loading search results">
                @for($i = 0; $i < 3; $i++)
                    <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4 motion-safe:animate-pulse" aria-hidden="true">
                        <div class="h-10 w-10 shrink-0 rounded-lg bg-slate-100"></div>
                        <div class="flex-1 space-y-2"><div class="h-4 w-1/2 rounded bg-slate-100"></div><div class="h-3 w-2/3 rounded bg-slate-100"></div></div>
                    </div>
                @endfor
            </div>

            <div x-ref="results" data-count="{{ count($results) }}" x-show="!isLoading" class="space-y-5">
                @php
                    $groups = [
                        'member' => ['Students', 'Select student'],
                        'reservation' => ['Approved reservations', 'Select reservation'],
                        'book' => ['Items', 'Select item'],
                    ];
                    $currentIndex = 0;
                @endphp
                @foreach($groups as $type => [$heading, $action])
                    @php $items = collect($results)->where('type', $type); @endphp
                    @if($items->isNotEmpty())
                        <section aria-label="{{ $heading }}">
                            <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-600">{{ $heading }}</h4>
                            <div class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200">
                                @foreach($items as $item)
                                    @php $itemIndex = $currentIndex++; @endphp
                                    <div id="search-row-{{ $itemIndex }}" :class="selectedIndex === {{ $itemIndex }} ? 'bg-blue-50' : 'bg-white'" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="break-words text-sm font-bold text-slate-900">{{ $item['title'] }}</p>
                                                @if($type === 'member')<span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $item['status'] }}</span>@endif
                                            </div>
                                            @if(!empty($item['subtitle']))<p class="mt-1 break-words text-sm text-slate-600">{{ $item['subtitle'] }}</p>@endif
                                            <p class="mt-1 break-all text-xs text-slate-600">
                                                {{ $item['code'] }}@if($type === 'member' && !empty($item['email'])) · {{ $item['email'] }}@endif
                                                @if($type === 'reservation') · {{ $item['student_id'] }} · {{ $item['accession'] }}@endif
                                            </p>
                                        </div>
                                        <button type="button" @click="$dispatch('set-search-value', { code: {{ Js::from($item['code']) }} }); open = false"
                                            class="min-h-11 shrink-0 rounded-lg border border-[#102B70] px-4 text-sm font-semibold text-[#102B70] hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]">{{ $action }}</button>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach

                @if(count($results) === 0)
                    <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-600">
                        {{ trim($searchQuery) === '' ? 'No students or items are available.' : 'No results found. Try another name, title, or code.' }}
                    </p>
                @endif
                @if(($activeTab === 'all' && ($memberCount > $perPage || $bookCount > $perPage || $reservationCount > $perPage)) ||
                    ($activeTab === 'members' && $memberCount > $perPage) || ($activeTab === 'books' && $bookCount > $perPage) ||
                    ($activeTab === 'reservations' && $reservationCount > $perPage))
                    <button type="button" wire:click="loadMore" @click="isLoading = true; selectedIndex = -1" class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-[#102B70] hover:bg-slate-50">Load more</button>
                @endif
            </div>
        </div>

        @php
            $prefix = \App\Services\EmployeeRoleService::getRoutePrefix();
            $canAddStudent = \App\Services\EmployeeRoleService::canCreateStudent() && \App\Services\EmployeeRoleService::canViewStudents();
            $canAddBook = \App\Services\EmployeeRoleService::canCreateBook() && \App\Services\EmployeeRoleService::canManageCatalog();
        @endphp
        @if($canAddStudent || $canAddBook)
            <div class="flex shrink-0 flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-200 px-4 py-4 text-sm sm:px-6">
                <span class="text-slate-600">Can't find a record?</span>
                @if($canAddStudent)<a href="{{ route($prefix . 'user-management.add') }}" class="font-semibold text-[#102B70] underline underline-offset-2">Add student</a>@endif
                @if($canAddBook)<a href="{{ route($prefix . 'book-management.add') }}" class="font-semibold text-[#102B70] underline underline-offset-2">Add item</a>@endif
            </div>
        @endif
    </div>
</div>
