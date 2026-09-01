@props(['order' => 'left'])

@php
    $orderClasses = $order === 'right'
        ? 'lg:order-2 lg:col-span-6 xl:col-span-7'
        : 'lg:order-1 lg:col-span-6 xl:col-span-7';
@endphp

<section
    {{ $attributes->class(["relative hidden min-h-dvh lg:h-dvh overflow-hidden bg-[#102b70] lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16 {$orderClasses}"]) }}>
    <img
        src="{{ asset('images/pgpc-ng.webp') }}"
        alt="Padre Garcia Polytechnic College Campus"
        loading="lazy"
        decoding="async"
        fetchpriority="low"
        class="absolute inset-0 h-full w-full object-cover pointer-events-none select-none"
    >
    <div class="absolute inset-0 bg-gradient-to-br from-[#071943]/95 via-[#102b70]/88 to-[#102b70]/70"></div>
    <div class="absolute -left-24 bottom-20 h-80 w-80 rounded-full border border-white/10 pointer-events-none"></div>
    <div class="absolute -left-8 bottom-36 h-48 w-48 rounded-full border border-[#fcc719]/25 pointer-events-none"></div>

    <a href="{{ url('/') }}" class="relative z-10 inline-flex w-fit items-center gap-3.5 text-white group" wire:navigate>
        <img src="{{ asset('logo.webp') }}" alt="Padre Garcia Polytechnic College Logo"
            loading="lazy"
            decoding="async"
            fetchpriority="low"
            class="h-12 w-12 object-contain"
            onerror="this.src='{{ asset('images/logo.webp') }}'">
        <div>
            <span class="block text-base font-semibold leading-tight">Padre Garcia Polytechnic College</span>
            <span class="block text-xs font-bold uppercase tracking-[0.18em] text-[#fcc719]">Library System</span>
        </div>
    </a>

    <div class="relative z-10 max-w-2xl pb-8">
        <h1 class="max-w-xl text-5xl font-bold leading-[1.08] tracking-tight text-white xl:text-6xl">
            Explore knowledge.<br>
            <span class="text-[#fcc719]">Empower learning.</span>
        </h1>
        <p class="mt-6 max-w-xl text-lg leading-8 text-blue-100/90">
            A secure workspace for library resources, catalog search, book reservations, and borrow transactions.
        </p>

        <div class="mt-10 grid max-w-xl grid-cols-3 gap-3">
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <svg class="mb-3 h-6 w-6 text-[#fcc719]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5A8.5 8.5 0 003 6.253v13A8.5 8.5 0 017.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5A8.5 8.5 0 0121 6.253v13A8.5 8.5 0 0016.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-sm font-semibold text-white">Cataloging</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <svg class="mb-3 h-6 w-6 text-[#fcc719]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-sm font-semibold text-white">Reservations</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <svg class="mb-3 h-6 w-6 text-[#fcc719]" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-semibold text-white">Borrowing</p>
            </div>
        </div>
    </div>

    <p class="relative z-10 text-xs text-blue-100/65">© {{ date('Y') }} Padre Garcia Polytechnic College Library. All rights reserved.
    </p>
</section>
