@props(['borrowers' => collect()])
@php
    $role = request()->is('librarian*') ? 'librarian' : 'admin';
    $displayBorrowers = collect($borrowers)->take(7);
@endphp

<div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 sm:px-7 sm:py-6 border-b border-[#E2E8F0] bg-white flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Borrowed Students</h2>
            <p class="text-slate-500 text-xs mt-1">Active book borrowings and student records</p>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="admin-mobile-table w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <th scope="col" class="py-3 px-6 sm:px-7 text-xs font-bold text-slate-700 uppercase tracking-wider w-[22%]">Member</th>
                    <th scope="col" class="py-3 px-6 sm:px-7 text-xs font-bold text-slate-700 uppercase tracking-wider w-[47%]">Book</th>
                    <th scope="col" class="py-3 px-6 sm:px-7 text-xs font-bold text-slate-700 uppercase tracking-wider w-[18%]">Borrowed</th>
                    <th scope="col" class="py-3 px-6 sm:px-7 text-xs font-bold text-slate-700 uppercase tracking-wider w-[13%] text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-xs">
                @forelse ($displayBorrowers as $borrowing)
                    <tr class="hover:bg-slate-50/70 transition-colors h-[58px]">
                        <td data-label="Member" class="px-6 sm:px-7 text-sm font-bold text-[#102B70] whitespace-nowrap">
                            {{ $borrowing->student ? ($borrowing->student->first_name . ' ' . $borrowing->student->last_name) : 'Unknown Member' }}
                        </td>
                        <td data-label="Book" class="px-6 sm:px-7 text-sm font-medium text-slate-700">
                            {{ Str::limit($borrowing->book?->bookDetail?->bookData?->book_title ?? 'Unknown Book', 50) }}
                        </td>
                        <td data-label="Borrowed" class="px-6 sm:px-7 text-sm font-semibold text-slate-800 whitespace-nowrap">
                            {{ $borrowing->issued_date ? $borrowing->issued_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td data-label="Status" class="px-6 sm:px-7 text-right whitespace-nowrap">
                            @if ($borrowing->due_date && $borrowing->due_date < now())
                                <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-0.5 text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    Overdue
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    
                                    Borrowed
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500 text-sm">
                            No active borrowing records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
