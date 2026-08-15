@props(['borrowers' => collect()])

<div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] flex flex-col h-full overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 sm:px-7 sm:py-6 flex justify-between items-start bg-white">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Current Borrowers</h2>
            <p class="text-slate-500 text-xs mt-1">Students with active book loans</p>
        </div>
        <a href="" class="px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors rounded-lg text-xs font-semibold">View all</a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-y border-[#E2E8F0] bg-[#F8FAFC]">
                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[22%]">Member</th>
                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[47%]">Book</th>
                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[18%]">Borrowed</th>
                    <th class="py-3 px-6 sm:px-7 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[13%] text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0] bg-white">
                @forelse ($borrowers as $borrowing)
                    <tr class="hover:bg-slate-50 transition-colors h-[58px]">
                        <td class="px-6 sm:px-7 text-[14px] font-semibold text-slate-900 whitespace-nowrap">
                            {{ $borrowing->student->first_name }} {{ $borrowing->student->last_name }}
                        </td>
                        <td class="px-6 sm:px-7 text-[14px] font-normal text-slate-600">
                            {{ Str::limit($borrowing->book->bookDetail->bookData->book_title ?? 'Unknown Book', 50) }}
                        </td>
                        <td class="px-6 sm:px-7 text-xs text-slate-500 whitespace-nowrap">
                            {{ $borrowing->issued_date ? $borrowing->issued_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 sm:px-7 text-right whitespace-nowrap">
                            @if ($borrowing->due_date && $borrowing->due_date < now())
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 border border-red-200">Overdue</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">Borrowed</span>
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
