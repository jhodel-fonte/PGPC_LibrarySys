@props([
    'checkInRoute' => route(request()->segment(1) . '.circulation-desk.return'),
    'checkOutRoute' => route(request()->segment(1) . '.circulation-desk.borrow'),
])

<div class="relative w-full flex flex-col items-center justify-start pt-2 md:pt-4 px-4 md:px-8 pb-4 font-sans min-h-full bg-[#F8FAFC]">
    
    <!-- Header Area -->
    <div class="w-full max-w-6xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 mt-8 relative z-10">
        <div>
            <h1 class="text-4xl md:text-[2.5rem] font-bold text-[#102B70] tracking-tight mb-2 leading-tight">
                Circulation Desk
            </h1>
            <p class="text-[#64748B] text-lg font-medium">
                Process book loans and returns quickly and efficiently.
            </p>
        </div>
        
        <!-- Quick Scan Info Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#E2E8F0] p-4 flex items-center gap-4 min-w-[320px]">
            <div class="w-12 h-12 rounded-xl bg-[#EFF6FF] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7V5a2 2 0 0 1 2-2h2M18 5a2 2 0 0 1 2 2v2M4 17v2a2 2 0 0 0 2 2h2M18 19a2 2 0 0 0 2-2v-2M10 8v8M14 8v8M7 8v8M17 8v8"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-[#0F172A] font-bold text-[15px]">Quick Scan Ready</h3>
                <p class="text-[#64748B] text-[13px] mt-0.5">Scan member ID or barcode to get started</p>
            </div>
        </div>
    </div>

    <!-- Cards Container -->
    <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10 mb-4">
        
        <!-- BORROW CARD -->
        <a href="{{ $checkInRoute }}" class="group relative flex flex-col p-5 md:p-6 rounded-3xl bg-white border border-[#E2E8F0] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgba(16,43,112,0.08)] hover:border-[#CBD5E1] hover:bg-[#F8FAFC] transition-all duration-300 cursor-pointer focus:outline-none focus:ring-4 focus:ring-[#3B82F6]/20">

            <!-- Card Header -->
            <div class="flex justify-between items-start mb-4 relative min-h-[112px]">
                <div class="pr-4 relative z-10">
                    <h2 class="text-[2rem] font-bold text-[#102B70] tracking-tight mb-2">Check-Out / Borrow</h2>
                    <p class="text-[#64748B] text-[15px] leading-relaxed max-w-[220px]">
                        Borrow books to members and set due dates.
                    </p>
                </div>
                <!-- Illustration overlay -->
                <div class="absolute -top-6 -right-2 md:-top-3 md:-right-2 w-35 h-35 md:w-36 md:h-36 shrink-0 transform transition-transform duration-500 group-hover:scale-110 group-hover:-translate-y-2 group-hover:rotate-2 pointer-events-none z-20 drop-shadow-xl">
                    <img src="{{ asset('images/checkout-book.webp') }}" alt="Checkout Book" class="w-full h-full object-contain">
                </div>
            </div>

            <!-- Divider -->
            <div class="w-full h-[1px] bg-gradient-to-r from-[#E2E8F0] via-[#E2E8F0] to-transparent mb-4"></div>

            <!-- Features -->
            <div class="flex flex-col gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#EFF6FF] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-[#334155] text-[15px] font-medium">Assign books to a member</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#EFF6FF] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-[#334155] text-[15px] font-medium">Set loan period & due date</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#EFF6FF] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="text-[#334155] text-[15px] font-medium">Check member borrowing eligibility</span>
                </div>
            </div>

            <!-- Integrated Action -->
            <div class="mt-auto pt-2">
                <div class="w-full bg-[#EFF6FF] group-hover:bg-[#E0EFFF] border border-[#DBEAFE] group-hover:border-[#BFDBFE] rounded-xl p-4 flex items-center justify-between transition-colors duration-300">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V5a2 2 0 0 1 2-2h2M18 5a2 2 0 0 1 2 2v2M4 17v2a2 2 0 0 0 2 2h2M18 19a2 2 0 0 0 2-2v-2M10 8v8M14 8v8M7 8v8M17 8v8"></path></svg>
                        <span class="text-[#102B70] font-bold text-lg">Start Borrowing</span>
                    </div>
                    <svg class="w-5 h-5 text-[#2563EB] transform transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </div>
                <div class="flex items-center gap-2 mt-3 text-[#64748B] text-[13px] px-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Make sure the member and book are valid before issuing.</p>
                </div>
            </div>
        </a>

        <!-- RETURN CARD -->
        <a href="{{ $checkInRoute }}" class="group relative flex flex-col p-5 md:p-6 rounded-3xl bg-white border border-[#E2E8F0] shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.08)] hover:border-[#CBD5E1] hover:bg-[#F8FAF9] transition-all duration-300 cursor-pointer focus:outline-none focus:ring-4 focus:ring-[#10B981]/20">

            <!-- Card Header -->
            <div class="flex justify-between items-start mb-4 relative min-h-[112px]">
                <div class="pr-4 relative z-10">
                    <h2 class="text-[2rem] font-bold text-[#102B70] tracking-tight mb-2">Check-In / Return</h2>
                    <p class="text-[#64748B] text-[15px] leading-relaxed max-w-[220px]">
                        Receive returned books and update availability.
                    </p>
                </div>
                <div class="absolute -top-6 -right-2 md:-top-3 md:-right-2 w-32 h-32 md:w-36 md:h-36 shrink-0 transform transition-transform duration-500 group-hover:scale-110 group-hover:-translate-y-2 group-hover:-rotate-2 pointer-events-none z-20 drop-shadow-xl">
                    <img src="{{ asset('images/checkin-book.webp') }}" alt="Checkin Book" class="w-full h-full object-contain">
                </div>
            </div>

            <!-- Divider -->
            <div class="w-full h-[1px] bg-gradient-to-r from-[#E2E8F0] via-[#E2E8F0] to-transparent mb-4"></div>

            <!-- Features -->
            <div class="flex flex-col gap-3 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#ECFDF5] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-[#334155] text-[15px] font-medium">Scan returned book</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#ECFDF5] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <span class="text-[#334155] text-[15px] font-medium">Check for damage or missing items</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#ECFDF5] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-[#334155] text-[15px] font-medium">Confirm return & update availability</span>
                </div>
            </div>

            <!-- Integrated Action -->
            <div class="mt-auto pt-2">
                <div class="w-full bg-[#F0FDF4] group-hover:bg-[#DCFCE7] border border-[#DCFCE7] group-hover:border-[#BBF7D0] rounded-xl p-4 flex items-center justify-between transition-colors duration-300">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V5a2 2 0 0 1 2-2h2M18 5a2 2 0 0 1 2 2v2M4 17v2a2 2 0 0 0 2 2h2M18 19a2 2 0 0 0 2-2v-2M10 8v8M14 8v8M7 8v8M17 8v8"></path></svg>
                        <span class="text-[#065F46] font-bold text-lg">Start Returning</span>
                    </div>
                    <svg class="w-5 h-5 text-[#059669] transform transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </div>
                <div class="flex items-center gap-2 mt-3 text-[#64748B] text-[13px] px-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Inspect the book before confirming return.</p>
                </div>
            </div>
        </a>

    </div>

    <!-- Quick Guide Section -->
    <div class="w-full max-w-6xl bg-white rounded-3xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-[#E2E8F0] p-4 lg:p-6 flex flex-col lg:flex-row items-center justify-between gap-4 relative z-10">
        
        <!-- Intro -->
        <div class="flex items-center gap-4 flex-1 w-full justify-start lg:justify-center">
            <div class="w-12 h-12 rounded-full bg-[#EFF6FF] flex items-center justify-center shrink-0 border border-[#DBEAFE]">
                <svg class="w-6 h-6 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-[#0F172A] text-[15px]">Quick Guide</h3>
                <p class="text-[#64748B] text-[13px] leading-relaxed mt-0.5">Follow these simple steps<br class="hidden sm:block"> for smooth transactions.</p>
            </div>
        </div>

        <!-- Chevron -->
        <div class="hidden lg:block text-[#CBD5E1] shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <!-- Step 1 -->
        <div class="flex items-center gap-4 flex-1 w-full justify-start lg:justify-center">
            <div class="w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-[13px] shrink-0 shadow-sm">1</div>
            <div>
                <h3 class="font-bold text-[#0F172A] text-[14px]">Scan Member ID</h3>
                <p class="text-[#64748B] text-[13px] leading-relaxed mt-0.5">Enter or scan<br class="hidden sm:block"> member barcode</p>
            </div>
        </div>

        <!-- Chevron -->
        <div class="hidden lg:block text-[#CBD5E1] shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <!-- Step 2 -->
        <div class="flex items-center gap-4 flex-1 w-full justify-start lg:justify-center">
            <div class="w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-[13px] shrink-0 shadow-sm">2</div>
            <div>
                <h3 class="font-bold text-[#0F172A] text-[14px]">Scan Book</h3>
                <p class="text-[#64748B] text-[13px] leading-relaxed mt-0.5">Scan book barcode<br class="hidden sm:block"> to proceed</p>
            </div>
        </div>

        <!-- Chevron -->
        <div class="hidden lg:block text-[#CBD5E1] shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>

        <!-- Step 3 -->
        <div class="flex items-center gap-4 flex-1 w-full justify-start lg:justify-center">
            <div class="w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-[13px] shrink-0 shadow-sm">3</div>
            <div>
                <h3 class="font-bold text-[#0F172A] text-[14px]">Confirm & Complete</h3>
                <p class="text-[#64748B] text-[13px] leading-relaxed mt-0.5">Review details and<br class="hidden sm:block"> complete transaction</p>
            </div>
        </div>

    </div>

</div>