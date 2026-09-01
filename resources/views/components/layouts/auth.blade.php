@php
    $isStaff = request()->routeIs('employee.login') || request()->is('portal/employee*') || request()->is('employee/*') || request()->is('staff/*');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#102b70">

    <title>{{ $isStaff ? 'Staff Login' : 'Student Login' }} | Padre Garcia Polytechnic College Library System</title>
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>

<body class="min-h-dvh bg-slate-100 font-sans text-slate-900 antialiased selection:bg-[#FCC719] selection:text-[#102B70]">

    <!-- Preloader -->
    <x-preloader />

    @persist('top-loader')
        <livewire:components.top-loader />
    @endpersist

    <main id="portal-content"
        class="opacity-0 transition-opacity duration-700 ease-in-out relative min-h-dvh lg:h-dvh overflow-hidden lg:grid lg:grid-cols-[minmax(0,1.08fr)_minmax(440px,0.92fr)]">

        <!-- Hero Information Side -->
        <section
            class="relative hidden min-h-dvh lg:h-dvh overflow-hidden bg-[#102b70] lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16 lg:order-1">
            <img src="{{ asset('images/pgpc-ng.webp') }}" alt="PGPC Campus"
                class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-[#071943]/95 via-[#102b70]/88 to-[#102b70]/70"></div>
            <div class="absolute -left-24 bottom-20 h-80 w-80 rounded-full border border-white/10 pointer-events-none"></div>
            <div class="absolute -left-8 bottom-36 h-48 w-48 rounded-full border border-[#fcc719]/25 pointer-events-none"></div>

            <a href="{{ url('/') }}" class="relative z-10 inline-flex w-fit items-center gap-3.5 text-white group" wire:navigate>
                <img src="{{ asset('logo.webp') }}" alt="PGPC Logo"
                    class="h-12 w-12 object-contain"
                    onerror="this.src='{{ asset('images/logo.webp') }}'">
                <div>
                    <span class="block text-base font-semibold leading-tight">Padre Garcia Polytechnic College</span>
                    <span class="block text-xs font-bold uppercase tracking-[0.18em] text-[#fcc719]">Library System</span>
                </div>
            </a>

            <div class="relative z-10 max-w-2xl pb-8">
                <div
                    class="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm shadow-xs">
                    <span class="h-2 w-2 rounded-full bg-[#fcc719]"></span>
                    {{ $isStaff ? 'Authorized personnel access' : 'Student & Member access' }}
                </div>
                <h1 class="max-w-xl text-5xl font-bold leading-[1.08] tracking-tight text-white xl:text-6xl">
                    {{ $isStaff ? 'Manage knowledge.' : 'Explore knowledge.' }}<br>
                    <span class="text-[#fcc719]">Empower learning.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-blue-100/90">
                    {{ $isStaff ? 'A secure workspace for library operations, circulation, cataloging, and member services.' : 'A secure workspace for library resources, catalog search, book reservations, and borrow transactions.' }}
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
                        <p class="text-sm font-semibold text-white">{{ $isStaff ? 'Members' : 'Reservations' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                        <svg class="mb-3 h-6 w-6 text-[#fcc719]" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-semibold text-white">{{ $isStaff ? 'Reports' : 'Borrowing' }}</p>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-xs text-blue-100/65">© {{ date('Y') }} PGPC Library. All rights reserved.
            </p>
        </section>

        <!-- Form Card Side -->
        <section
            class="relative flex flex-col min-h-dvh lg:min-h-0 lg:h-dvh lg:overflow-y-auto bg-slate-50 px-4 py-8 sm:px-8 lg:px-12 lg:order-2">

            <!-- Top Right Back to Home Button -->
            <div class="absolute right-4 top-4 sm:right-6 sm:top-6 z-30">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200/90 text-xs font-bold text-slate-700 hover:text-[#102b70] shadow-xs hover:shadow-sm transition-all group backdrop-blur-sm" wire:navigate>
                    <svg class="w-4 h-4 text-slate-500 group-hover:text-[#102b70] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Back to Home</span>
                </a>
            </div>

            <div
                class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#102b70] via-[#fcc719] to-[#102b70] lg:hidden">
            </div>
            <div class="absolute right-0 top-0 h-64 w-64 rounded-bl-full bg-blue-50/80 pointer-events-none"></div>

            <div class="relative z-10 w-full max-w-md m-auto py-8">
                <!-- Mobile Logo Header -->
                <div class="mb-8 flex items-center justify-between lg:hidden pr-32">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5" wire:navigate>
                        <img src="{{ asset('logo.webp') }}" alt="PGPC logo"
                            class="h-9 w-9 object-contain"
                            onerror="this.src='{{ asset('images/logo.webp') }}'">
                        <div class="text-left">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#102b70] leading-none">PGPC Library</span>
                            <span class="block text-[9px] font-semibold text-slate-500 uppercase tracking-widest mt-0.5 leading-none">System</span>
                        </div>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </section>
    </main>

    @livewireScripts
</body>

</html>
