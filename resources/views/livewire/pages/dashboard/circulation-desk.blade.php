@props([
    'checkInRoute' => route(request()->segment(1) . '.circulation-desk.return'),
    'checkOutRoute' => route(request()->segment(1) . '.circulation-desk.borrow'),
])

<div class="min-h-full w-full bg-[#F8FAFC] px-4 py-6 font-sans sm:px-6 lg:px-8">

    <div class="mx-auto w-full max-w-6xl">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <div class="mb-7">
            <h1 class="text-2xl font-bold tracking-tight text-[#102B70] sm:text-3xl">
                Circulation Desk
            </h1>

            <p class="mt-1 text-sm text-[#64748B]">
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
                class="group relative flex min-h-[350px] flex-col overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#CBD5E1] hover:shadow-[0_12px_24px_rgba(16,43,112,0.06)] focus:outline-none focus:ring-4 focus:ring-[#102B70]/10 sm:p-8"
            >

                {{-- Soft Background Glow --}}
                <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#EFF6FF] opacity-60 blur-2xl transition-transform duration-500 group-hover:scale-110"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-[#EFF6FF]/20 opacity-40 blur-xl"></div>


                {{-- Card Header --}}
                <div class="relative z-10 flex items-start justify-between gap-4">

                    <div class="flex-1 mt-3">

                        <h2 class="text-3xl font-bold tracking-tight text-[#102B70]">
                            Check-Out
                        </h2>

                        <p class="mt-2 text-sm leading-relaxed text-[#64748B]">
                            Issue books to a member and create a new borrowing transaction.
                        </p>

                    </div>


                    {{-- Illustration --}}
                    <div class="relative z-10 h-24 w-24 shrink-0 transition-transform duration-500 group-hover:-translate-y-1 group-hover:scale-105 sm:h-28 sm:w-28">
                        <img
                            src="{{ asset('images/checkout-book.webp') }}"
                            alt="Check-Out"
                            class="h-full w-full object-contain drop-shadow-[0_8px_16px_rgba(16,43,112,0.06)]"
                        >
                    </div>

                </div>


                {{-- Action --}}
                <div class="relative z-10 mt-auto pt-6">

                    <div class="flex items-center justify-between rounded-2xl bg-[#102B70] px-5 py-4 transition-colors duration-300 group-hover:bg-[#0B225E]">

                        <div>
                            <p class="text-sm font-bold text-white">
                                Start Check-Out
                            </p>

                            <p class="mt-0.5 text-xs text-[#EFF6FF]/80">
                                Begin borrowing transaction
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FCC719] transition-transform duration-300 group-hover:translate-x-1">

                            <svg
                                class="h-4 w-4 text-[#102B70]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2.5"
                                    d="M5 12h14m-6-6l6 6-6 6"
                                />
                            </svg>

                        </div>

                    </div>

                </div>

            </a>


            <a
                href="{{ $checkInRoute }}"
                wire:navigate
                class="group relative flex min-h-[350px] flex-col overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#CBD5E1] hover:shadow-[0_12px_24px_rgba(16,43,112,0.06)] focus:outline-none focus:ring-4 focus:ring-[#102B70]/10 sm:p-8"
            >

                {{-- Soft Background Glow --}}
                <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#EFF6FF] opacity-60 blur-2xl transition-transform duration-500 group-hover:scale-110"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-[#EFF6FF]/20 opacity-40 blur-xl"></div>


                {{-- Card Header --}}
                <div class="relative z-10 flex items-start justify-between gap-4">

                    <div class="flex-1 mt-3">

                        <h2 class="text-3xl font-bold tracking-tight text-[#102B70]">
                            Check-In
                        </h2>

                        <p class="mt-2 text-sm leading-relaxed text-[#64748B]">
                            Receive returned books, inspect their condition, and complete the return.
                        </p>

                    </div>


                    {{-- Illustration --}}
                    <div class="relative z-10 h-24 w-24 shrink-0 transition-transform duration-500 group-hover:-translate-y-1 group-hover:scale-105 sm:h-28 sm:w-28">
                        <img
                            src="{{ asset('images/checkin-book.webp') }}"
                            alt="Check-In"
                            class="h-full w-full object-contain drop-shadow-[0_8px_16px_rgba(16,43,112,0.06)]"
                        >
                    </div>

                </div>


                {{-- Action --}}
                <div class="relative z-10 mt-auto pt-6">

                    <div class="flex items-center justify-between rounded-2xl bg-[#102B70] px-5 py-4 transition-colors duration-300 group-hover:bg-[#0B225E]">

                        <div>
                            <p class="text-sm font-bold text-white">
                                Start Check-In
                            </p>

                            <p class="mt-0.5 text-xs text-[#EFF6FF]/80">
                                Begin return transaction
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FCC719] transition-transform duration-300 group-hover:translate-x-1">

                            <svg
                                class="h-4 w-4 text-[#102B70]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2.5"
                                    d="M5 12h14m-6-6l6 6-6 6"
                                />
                            </svg>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        {{-- =========================================================
            QUICK GUIDE
        ========================================================== --}}
        <div class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm sm:p-6">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-center">

                {{-- Title --}}
                <div class="flex items-center gap-3 lg:w-[27%]">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EFF6FF]">
                        <svg
                            class="h-5 w-5 text-[#102B70]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-[#0F172A]">
                            Quick Guide
                        </h3>

                        <p class="mt-0.5 text-xs text-[#64748B]">
                            Simple transaction workflow
                        </p>
                    </div>

                </div>


                {{-- Guide Steps --}}
                <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">

                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] px-3 py-2.5">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-[11px] font-bold text-white">
                            1
                        </span>

                        <div>
                            <p class="text-xs font-bold text-[#334155]">
                                Identify
                            </p>

                            <p class="text-[11px] text-[#64748B]">
                                Member or book
                            </p>
                        </div>

                    </div>


                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] px-3 py-2.5">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-[11px] font-bold text-white">
                            2
                        </span>

                        <div>
                            <p class="text-xs font-bold text-[#334155]">
                                Process
                            </p>

                            <p class="text-[11px] text-[#64748B]">
                                Review transaction
                            </p>
                        </div>

                    </div>


                    <div class="flex items-center gap-3 rounded-xl bg-[#F8FAFC] px-3 py-2.5">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#102B70] text-[11px] font-bold text-white">
                            3
                        </span>

                        <div>
                            <p class="text-xs font-bold text-[#334155]">
                                Complete
                            </p>

                            <p class="text-[11px] text-[#64748B]">
                                Confirm transaction
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
