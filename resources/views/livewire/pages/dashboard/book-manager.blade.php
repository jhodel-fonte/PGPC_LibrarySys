<div class="bg-[#F8FAFC] lg:h-full lg:flex lg:flex-col lg:min-h-0">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 relative flex flex-col gap-6 lg:h-full lg:min-h-0 lg:flex-1">

        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.012] z-0">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between lg:shrink-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#102B70]">Book Inventory Manager</h1>
                <p class="mt-1 text-sm text-slate-500">Track and manage physical book copies, locations, and copy conditions.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="alert('Add New Book feature coming soon!')" class="flex items-center gap-2 h-11 px-5 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs uppercase tracking-wider font-bold transition-colors shadow-sm focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Book
                </button>
                <button type="button" onclick="alert('Book Import feature coming soon!')" class="flex items-center gap-2 h-11 px-5 rounded-2xl border border-[#102B70] bg-white text-[#102B70] hover:bg-slate-50 text-xs uppercase tracking-wider font-bold transition-colors shadow-sm focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    Import Book
                </button>
            </div>
        </div>

        <!-- Success/Error Toast Message banners -->
        @if($successMessage)
            <div class="relative z-10 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-sm font-semibold text-emerald-800 animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $successMessage }}</span>
                </div>
                <button type="button" wire:click="clearMessages" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg select-none">&times;</button>
            </div>
        @endif

        @if($errorMessage)
            <div class="relative z-10 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between text-sm font-semibold text-red-800 animate-fade-in">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
                <button type="button" wire:click="clearMessages" class="text-red-500 hover:text-red-700 font-bold text-lg select-none">&times;</button>
            </div>
        @endif

        <!-- 2. Statistics Cards -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 lg:gap-5 lg:shrink-0">
            <!-- Total Titles -->
            <div class="min-h-[104px] rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Book Titles</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-[#0F172A]">{{ number_format($stats['total_titles']) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-[#EFF6FF] text-[#102B70]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                </div>
            </div>

            <!-- Total Copies -->
            <div class="min-h-[104px] rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Copies</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-[#0F172A]">{{ number_format($stats['total_copies']) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-[#DBEAFE] text-[#1D4ED8]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>

            <!-- Available Copies -->
            <div class="min-h-[104px] rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Available</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-emerald-700">{{ number_format($stats['available']) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>

            <!-- Borrowed Copies -->
            <div class="min-h-[104px] rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Borrowed</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-blue-700">{{ number_format($stats['borrowed']) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>

            <!-- Damaged/Lost Copies -->
            <div class="min-h-[104px] rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Damaged / Lost</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight text-red-700">{{ number_format($stats['damaged_lost']) }}</p>
                </div>
                <div class="h-11 w-11 rounded-full flex items-center justify-center bg-red-50 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="9" x2="15" y1="9" y2="15"/><line x1="15" x2="9" y1="9" y2="15"/></svg>
                </div>
            </div>
        </div>

        <!-- 3. Table Card -->
        <div class="relative z-10 rounded-2xl border border-[#E2E8F0] bg-white shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col lg:flex-1 lg:min-h-0">

            <!-- Toolbar -->
            <div class="border-b border-[#E2E8F0] bg-white px-4 py-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between lg:shrink-0">

                <!-- Filter Tabs -->
                <div class="flex items-center gap-1">
                    @foreach(['All Copies', 'Available', 'Borrowed', 'Damaged/Lost'] as $tab)
                        <button
                            wire:click="setTab('{{ $tab }}')"
                            class="px-3.5 h-9 rounded-lg text-sm transition-colors {{ $activeTab === $tab ? 'bg-[#102B70] text-white font-semibold' : 'text-slate-600 hover:text-[#102B70] hover:bg-slate-50' }}"
                        >
                            {{ $tab }}
                        </button>
                    @endforeach
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-[340px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search accession, code, title, ISBN..."
                        class="h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-10 pr-3 text-sm focus:border-[#102B70] focus:ring-2 focus:ring-[#EFF6FF] transition-shadow outline-none"
                    >
                </div>
            </div>

            <!-- Table Wrapper with Scroll -->
            <div class="overflow-x-auto overflow-y-hidden w-full relative flex-1 min-h-0 lg:overflow-y-auto">
                <!-- Livewire Loading Overlay -->
                <div wire:loading.delay wire:target="search, sortBy, setTab, previousPage, nextPage, gotoPage, deleteCopy" class="absolute inset-0 z-20 bg-white/50 backdrop-blur-[1px] flex items-start justify-center pt-16 rounded-b-2xl">
                    <div class="flex items-center gap-2 text-[#102B70] bg-white px-4 py-2.5 rounded-xl shadow-md border border-[#E2E8F0]">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-semibold">Loading Catalog...</span>
                    </div>
                </div>

                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead class="sticky top-0 z-10 bg-[#F8FAFC] shadow-[0_1px_0_0_#E2E8F0]">
                        <tr>
                            @foreach($this->headers as $header)
                                @if($header['sortable'])
                                    <th wire:click="sortBy('{{ $header['index'] }}')" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500 cursor-pointer hover:bg-slate-100 transition-colors group select-none">
                                        <div class="flex items-center gap-1.5">
                                            {{ $header['label'] }}
                                            @if($sort['column'] === $header['index'])
                                                <svg class="h-3.5 w-3.5 text-[#102B70] transition-transform {{ $sort['direction'] === 'desc' ? 'rotate-180' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                            @endif
                                        </div>
                                    </th>
                                @else
                                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.03em] text-slate-500">
                                        {{ $header['label'] }}
                                    </th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($books as $book)
                            @php
                                $detail = $book->bookDetail;
                                $data = $detail ? $detail->bookData : null;
                                $authorName = 'Unknown Author';
                                if ($data && $data->authors->isNotEmpty()) {
                                    $authorName = $data->authors->map(function($a) {
                                        return trim($a->first_name . ' ' . $a->last_name);
                                    })->implode(', ');
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors h-[68px] group">
                                <!-- Book Info -->
                                <td class="px-5 py-2.5 max-w-[340px]">
                                    <div class="flex items-center gap-3">
                                        <!-- Miniature Book Thumbnail -->
                                        @if($detail && $detail->cover_image)
                                            <img src="{{ asset('storage/' . $detail->cover_image) }}" class="h-10 w-10 rounded-lg object-cover shrink-0 border border-slate-100" alt="Cover">
                                        @else
                                            @php
                                                $initials = collect(explode(' ', $data ? $data->book_title : 'BOOK'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                                            @endphp
                                            <div class="h-10 w-10 rounded-lg bg-[#E8EEFC] text-[#102B70] flex items-center justify-center shrink-0 font-bold text-xs uppercase select-none">
                                                {{ strtoupper($initials) }}
                                            </div>
                                        @endif

                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-semibold text-[#102B70] group-hover:text-blue-700 transition-colors truncate" title="{{ $data ? $data->book_title : 'Unknown' }}">
                                                {{ $data ? $data->book_title : 'Unknown Title' }}
                                            </span>
                                            <span class="text-xs text-slate-500 truncate mt-0.5">{{ $authorName }}</span>
                                            @if($detail && $detail->isbn)
                                                <span class="text-[10px] text-[#94A3B8] font-semibold tracking-wide uppercase mt-0.5">ISBN: {{ $detail->isbn }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Accession Number -->
                                <td class="px-5 py-2.5">
                                    <span class="text-sm text-slate-700 font-medium">{{ $book->accession_number }}</span>
                                </td>

                                <!-- Unique Code -->
                                <td class="px-5 py-2.5">
                                    <span class="text-sm text-slate-500 font-mono">{{ $book->code ?: '—' }}</span>
                                </td>

                                <!-- Location -->
                                <!-- Location -->
                                <td class="px-5 py-2.5 text-sm text-slate-700 font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>{{ $book->location ?: 'Not Placed' }}</span>
                                    </div>
                                </td>

                                <!-- Condition -->
                                <td class="px-5 py-2.5">
                                    @php
                                        $cond = strtolower($book->condition ? $book->condition->status : 'good');
                                        $condColor = match($cond) {
                                            'new' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                            'good' => 'border-[#BFDBFE] bg-[#DBEAFE] text-[#1D4ED8]',
                                            'fair' => 'border-[#FDE68A] bg-[#FEF3C7] text-[#B45309]',
                                            'damaged' => 'border-[#FED7AA] bg-[#FFEDD5] text-[#C2410C]',
                                            'lost' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                            default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $condColor }}">
                                        {{ $book->condition ? $book->condition->status : 'Unknown' }}
                                    </span>
                                </td>

                                <!-- Catalog Status -->
                                <td class="px-5 py-2.5">
                                    @php
                                        $status = strtolower($book->status);
                                        $statusColor = match($status) {
                                            'available' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                            'borrowed' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                            default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $statusColor }}">
                                        {{ ucfirst($book->status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-2.5 text-right text-sm font-semibold">
                                    <div class="flex items-center justify-end gap-3.5">
                                        <button
                                            wire:click="editCopy({{ $book->id }})"
                                            type="button"
                                            class="text-[#102B70] hover:text-blue-700 transition-colors focus:outline-none"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            wire:click="deleteCopy({{ $book->id }})"
                                            @if($book->status === 'borrowed') disabled @endif
                                            type="button"
                                            class="text-red-600 hover:text-red-800 transition-colors focus:outline-none disabled:opacity-40 disabled:cursor-not-allowed"
                                            onclick="confirm('Are you sure you want to delete this book copy?') || event.stopImmediatePropagation()"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500 text-sm font-semibold">
                                    No book copies match the current search or filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            @if($books->hasPages())
                <div class="border-t border-[#E2E8F0] px-4 py-3 bg-[#F8FAFC] lg:shrink-0">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- EDIT BOOK COPY MODAL (Tailwind CSS Modal with Livewire Show trigger) -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xl max-w-md w-full overflow-hidden animate-fade-in">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#102B70]">Edit Copy Settings</h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5 uppercase tracking-wider">Accession: {{ $editAccessionNumber }}</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-[#0F172A] text-2xl font-bold select-none">&times;</button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="saveCopy" class="p-6 space-y-5">
                    <!-- Unique QR Code (Disabled display) -->
                    <div class="space-y-1.5">
                        <label class="text-[11.5px] font-bold text-[#334155] uppercase tracking-wider">Unique Copy QR Code</label>
                        <input type="text" value="{{ $editCode }}" disabled class="w-full h-11 px-4 rounded-xl border border-[#E2E8F0] bg-slate-50 text-slate-500 text-sm font-bold font-mono outline-none cursor-not-allowed">
                    </div>

                    <!-- Shelf Location Input -->
                    <div class="space-y-1.5">
                        <label for="editLocation" class="text-[11.5px] font-bold text-[#334155] uppercase tracking-wider">Shelf Location</label>
                        <input
                            wire:model="editLocation"
                            type="text"
                            id="editLocation"
                            placeholder="e.g. Shelf A-2, Section B"
                            class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm text-[#0F172A] font-semibold outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all"
                        >
                        @error('editLocation') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Condition Selection -->
                    <div class="space-y-1.5">
                        <label for="editConditionId" class="text-[11.5px] font-bold text-[#334155] uppercase tracking-wider">Physical Copy Condition</label>
                        <div class="relative">
                            <select
                                wire:model="editConditionId"
                                id="editConditionId"
                                class="w-full h-12 px-4 rounded-xl border border-[#E2E8F0] bg-white text-sm text-[#0F172A] font-semibold outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all appearance-none cursor-pointer"
                            >
                                <option value="">Select Condition</option>
                                @foreach($conditions as $condition)
                                    <option value="{{ $condition->id }}">{{ $condition->status }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('editConditionId') <span class="text-xs font-bold text-[#EF4444]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Action buttons -->
                    <div class="pt-3 border-t border-[#F1F5F9] flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeEditModal"
                            class="px-5 h-11 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs uppercase tracking-wider font-bold rounded-xl transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 h-11 bg-[#102B70] hover:bg-[#0B225E] text-white text-xs uppercase tracking-wider font-bold rounded-xl transition-colors flex items-center gap-1.5 shadow-sm disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="saveCopy">Save Changes</span>
                            <span wire:loading.flex wire:target="saveCopy" class="items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
