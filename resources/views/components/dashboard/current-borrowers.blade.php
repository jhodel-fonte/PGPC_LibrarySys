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
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <th class="py-3 px-6 sm:px-7 text-[14px] font-bold text-slate-800 uppercase tracking-wider w-[22%]">Member</th>
                    <th class="py-3 px-6 sm:px-7 text-[14px] font-bold text-slate-800 uppercase tracking-wider w-[47%]">Book</th>
                    <th class="py-3 px-6 sm:px-7 text-[14px] font-bold text-slate-800 uppercase tracking-wider w-[18%]">Borrowed</th>
                    <th class="py-3 px-6 sm:px-7 text-[14px] font-bold text-slate-800 uppercase tracking-wider w-[13%] text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-xs">
                @forelse ($displayBorrowers as $borrowing)
                    <tr class="hover:bg-slate-50/70 transition-colors h-[58px]">
                        <td class="px-6 sm:px-7 text-[15px] font-bold text-[#102B70] whitespace-nowrap">
                            {{ $borrowing->student ? ($borrowing->student->first_name . ' ' . $borrowing->student->last_name) : 'Unknown Member' }}
                        </td>
                        <td class="px-6 sm:px-7 text-[15px] font-medium text-slate-700">
                            {{ Str::limit($borrowing->book?->bookDetail?->bookData?->book_title ?? 'Unknown Book', 50) }}
                        </td>
                        <td class="px-6 sm:px-7 text-[15px] font-semibold text-black whitespace-nowrap">
                            {{ $borrowing->issued_date ? $borrowing->issued_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 sm:px-7 text-right whitespace-nowrap">
                            @if ($borrowing->due_date && $borrowing->due_date < now())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    Overdue
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    
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
