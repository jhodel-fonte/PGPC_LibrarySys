<div class="bg-[#F8FAFC] flex flex-col min-h-0 flex-1 overflow-hidden font-sans">
    <div class="mx-auto w-full max-w-[1600px] p-3 sm:p-4 lg:p-6 relative flex flex-col gap-3 sm:gap-4 min-h-0 flex-1">

        <!-- Background Watermark -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden flex items-center justify-center opacity-[0.018] z-0">
            <img src="{{ asset('images/logo.webp') }}" class="w-2/3 max-w-[800px] object-contain" alt="">
        </div>

        <!-- 1. Page Header -->
        <div class="relative z-10 flex flex-col gap-3 sm:gap-4 sm:flex-row sm:items-start sm:justify-between lg:shrink-0">
            <div class="flex min-w-0 items-start gap-3.5">
                <div class="min-w-0 pl-0 sm:pl-2">
                    <h1 class="text-xl font-bold tracking-[-0.02em] text-[#102B70] sm:text-2xl">Active Borrowers</h1>
                    <p class="mt-0.5 max-w-2xl text-xs sm:text-sm font-medium text-slate-600">
                        View and manage students with books currently on loan, check due dates, and review circulation history.
                    </p>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 sm:shrink-0">
                <a href="{{ route($routePrefix . 'circulation-desk.index') }}" wire:navigate
                    class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-[#CBD5E1] bg-white px-4 text-sm font-semibold text-[#102B70] shadow-sm transition-colors hover:border-[#102B70] hover:bg-slate-50 active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                    <span>Return to Circulation Desk</span>
                </a>

                <a href="{{ route($routePrefix . 'circulation-desk.borrow') }}" wire:navigate
                    class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-[#102B70] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0B225E] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    <span>Check-Out Book</span>
                </a>
            </div>
        </div>

        <!-- 2. Data Table Component (Identical to Users & Book Manager Tables) -->
        <x-widget.data-table
            :headers="$this->headers"
            :sort="$sort"
            :tabs="[
                'active' => 'Active Loans (' . $activeCount . ')',
                'overdue' => 'Overdue (' . $overdueCount . ')',
                'returned' => 'Returned (' . $returnedCount . ')',
                'all' => 'All Records (' . $totalHistoryCount . ')'
            ]"
            :activeTab="$activeTab"
            tabMethod="setTab"
            :showFilter="true"
            :showColumns="true"
            :activeFilterCount="$this->activeFilterCount"
            searchPlaceholder="Search by student name, ID number, book title, or accession..."
            :paginator="$borrowings"
            minWidth="1050px"
        >
            <!-- Filter Dropdown Slot -->
            <x-slot:filterDropdown>
                <div class="font-sans select-none">
                    <!-- Filter Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#102B70]">Filter Borrowers</h3>
                        </div>
                        @if($this->activeFilterCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EFF6FF] text-[#102B70] border border-[#BFDBFE]">
                                {{ $this->activeFilterCount }} Active
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3.5 pt-3.5 pb-4">
                        <!-- Degree Program Filter -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#334155]">Degree Program</label>
                            <select
                                wire:model="tempFilterProgram"
                                class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                            >
                                <option value="">All Programs</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog }}">{{ $prog }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Level Filter -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-[#334155]">Year Level</label>
                            <select
                                wire:model="tempFilterYearLevel"
                                class="w-full h-10 px-3.5 rounded-xl border border-[#CBD5E1] bg-white text-xs font-semibold text-[#0F172A] outline-none focus:border-[#102B70] focus:ring-4 focus:ring-[#EFF6FF] transition-all cursor-pointer font-sans"
                            >
                                <option value="">All Year Levels</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="clearFilters"
                            @click="filterOpen = false"
                            class="flex-1 h-9 px-3 rounded-xl border border-[#CBD5E1] hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors cursor-pointer"
                        >
                            Reset
                        </button>
                        <button
                            type="button"
                            wire:click="applyFilters"
                            @click="filterOpen = false"
                            class="flex-1 h-9 px-4 rounded-xl bg-[#102B70] hover:bg-[#0B225E] active:scale-[0.98] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </div>
            </x-slot:filterDropdown>

            <!-- Table Rows -->
            @forelse($borrowings as $item)
                @php
                    $student = $item->student;
                    $studentAccount = $student?->account;
                    $fullName = $student ? "{$student->first_name} {$student->last_name}" : 'Student Member';
                    $idNumber = $student?->school_id_number ?? '—';
                    $programInfo = $student ? trim(($student->program ?? '') . ($student->year_level ? " - Year {$student->year_level}" : '')) : '';
                    $initials = $student ? strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) : 'ST';

                    $bBook = $item->book;
                    $bDetail = $bBook?->bookDetail;
                    $bData = $bDetail?->bookData;
                    $bTitle = $bData?->book_title ?? 'Untitled Book';
                    $bAccession = $bBook?->accession_number ?? ('ACC-' . str_pad($item->book_id, 4, '0', STR_PAD_LEFT));

                    $isReturned = !empty($item->return_date);
                    $isOverdue = !$isReturned && $item->due_date && $item->due_date->isPast();

                    $issuedByStaff = $item->issuedBy
                        ? trim($item->issuedBy->first_name . ' ' . $item->issuedBy->last_name)
                        : null;
                @endphp

                <tr class="hover:bg-slate-50/70 transition-colors h-[72px] group">
                    <!-- 1. STUDENT / BORROWER INFO -->
                    <td x-show="cols['student'] !== false" class="px-6 py-4 align-middle">
                        <div class="flex items-center gap-3.5 group/user">
                            @if($student?->image_url)
                                <img src="{{ $student->image_url }}" alt="{{ $fullName }}" class="w-10 h-10 rounded-full object-cover border border-[#CBD5E1] shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-[#102B70] font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $initials }}
                                </div>
                            @endif

                            <div class="flex flex-col min-w-0">
                                @if($studentAccount)
                                    <a href="{{ route($routePrefix . 'user-details', $studentAccount->encrypted_id) }}" wire:navigate class="text-[14px] font-bold text-[#102B70] hover:text-blue-700 hover:underline transition-colors truncate">
                                        {{ $fullName }}
                                    </a>
                                @else
                                    <span class="text-[14px] font-bold text-[#102B70] truncate">
                                        {{ $fullName }}
                                    </span>
                                @endif
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium truncate mt-0.5">
                                    <span class="font-mono">{{ $idNumber }}</span>
                                    @if($programInfo)
                                        <span>•</span>
                                        <span class="truncate">{{ $programInfo }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- 2. BOOK DETAILS & ACCESSION -->
                    <td x-show="cols['book'] !== false" class="px-6 py-4 align-middle">
                            <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold text-slate-900 leading-tight truncate max-w-[340px]" title="{{ $bTitle }}">
                                    {{ $bTitle }}
                                </span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-[11px] font-semibold text-[#102B70] px-2 py-0.5 rounded-md">
                                        {{ $bAccession }}
                                    </span>
                                </div>
                            </div>
                    </td>

                    <!-- 3. BORROWED DATE -->
                    <td x-show="cols['borrowed_date'] !== false" class="px-6 py-4 align-middle whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-800">
                                {{ $item->issued_date ? $item->issued_date->format('M d, Y') : '—' }}
                            </span>
                            @if($issuedByStaff)
                                <span class="text-xs text-slate-400 font-medium mt-0.5">
                                    By {{ $issuedByStaff }}
                                </span>
                            @endif
                        </div>
                    </td>

                    <!-- 4. DUE DATE -->
                    <td x-show="cols['due_date'] !== false" class="px-6 py-4 align-middle whitespace-nowrap">
                        @if($isReturned)
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-500">
                                    {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                </span>
                                <span class="text-xs text-emerald-600 font-medium mt-0.5">
                                    Returned {{ $item->return_date ? $item->return_date->format('M d') : '' }}
                                </span>
                            </div>
                        @elseif($isOverdue)
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-red-600">
                                    {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                </span>
                                <span class="text-xs text-red-500 font-semibold mt-0.5">
                                    {{ $item->due_date ? $item->due_date->diffForHumans() : 'Overdue' }}
                                </span>
                            </div>
                        @else
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-700">
                                    {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium mt-0.5">
                                    Due {{ $item->due_date ? $item->due_date->diffForHumans(['parts' => 1]) : '' }}
                                </span>
                            </div>
                        @endif
                    </td>

                    <!-- 5. LOAN STATUS BADGE -->
                    <td x-show="cols['status'] !== false" class="px-6 py-4 align-middle">
                        @if($isReturned)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Returned
                            </span>
                        @elseif($isOverdue)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                Overdue
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                On Loan
                            </span>
                        @endif
                    </td>

                    <!-- 6. ACTIONS -->
                    <td class="px-6 py-4 align-middle text-right pr-6">
                        <div class="flex items-center justify-end">
                            <x-widget.table-action-dot>
                                @if(!$isReturned)
                                    <a href="{{ route($routePrefix . 'circulation-desk.return') }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-[#102B70] hover:bg-slate-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        Process Return
                                    </a>
                                @endif

                                @if($studentAccount)
                                    <a href="{{ route($routePrefix . 'user-details', $studentAccount->encrypted_id) }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        View Patron Profile
                                    </a>
                                @endif

                                @if($bData)
                                    <a href="{{ route('opac.book.detail', $bData->id) }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        View in OPAC
                                    </a>
                                @endif
                            </x-widget.table-action-dot>
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-widget.data-table>

    </div>
</div>
