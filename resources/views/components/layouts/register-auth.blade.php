<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#102b70">

    <title>Student Registration | Padre Garcia Polytechnic College Library System</title>
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>

<body class="min-h-dvh bg-slate-100 font-sans text-slate-900 antialiased select-none cursor-default">

    <!-- Preloader -->
    <x-preloader />

    @persist('top-loader')
        <livewire:components.top-loader />
    @endpersist

    <main id="portal-content"
        class="opacity-0 transition-opacity duration-700 ease-in-out relative min-h-dvh lg:h-dvh overflow-hidden lg:grid lg:grid-cols-12">

        <section
            class="relative flex flex-col min-h-dvh lg:min-h-0 lg:h-dvh lg:overflow-y-auto scroll-smooth bg-slate-100/80 px-4 py-8 sm:px-8 lg:px-8 xl:px-12 lg:order-1 lg:col-span-6 xl:col-span-5 outline-none focus:outline-none select-none cursor-default">

            <div class="select-none mb-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200/90 text-xs font-bold text-slate-700 hover:text-[#102b70] shadow-xs hover:shadow-sm transition-all group backdrop-blur-sm outline-none focus:outline-none focus:ring-0 select-none" wire:navigate>
                    <svg class="w-4 h-4 text-slate-500 group-hover:text-[#102b70] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Back to Home</span>
                </a>
            </div>

            <div
                class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#102b70] via-[#fcc719] to-[#102b70] lg:hidden pointer-events-none select-none">
            </div>
            <div class="absolute right-0 top-0 h-64 w-64 rounded-bl-full bg-blue-50/80 pointer-events-none select-none"></div>

            <div class="relative z-10 w-full max-w-[560px] m-auto py-8">
                <div class="mb-8 flex items-center justify-between lg:hidden pr-32 select-none">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 outline-none focus:outline-none" wire:navigate>
                        <img src="{{ asset('logo.webp') }}" alt="PGPC logo"
                            class="h-9 w-9 object-contain"
                            onerror="this.src='{{ asset('images/logo.webp') }}'">
                        <div class="text-left">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#102b70] leading-none">Padre Garcia Polytechnic College Library</span>
                            <span class="block text-[9px] font-semibold text-slate-500 uppercase tracking-widest mt-0.5 leading-none">System</span>
                        </div>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </section>

        <!-- Right Hero Branding Side -->
        <x-auth.login-hero order="right" />

    </main>

    <script>
        document.addEventListener('pointerdown', function (e) {
            if (!e.target.closest('input, textarea, select, button, a, label, [contenteditable="true"]')) {
                if (document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA' || document.activeElement.tagName === 'SELECT')) {
                    document.activeElement.blur();
                }
            }
        });
    </script>

    @livewireScripts
</body>

</html>

