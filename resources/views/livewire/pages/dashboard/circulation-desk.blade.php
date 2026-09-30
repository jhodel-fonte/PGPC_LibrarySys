@php
    $role = request()->segment(1) ?: 'admin';
    $checkInRoute = route($role . '.circulation-desk.return');
    $checkOutRoute = route($role . '.circulation-desk.borrow');
@endphp

<section class="h-full min-h-0 overflow-hidden bg-[#F8FBFF] font-sans text-[#132657]" aria-labelledby="circulation-title">
    <div class="mx-auto flex h-full min-h-0 w-full max-w-[46rem] flex-col gap-2 px-3 pt-6 pb-3 sm:gap-3 sm:px-5 sm:pt-8 sm:pb-4 lg:max-w-[76rem] lg:gap-5 lg:px-10 lg:pt-12 lg:pb-6">
        <div class="flex shrink-0 flex-col items-center text-center">
            <h1 id="circulation-title" class="mt-1 text-[clamp(1.55rem,4vw,2.65rem)] font-extrabold leading-none tracking-[-0.035em] text-[#102B70] lg:mt-2">Circulation Desk</h1>
            <p class="mt-1.5 max-w-xl text-[11px] font-medium leading-snug text-[#52627F] sm:text-xs lg:mt-2 lg:text-base">Process book loans and returns quickly and efficiently.</p>
        </div>

        <div class="grid shrink-0 grid-cols-1 gap-2.5 sm:gap-3 lg:grid-cols-2 lg:gap-6">
            <a href="{{ $checkOutRoute }}" wire:navigate class="group flex items-center gap-3 overflow-hidden rounded-2xl border border-[#D5E2F0] bg-gradient-to-br from-white to-[#F2F8FF] p-3 shadow-[0_8px_24px_rgba(16,43,112,0.06)] outline-none transition duration-200 hover:-translate-y-0.5 hover:border-[#B8C8DC] hover:shadow-[0_18px_38px_rgba(16,43,112,0.12)] focus-visible:ring-4 focus-visible:ring-[#2563EB]/25 motion-reduce:transform-none motion-reduce:transition-none sm:gap-5 sm:p-4 lg:flex-col lg:items-stretch lg:p-8 lg:text-center" aria-label="Go to Check-Out">
                <div class="flex aspect-square h-[76px] shrink-0 items-center justify-center rounded-full bg-[#E8F2FF] sm:h-24 lg:mx-auto lg:h-32">
                    <img src="{{ asset('images/checkout-book.webp') }}" alt="" class="h-[82%] w-[82%] object-contain drop-shadow-sm transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-1 motion-reduce:transform-none motion-reduce:transition-none">
                </div>
                <div class="min-w-0 flex-1 lg:mt-5 lg:flex-none">
                    <h2 class="text-lg font-extrabold leading-tight tracking-[-0.025em] text-[#102B70] sm:text-xl lg:text-3xl">Check-Out</h2>
                    <p class="mt-1 max-w-md text-[10px] font-medium leading-[1.45] text-[#52627F] sm:text-xs lg:mx-auto lg:mt-3 lg:text-sm">Issue books to an eligible student member, review borrowing limits, and record due dates.</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-[#102B70] transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transform-none motion-reduce:transition-none lg:hidden" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                </span>
                <span class="mt-3 hidden w-full items-center justify-center gap-3 rounded-xl bg-[#102B70] px-5 py-3.5 text-sm font-bold text-white shadow-[0_8px_18px_rgba(16,43,112,.18)] transition-colors group-hover:bg-[#0B225E] lg:flex lg:mt-8">
                    Go to Check-Out
                    <svg class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transform-none motion-reduce:transition-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                </span>
            </a>

            <a href="{{ $checkInRoute }}" wire:navigate class="group flex items-center gap-3 overflow-hidden rounded-2xl border border-[#D5E2F0] bg-gradient-to-br from-white to-[#F2F8FF] p-3 shadow-[0_8px_24px_rgba(16,43,112,0.06)] outline-none transition duration-200 hover:-translate-y-0.5 hover:border-[#B8C8DC] hover:shadow-[0_18px_38px_rgba(16,43,112,0.12)] focus-visible:ring-4 focus-visible:ring-[#2563EB]/25 motion-reduce:transform-none motion-reduce:transition-none sm:gap-5 sm:p-4 lg:flex-col lg:items-stretch lg:p-8 lg:text-center" aria-label="Go to Check-In">
                <div class="flex aspect-square h-[76px] shrink-0 items-center justify-center rounded-full bg-[#E8F2FF] sm:h-24 lg:mx-auto lg:h-32">
                    <img src="{{ asset('images/checkin-book.webp') }}" alt="" class="h-[82%] w-[82%] object-contain drop-shadow-sm transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-1 motion-reduce:transform-none motion-reduce:transition-none">
                </div>
                <div class="min-w-0 flex-1 lg:mt-5 lg:flex-none">
                    <h2 class="text-lg font-extrabold leading-tight tracking-[-0.025em] text-[#102B70] sm:text-xl lg:text-3xl">Check-In</h2>
                    <p class="mt-1 max-w-md text-[10px] font-medium leading-[1.45] text-[#52627F] sm:text-xs lg:mx-auto lg:mt-3 lg:text-sm">Accept returned books, verify condition, and update availability records.</p>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-[#102B70] transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transform-none motion-reduce:transition-none lg:hidden" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                </span>
                <span class="mt-3 hidden w-full items-center justify-center gap-3 rounded-xl bg-[#102B70] px-5 py-3.5 text-sm font-bold text-white shadow-[0_8px_18px_rgba(16,43,112,.18)] transition-colors group-hover:bg-[#0B225E] lg:flex lg:mt-8">
                    Go to Check-In
                    <svg class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transform-none motion-reduce:transition-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                </span>
            </a>
        </div>
        
        <ol class="grid shrink-0 grid-cols-3 border-y border-[#DDE6F1] py-2 sm:py-3 lg:py-4" aria-label="Circulation workflow">
            <li class="flex min-w-0 items-center justify-center gap-2 border-r border-[#DDE6F1] px-1.5 sm:gap-3 sm:px-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E6F1FF] text-xs font-extrabold tabular-nums text-[#1354C4] sm:h-10 sm:w-10">1</span>
                <span class="min-w-0"><strong class="block text-[10px] font-extrabold text-[#132657] sm:text-xs lg:text-sm">Identify</strong><span class="hidden text-[10px] leading-tight text-[#52627F] sm:block lg:text-xs">Scan member ID or barcode</span></span>
            </li>
            <li class="flex min-w-0 items-center justify-center gap-2 border-r border-[#DDE6F1] px-1.5 sm:gap-3 sm:px-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E6F1FF] text-xs font-extrabold tabular-nums text-[#1354C4] sm:h-10 sm:w-10">2</span>
                <span class="min-w-0"><strong class="block text-[10px] font-extrabold text-[#132657] sm:text-xs lg:text-sm">Process</strong><span class="hidden text-[10px] leading-tight text-[#52627F] sm:block lg:text-xs">Review queue and condition</span></span>
            </li>
            <li class="flex min-w-0 items-center justify-center gap-2 px-1.5 sm:gap-3 sm:px-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#E6F1FF] text-xs font-extrabold tabular-nums text-[#1354C4] sm:h-10 sm:w-10">3</span>
                <span class="min-w-0"><strong class="block text-[10px] font-extrabold text-[#132657] sm:text-xs lg:text-sm">Finalize</strong><span class="hidden text-[10px] leading-tight text-[#52627F] sm:block lg:text-xs">Confirm &amp; log records</span></span>
            </li>
        </ol>

    </div>
</section>
