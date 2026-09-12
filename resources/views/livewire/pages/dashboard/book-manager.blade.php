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
                <a href="{{ route('admin.book-management.add') }}" wire:navigate class="flex items-center gap-2 h-11 px-5 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-white text-xs uppercase tracking-wider font-bold transition-colors shadow-sm focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Book
                </a>
                <button type="button" onclick="alert('Book Import feature coming soon!')" class="flex items-center gap-2 h-11 px-5 rounded-2xl border border-[#102B70] bg-white text-[#102B70] hover:bg-slate-50 text-xs uppercase tracking-wider font-bold transition-colors shadow-sm focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    Import Book
                </button>
            </div>
        </div>

        <!-- 2. Statistics Cards -->
        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-5 lg:shrink-0">
            <!-- Total Titles -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Book Titles</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#0F172A]">{{ number_format($stats['total_titles']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-[#EFF6FF] text-[#102B70] shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                </div>
            </div>

            <!-- Total Copies -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Total Copies</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#0F172A]">{{ number_format($stats['total_copies']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-[#DBEAFE] text-[#1D4ED8] shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>

            <!-- Available Copies -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Available</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-emerald-700">{{ number_format($stats['available']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>

            <!-- Borrowed Copies -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Borrowed</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-blue-700">{{ number_format($stats['borrowed']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>

            <!-- Damaged/Lost Copies -->
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-3.5 shadow-[0_1px_3px_rgba(15,23,42,0.03)] flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 truncate">Damaged / Lost</p>
                    <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-red-700">{{ number_format($stats['damaged_lost']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-red-50 text-red-600 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="9" x2="15" y1="9" y2="15"/><line x1="15" x2="9" y1="9" y2="15"/></svg>
                </div>
            </div>
        </div>

        <!-- 3. Table Card -->
        <x-data-table
            :headers="$this->headers"
            :sort="$sort"
            :tabs="['All Books', 'In Stock', 'Borrowed', 'Damaged/Lost']"
            :activeTab="$activeTab"
            searchPlaceholder="Search title, author, ISBN, call number, accession..."
            :paginator="$bookDetails"
            minWidth="1050px"
        >
            @forelse($bookDetails as $detail)
                @php
                    $data = $detail->bookData;
                    $authorName = 'Unknown Author';
                    if ($data && $data->authors->isNotEmpty()) {
                        $authorName = $data->authors->map(function($a) {
                            return trim($a->first_name . ' ' . $a->last_name);
                        })->implode(', ');
                    }
                    $categories = $data ? $data->categories : collect();
                @endphp
                <tr class="hover:bg-slate-50/70 transition-colors h-[76px] group">
                    <!-- Book Info -->
                    <td class="px-6 py-4 align-middle max-w-[340px]">
                        <div class="flex items-center gap-3.5">
                            <!-- Miniature Book Thumbnail (Vertical Book Ratio) -->
                            @if($detail->cover_image)
                                <img src="{{ asset('storage/' . $detail->cover_image) }}" class="w-9 h-12 rounded-lg object-cover shrink-0 border border-slate-200 shadow-2xs" alt="Cover">
                            @else
                                @php
                                    $initials = collect(explode(' ', $data ? $data->book_title : 'BOOK'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                                @endphp
                                <div class="w-9 h-12 rounded-lg bg-[#E8EEFC] text-[#102B70] flex items-center justify-center shrink-0 font-bold text-xs uppercase select-none border border-[#DBEAFE] shadow-2xs">
                                    {{ strtoupper($initials) }}
                                </div>
                            @endif

                            <div class="flex flex-col min-w-0">
                                <span class="text-sm font-bold text-[#102B70] group-hover:text-blue-700 transition-colors truncate" title="{{ $data ? $data->book_title : 'Unknown' }}">
                                    {{ $data ? $data->book_title : 'Unknown Title' }}
                                </span>
                                <span class="text-xs text-slate-500 truncate mt-0.5 font-medium">{{ $authorName }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- ISBN / Call Number -->
                    <td class="px-6 py-4 align-middle">
                        <div class="flex flex-col">
                            <span class="text-sm text-slate-700 font-semibold font-mono">{{ $detail->isbn ?: '—' }}</span>
                            @if($detail->call_number)
                                <span class="text-xs text-slate-500 font-medium mt-0.5">Call: {{ $detail->call_number }}</span>
                            @endif
                        </div>
                    </td>

                    <!-- Category -->
                    <td class="px-6 py-4 align-middle">
                        @if($categories->isNotEmpty())
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @foreach($categories->take(2) as $category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                                @if($categories->count() > 2)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-bold text-slate-500">
                                        +{{ $categories->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-slate-400 font-medium">Uncategorized</span>
                        @endif
                    </td>

                    <!-- Total Copies -->
                    <td class="px-6 py-4 align-middle">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 bg-slate-50 text-slate-700">
                            {{ $detail->total_copies }} {{ Str::plural('Copy', $detail->total_copies) }}
                        </span>
                    </td>

                    <!-- Status / Availability -->
                    <td class="px-6 py-4 align-middle">
                        @if($detail->available_copies > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]">
                                {{ $detail->available_copies }} Available
                            </span>
                        @elseif($detail->total_copies > 0 && $detail->borrowed_copies > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border border-[#FED7AA] bg-[#FFEDD5] text-[#C2410C]">
                                All Borrowed
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 bg-slate-100 text-slate-500">
                                No Copies
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 align-middle text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button
                                wire:click="openCopiesModal({{ $detail->id }})"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#102B70] text-xs uppercase tracking-wider font-bold transition-colors shadow-2xs focus:outline-none"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                                Copies ({{ $detail->total_copies }})
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm md:text-base font-semibold">
                        No book records match the current search or filters.
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </div>

    <!-- 4. MANAGE COPIES MODAL (View & manage all copies for selected title) -->
    @if($showCopiesModal && $selectedBookDetail)
        @php
            $modalData = $selectedBookDetail->bookData;
            $modalAuthor = 'Unknown Author';
            if ($modalData && $modalData->authors->isNotEmpty()) {
                $modalAuthor = $modalData->authors->map(fn($a) => trim($a->first_name . ' ' . $a->last_name))->implode(', ');
            }
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-[#E2E8F0] shadow-2xl max-w-3xl w-full overflow-hidden animate-fade-in flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-[#E2E8F0] bg-slate-50 flex items-start justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3.5 min-w-0">
                        @if($selectedBookDetail->cover_image)
                            <img src="{{ asset('storage/' . $selectedBookDetail->cover_image) }}" class="w-10 h-14 rounded-lg object-cover shrink-0 border border-slate-200 shadow-xs" alt="Cover">
                        @else
                            <div class="w-10 h-14 rounded-lg bg-[#E8EEFC] text-[#102B70] flex items-center justify-center shrink-0 font-bold text-xs uppercase border border-[#DBEAFE]">
                                BOOK
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-[#102B70] truncate">{{ $modalData ? $modalData->book_title : 'Book Copies' }}</h3>
                            <p class="text-xs text-slate-500 font-medium truncate mt-0.5">{{ $modalAuthor }}</p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">ISBN: {{ $selectedBookDetail->isbn ?: 'N/A' }} &bull; Call: {{ $selectedBookDetail->call_number ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeCopiesModal" class="text-slate-400 hover:text-[#0F172A] text-2xl font-bold select-none shrink-0">&times;</button>
                </div>

                <!-- Modal Body: Table of Physical Copies -->
                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">Physical Copies ({{ $selectedBookDetail->books->count() }})</h4>
                    </div>

                    <div class="border border-[#E2E8F0] rounded-2xl overflow-hidden bg-white shadow-2xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-slate-500 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3">Accession No.</th>
                                    <th class="px-4 py-3">QR / Code</th>
                                    <th class="px-4 py-3">Location</th>
                                    <th class="px-4 py-3">Condition</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($selectedBookDetail->books as $copy)
                                    @php
                                        $cond = strtolower($copy->condition ? $copy->condition->status : 'good');
                                        $condColor = match($cond) {
                                            'new' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                            'good' => 'border-[#BFDBFE] bg-[#DBEAFE] text-[#1D4ED8]',
                                            'fair' => 'border-[#FDE68A] bg-[#FEF3C7] text-[#B45309]',
                                            'damaged' => 'border-[#FED7AA] bg-[#FFEDD5] text-[#C2410C]',
                                            'lost' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                            default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                                        };

                                        $stat = strtolower($copy->status);
                                        $statColor = match($stat) {
                                            'available' => 'border-[#BBF7D0] bg-[#DCFCE7] text-[#15803D]',
                                            'borrowed' => 'border-[#FECACA] bg-[#FEE2E2] text-[#B91C1C]',
                                            default => 'border-[#E2E8F0] bg-[#F1F5F9] text-[#475569]'
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-slate-800 font-mono">{{ $copy->accession_number }}</td>
                                        <td class="px-4 py-3 text-slate-600 font-mono">{{ $copy->code ?: '—' }}</td>
                                        <td class="px-4 py-3 text-slate-600 font-medium">{{ $copy->location ?: 'Not Placed' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $condColor }}">
                                                {{ $copy->condition ? $copy->condition->status : 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $statColor }}">
                                                {{ ucfirst($copy->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    wire:click="editCopy({{ $copy->id }})"
                                                    type="button"
                                                    class="text-xs font-bold uppercase tracking-wider text-[#102B70] hover:text-blue-700 transition-colors"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    wire:click="deleteCopy({{ $copy->id }})"
                                                    @if($copy->status === 'borrowed') disabled @endif
                                                    type="button"
                                                    class="text-xs font-bold uppercase tracking-wider text-red-600 hover:text-red-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                                    onclick="confirm('Are you sure you want to delete copy {{ $copy->accession_number }}?') || event.stopImmediatePropagation()"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-medium">
                                            No physical copies registered for this book.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex justify-end shrink-0">
                    <button
                        type="button"
                        wire:click="closeCopiesModal"
                        class="px-5 h-10 border border-slate-200 hover:bg-white text-slate-700 text-xs uppercase tracking-wider font-bold rounded-xl transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- 5. EDIT BOOK COPY MODAL (Tailwind CSS Modal with Livewire Show trigger) -->
    @if($showEditModal)
        <div class="fixed inset-0 z-60 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
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
