@php
    $prefix = \App\Services\EmployeeRoleService::getRoutePrefix();
    $checkInRoute = route($prefix . 'circulation-desk.return');
    $checkOutRoute = route($prefix . 'circulation-desk.borrow');
    $borrowedBooksRoute = route($prefix . 'circulation-desk.borrowed-books');
    $canBorrow = \App\Services\EmployeeRoleService::canBorrow();
    $canReturn = \App\Services\EmployeeRoleService::canReturn();
@endphp

<section class="min-h-full bg-[#F8FAFC] font-sans text-[#132657]" aria-labelledby="circulation-title">
    <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
        <header class="mb-6 sm:mb-8">
            <h1 id="circulation-title" class="text-2xl font-bold tracking-tight text-[#102B70] sm:text-3xl">Circulation Desk</h1>
            <p class="mt-1 text-sm leading-relaxed text-[#52627F] sm:text-base">Choose an action to manage loans and returns.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5">
            @if($canBorrow)
                <a href="{{ $checkOutRoute }}" wire:navigate class="group flex flex-col rounded-2xl border border-[#D5E2F0] bg-white p-5 shadow-sm transition hover:border-[#9CB2D4] hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#2563EB]/25 sm:min-h-[280px] sm:p-6 lg:min-h-[360px] lg:p-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E8F2FF] sm:h-20 sm:w-20 lg:h-24 lg:w-24">
                        <img src="{{ asset('images/checkout-book.webp') }}" alt="" class="h-4/5 w-4/5 object-contain">
                    </span>
                    <h2 class="mt-3 text-lg sm:mt-5 sm:text-xl lg:text-2xl font-bold text-[#102B70]">Borrow</h2>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-[#52627F]">Issue items, check borrowing limits, and set due dates.</p>
                    <span class="mt-3 sm:mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#102B70] px-4 text-sm font-bold text-white group-hover:bg-[#0B225E]">Start borrowing</span>
                </a>
            @endif

            @if($canReturn)
                <a href="{{ $checkInRoute }}" wire:navigate class="group flex flex-col rounded-2xl border border-[#D5E2F0] bg-white p-5 shadow-sm transition hover:border-[#9CB2D4] hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#2563EB]/25 sm:min-h-[280px] sm:p-6 lg:min-h-[360px] lg:p-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E8F2FF] sm:h-20 sm:w-20 lg:h-24 lg:w-24">
                        <img src="{{ asset('images/checkin-book.webp') }}" alt="" class="h-4/5 w-4/5 object-contain">
                    </span>
                    <h2 class="mt-3 text-lg sm:mt-5 sm:text-xl lg:text-2xl font-bold text-[#102B70]">Return</h2>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-[#52627F]">Check in returned items, review their condition, and update availability.</p>
                    <span class="mt-3 sm:mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#102B70] px-4 text-sm font-bold text-white group-hover:bg-[#0B225E]">Start a return</span>
                </a>
            @endif

            <div class="grid grid-cols-1 gap-4">
                <a href="{{ route($prefix . 'circulation-desk.reservations') }}" wire:navigate class="group flex min-w-0 flex-col rounded-2xl border border-[#D5E2F0] bg-white p-4 shadow-sm transition hover:border-[#9CB2D4] hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#2563EB]/25 min-h-[160px] sm:p-5">
                    <h2 class="text-base font-bold text-[#102B70] sm:text-lg">Reservations</h2>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-[#52627F]">Review requests and manage pickups.</p>
                    <span class="mt-4 text-sm font-bold text-[#102B70]">View reservations <span aria-hidden="true">&rarr;</span></span>
                </a>
                @if(\App\Services\EmployeeRoleService::canManageFines())
                    <a href="{{ route($prefix . 'circulation-desk.fine-payments') }}" wire:navigate class="group flex min-w-0 flex-col rounded-2xl border border-[#D5E2F0] bg-white p-4 shadow-sm transition hover:border-[#9CB2D4] hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#2563EB]/25 min-h-[160px] sm:p-5">
                        <h2 class="text-base font-bold text-[#102B70] sm:text-lg">Process Fine Payment</h2>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-[#52627F]">Record a cashier receipt for a returned item.</p>
                        <span class="mt-4 text-sm font-bold text-[#102B70]">Process payment <span aria-hidden="true">&rarr;</span></span>
                    </a>
                @endif
            </div>
        </div>

        <a href="{{ $borrowedBooksRoute }}" wire:navigate class="mt-4 flex min-h-16 items-center justify-between gap-4 rounded-2xl border border-[#D5E2F0] bg-white px-5 py-4 text-[#102B70] shadow-sm transition hover:border-[#9CB2D4] hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#2563EB]/25">
            <span><strong class="block text-base">Active Borrowers</strong><span class="block text-sm font-normal text-[#52627F]">Find current loans and check their due dates.</span></span>
            <span class="shrink-0 text-sm font-bold">View borrowers <span aria-hidden="true">&rarr;</span></span>
        </a>

        <section class="mt-7" aria-labelledby="circulation-summary-title">
            <h2 id="circulation-summary-title" class="mb-3 text-base font-bold text-[#102B70]">Circulation overview</h2>
            <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-xl border border-[#D5E2F0] bg-white p-4"><dt class="text-sm text-[#52627F]">Active reservations</dt><dd class="mt-1 text-2xl font-bold tabular-nums text-[#102B70]">{{ number_format($reservedCount) }}</dd><dd class="text-xs text-[#64748B]">Pending and ready for pickup</dd></div>
                <div class="rounded-xl border border-[#D5E2F0] bg-white p-4"><dt class="text-sm text-[#52627F]">Borrowed items</dt><dd class="mt-1 text-2xl font-bold tabular-nums text-[#102B70]">{{ number_format($borrowedCount) }}</dd><dd class="text-xs text-[#64748B]">Currently on loan</dd></div>
                <div class="rounded-xl border border-[#D5E2F0] bg-white p-4"><dt class="text-sm text-[#52627F]">Due today</dt><dd class="mt-1 text-2xl font-bold tabular-nums text-[#102B70]">{{ number_format($dueTodayCount) }}</dd><dd class="text-xs text-[#64748B]">Items still on loan</dd></div>
                <div class="rounded-xl border border-[#D5E2F0] bg-white p-4"><dt class="text-sm text-[#52627F]">Unpaid fines</dt><dd class="mt-1 text-2xl font-bold tabular-nums text-[#102B70]">{{ number_format($unpaidFinesCount) }}</dd><dd class="text-xs text-[#64748B]">Fine records awaiting payment</dd></div>
            </dl>
        </section>
    </div>
</section>

