<div class="h-[calc(100dvh-64px)] md:h-dvh bg-[#F8FAFC] flex flex-col">
    <div class="mx-auto w-full max-w-[1600px] p-4 lg:p-6 flex-1 flex flex-col min-h-0 gap-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#17357A]">Book Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage and maintain the library's catalog records and physical copies.</p>
            </div>
            
            <!-- Add Book Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="h-10 px-4 rounded-lg bg-[#17357A] hover:bg-[#122D68] text-white text-sm font-semibold inline-flex items-center gap-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Book
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{'rotate-180': open}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div x-show="open" x-transition.opacity.duration.200ms class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-200 z-20 py-1" style="display: none;">
                    <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#17357A] transition-colors">Add Book</a>
                    <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#17357A] transition-colors">Quick Add</a>
                    <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#17357A] transition-colors">Batch Add</a>
                    <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#17357A] transition-colors">Import MARC</a>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 shrink-0">
            <!-- Total Titles -->
            <div class="min-h-[100px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Total Titles</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($metrics['totalTitles']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                </div>
            </div>
            
            <!-- Total Copies -->
            <div class="min-h-[100px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Total Copies</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($metrics['totalCopies']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-amber-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                </div>
            </div>

            <!-- Available Copies -->
            <div class="min-h-[100px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Available</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($metrics['availableCopies']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
            </div>

            <!-- On Loan -->
            <div class="min-h-[100px] rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">On Loan</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($metrics['loanedCopies']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-slate-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
        </div>

        <!-- Main Book Manager Workspace -->
        <section class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            
            <!-- Toolbar -->
            <header class="shrink-0 p-4 border-b border-slate-200 bg-white space-y-4 sm:space-y-0 sm:flex sm:items-center sm:justify-between gap-4">
                
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title, author, ISBN..." class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 outline-none transition-shadow text-slate-900 placeholder:text-slate-400">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <select wire:model.live="category" class="h-10 min-w-[150px] rounded-lg border border-slate-200 bg-white px-3 py-0 text-sm text-slate-700 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 outline-none transition-shadow">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="status" class="h-10 min-w-[150px] rounded-lg border border-slate-200 bg-white px-3 py-0 text-sm text-slate-700 focus:border-[#17357A] focus:ring-[3px] focus:ring-[#17357A]/10 outline-none transition-shadow">
                        <option value="">All Statuses</option>
                        <option value="Available">Available</option>
                        <option value="Borrowed">On Loan</option>
                        <option value="Lost">Lost</option>
                        <option value="Damaged">Damaged</option>
                    </select>

                    @if($search || $category || $publisher || $status)
                        <button wire:click="resetFilters" class="h-10 px-3 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors text-sm font-medium inline-flex items-center gap-1.5 focus:outline-none">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                            Reset
                        </button>
                    @endif
                </div>
            </header>

            <!-- Scrollable Table -->
            <div class="min-h-0 flex-1 overflow-auto bg-white relative" id="bookTableContainer" x-init="$wire.on('updated', () => { document.getElementById('bookTableContainer').scrollTop = 0; })">
                
                <!-- Loading Overlay (Scoped to table) -->
                <div wire:loading.delay.longer class="absolute inset-0 bg-white/70 backdrop-blur-[1px] z-20 flex items-center justify-center">
                    <div class="h-8 w-8 rounded-full border-[3px] border-[#17357A]/20 border-t-[#17357A] animate-spin"></div>
                </div>

                <table class="min-w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10 bg-[#F8FAFC] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-[0.03em] w-[31%]">Book</th>
                            <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-[0.03em] w-[15%]">Author</th>
                            <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-[0.03em] w-[14%]">Category</th>
                            <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-[0.03em] w-[10%]">Copies</th>
                            <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-[0.03em] w-[17%]">Availability</th>
                            <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-[0.03em] w-[13%] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($books as $book)
                            @php
                                $totalCopiesForTitle = $book->bookDetails->sum(function($detail) {
                                    return $detail->books->count();
                                });
                                $availableCopiesForTitle = $book->bookDetails->sum(function($detail) {
                                    return $detail->books->where('status', 'Available')->count();
                                });
                                
                                $mainIsbn = $book->bookDetails->first()?->isbn ?? 'N/A';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors group">
                                <td class="py-3 px-5">
                                    <div class="flex items-start gap-3">
                                        <!-- Cover Thumbnail Fallback -->
                                        <div class="h-12 w-9 rounded-md bg-slate-100 border border-slate-200 shrink-0 flex items-center justify-center text-slate-400">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-semibold text-[#17357A] truncate">{{ $book->book_title }}</span>
                                            <span class="text-xs text-slate-500 truncate mt-0.5">ISBN: {{ $mainIsbn }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-5 text-sm text-slate-700">
                                    <div class="truncate max-w-[200px]">
                                        {{ $book->authors->pluck('author_name')->join(', ') ?: 'Unknown' }}
                                    </div>
                                </td>
                                <td class="py-3 px-5">
                                    @if($book->categories->isNotEmpty())
                                        <div class="inline-flex items-center rounded-md border border-[#DBEAFE] bg-[#EFF6FF] px-2.5 py-1 text-xs font-medium text-[#1D4ED8]">
                                            {{ $book->categories->first()->name }}
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-5 text-sm text-slate-600">
                                    {{ $totalCopiesForTitle }} total
                                </td>
                                <td class="py-3 px-5">
                                    @if($totalCopiesForTitle == 0)
                                        <div class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            No Copies
                                        </div>
                                    @elseif($availableCopiesForTitle > 0)
                                        <div class="inline-flex items-center gap-1.5 rounded-md border border-[#A7F3D0] bg-[#ECFDF5] px-2.5 py-1 text-xs font-medium text-[#047857]">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#10B981]"></span>
                                            {{ $availableCopiesForTitle }} Available
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 rounded-md border border-[#FDE68A] bg-[#FFFBEB] px-2.5 py-1 text-xs font-medium text-[#B45309]">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#F59E0B]"></span>
                                            None Available
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button class="p-1.5 text-slate-400 hover:text-[#17357A] hover:bg-slate-100 rounded-md transition-colors" title="View Details">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </button>
                                        <button class="p-1.5 text-slate-400 hover:text-[#17357A] hover:bg-slate-100 rounded-md transition-colors" title="Edit Book">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        
                                        <!-- More Actions Dropdown -->
                                        <div x-data="{ open: false }" class="relative" @click.away="open = false">
                                            <button @click="open = !open" class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors" title="More Actions">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                                            </button>
                                            <div x-show="open" style="display: none;" class="absolute right-0 mt-1 w-36 rounded-lg bg-white shadow-lg border border-slate-200 z-30 py-1" x-transition.opacity.duration.200ms>
                                                <button class="w-full text-left px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-[#17357A] transition-colors">Manage Copies</button>
                                                <div class="h-px bg-slate-200 my-1"></div>
                                                <button wire:click="confirmDelete({{ $book->id }})" class="w-full text-left px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">Delete Book</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-slate-300 mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <h3 class="text-sm font-medium text-slate-900">No books found</h3>
                                        <p class="mt-1 text-sm text-slate-500 max-w-sm text-center">
                                            @if($search || $category || $publisher || $status)
                                                No catalog records match your current search or filters.
                                            @else
                                                Your library catalog is empty. Add a book to get started.
                                            @endif
                                        </p>
                                        @if($search || $category || $publisher || $status)
                                            <button wire:click="resetFilters" class="mt-4 px-4 py-2 rounded-lg bg-white border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Clear Filters</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Fixed Pagination -->
            <footer class="shrink-0 border-t border-slate-200 bg-white px-5 py-3 min-h-[60px] flex items-center">
                <div class="w-full">
                    {{ $books->links('pagination::tailwind') }}
                </div>
            </footer>
        </section>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ open: @entangle('confirmingDeletion') }" x-show="open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition.opacity.duration.300ms class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <div x-show="open" x-transition.opacity.duration.300ms class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Delete Book Record?</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500">
                                    You are about to delete this bibliographic record. This action may also affect associated copy records depending on the system's catalog rules.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
                    <button wire:click="deleteBook" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Delete Book
                    </button>
                    <button wire:click="$set('confirmingDeletion', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#17357A] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
