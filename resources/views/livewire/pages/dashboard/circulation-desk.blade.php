@props([
    'checkInRoute' => route(request()->segment(1) . '.circulation-desk.return'),
    'checkOutRoute' => route(request()->segment(1) . '.circulation-desk.borrow'),
])

<div class="min-h-full w-full bg-[#F8FAFC] px-4 py-6 font-sans sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-6xl flex flex-col gap-8">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold tracking-tight text-[#102B70] sm:text-3xl">
                Circulation Desk
            </h1>
            <p class="text-sm text-[#64748B] font-medium">
                Process book loans and returns quickly and efficiently.
            </p>
        </div>

        {{-- =========================================================
            MAIN TRANSACTION OPTIONS
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- =====================================================
                CHECK-OUT / BORROW
            ====================================================== --}}
            <a
                href="{{ $checkOutRoute }}"
                wire:navigate
                class="group relative flex min-h-[340px] flex-col justify-between overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-6 sm:p-8 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-[#CBD5E1] hover:shadow-md focus:outline-none focus:ring-4 focus:ring-[#102B70]/10"
            >
                {{-- Card Header & Visual --}}
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-[#EFF6FF] text-[#102B70] border border-[#DBEAFE] mb-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            Issue Books
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#102B70]">
                            Check-Out
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-[#64748B]">
                            Issue books to an eligible student member, review borrowing limits, and record due dates.
                        </p>
                    </div>

                    {{-- Illustration --}}
                    <div class="relative h-24 w-24 sm:h-28 sm:w-28 shrink-0 transition-transform duration-300 group-hover:scale-105">
                        <img
                            src="{{ asset('images/checkout-book.webp') }}"
                            alt="Check-Out"
                            class="h-full w-full object-contain drop-shadow-sm"
                        >
                    </div>
                </div>

                {{-- Action Strip --}}
                <div class="pt-6">
                    <div class="flex items-center justify-between rounded-xl bg-[#102B70] px-5 py-4 transition-colors duration-200 group-hover:bg-[#0B225E] shadow-2xs">
                        <div>
                            <p class="text-sm font-bold text-white">
                                Start Check-Out
                            </p>
                            <p class="mt-0.5 text-xs text-[#EFF6FF]/80">
                                Open checkout workstation
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FCC719] text-[#102B70] transition-transform duration-200 group-hover:translate-x-1 shadow-2xs">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14m-6-6l6 6-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>

            {{-- =====================================================
                CHECK-IN / RETURN
            ====================================================== --}}
            <a
                href="{{ $checkInRoute }}"
                wire:navigate
                class="group relative flex min-h-[340px] flex-col justify-between overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-6 sm:p-8 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-[#CBD5E1] hover:shadow-md focus:outline-none focus:ring-4 focus:ring-[#102B70]/10"
            >
                {{-- Card Header & Visual --}}
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0] mb-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Receive Returns
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#102B70]">
                            Check-In
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-[#64748B]">
                            Scan returned books, assess physical condition, calculate overdue fines, and update inventory.
                        </p>
                    </div>

                    {{-- Illustration --}}
                    <div class="relative h-24 w-24 sm:h-28 sm:w-28 shrink-0 transition-transform duration-300 group-hover:scale-105">
                        <img
                            src="{{ asset('images/checkin-book.webp') }}"
                            alt="Check-In"
                            class="h-full w-full object-contain drop-shadow-sm"
                        >
                    </div>
                </div>

                {{-- Action Strip --}}
                <div class="pt-6">
                    <div class="flex items-center justify-between rounded-xl bg-[#102B70] px-5 py-4 transition-colors duration-200 group-hover:bg-[#0B225E] shadow-2xs">
                        <div>
                            <p class="text-sm font-bold text-white">
                                Start Check-In
                            </p>
                            <p class="mt-0.5 text-xs text-[#EFF6FF]/80">
                                Open check-in workstation
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FCC719] text-[#102B70] transition-transform duration-200 group-hover:translate-x-1 shadow-2xs">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14m-6-6l6 6-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>

        </div>

        {{-- =========================================================
            QUICK GUIDE
        ========================================================== --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center">

                {{-- Title --}}
                <div class="flex items-center gap-3 lg:w-[28%] shrink-0">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EFF6FF] border border-[#DBEAFE] text-[#102B70]">
                        <svg class="h-5 w-5 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-[#0F172A]">
                            Circulation Workflow
                        </h3>
                        <p class="mt-0.5 text-xs text-[#64748B] font-medium">
                            Standard desk operating procedure
                        </p>
                    </div>
                </div>

                {{-- Guide Steps --}}
                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-xs font-bold text-white tabular-nums shadow-2xs">
                            1
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#0F172A] truncate">
                                Identify
                            </p>
                            <p class="text-xs text-[#64748B] truncate mt-0.5">
                                Scan member ID or barcode
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-xs font-bold text-white tabular-nums shadow-2xs">
                            2
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#0F172A] truncate">
                                Process
                            </p>
                            <p class="text-xs text-[#64748B] truncate mt-0.5">
                                Review queue and condition
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3.5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-xs font-bold text-white tabular-nums shadow-2xs">
                            3
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#0F172A] truncate">
                                Finalize
                            </p>
                            <p class="text-xs text-[#64748B] truncate mt-0.5">
                                Confirm & log records
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
