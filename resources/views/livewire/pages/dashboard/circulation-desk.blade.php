@props(['activepage' => 'Borrowing'])
<div class="px-6 py-8 md:px-8 max-w-7xl mx-auto h-full">
    
    <!-- Page Header -->
    <div class="mb-10">
        <h1 class="text-[27px] font-bold text-slate-900 leading-tight">Circulation Desk</h1>
        <!-- Yellow accent line -->
        {{-- <div class="h-1.5 w-16 bg-[#FCC719] rounded-full mt-2 mb-4"></div> --}}
        <p class="text-sm font-bold text-slate-500">Manage book borrowing and returns</p>
    </div>

    <!-- Action Cards Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: Borrow / Check-out -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-8 hover:border-[#CBD5E1] hover:shadow-md transition-all group min-h-[260px] flex flex-col cursor-pointer focus-within:ring-2 focus-within:ring-[#102B70]" onclick="window.location.href='#'">
            
            <!-- Icon Container -->
            <div class="w-14 h-14 bg-[#EFF6FF] text-[#102B70] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-105 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1">
                <h2 class="text-[20px] font-bold text-slate-900 mb-2 group-hover:text-[#102B70] transition-colors">Borrow / Check-out</h2>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-sm">
                    Process new book loans to members and manage due dates.
                </p>
            </div>

            <!-- Action Link -->
            <div class="mt-8 flex items-center font-bold text-sm text-[#102B70]">
                Start Borrowing
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Return / Check-in -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] p-8 hover:border-[#CBD5E1] hover:shadow-md transition-all group min-h-[260px] flex flex-col cursor-pointer focus-within:ring-2 focus-within:ring-[#102B70]" onclick="window.location.href='#'">
            
            <!-- Icon Container -->
            <div class="w-14 h-14 bg-[#EFF6FF] text-[#102B70] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-105 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1">
                <h2 class="text-[20px] font-bold text-slate-900 mb-2 group-hover:text-[#102B70] transition-colors">Return / Check-in</h2>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-sm">
                    Process returned books and update their availability.
                </p>
            </div>

            <!-- Action Link -->
            <div class="mt-8 flex items-center font-bold text-sm text-[#102B70]">
                Start Returning
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>
        </div>

    </div>
</div>