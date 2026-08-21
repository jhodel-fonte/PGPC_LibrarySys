<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? "{$title} | PGPC Library" : config('app.name', 'PGPC Library') }}</title>



        <!-- Logo -->
        <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <tallstackui:script />
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        @persist('preloader')
            <x-preloader />
        @endpersist
        @persist('top-loader')
            <livewire:components.top-loader />
        @endpersist
        <div x-data="{ 
            sidebarOpen: false, 
            sidebarMinimized: localStorage.getItem('sidebarMinimized') === 'true' 
        }" 
        x-effect="localStorage.setItem('sidebarMinimized', sidebarMinimized)"
        class="h-screen bg-gray-100 flex overflow-hidden relative">
            <livewire:layout.sidebar-nav />
            
            <!-- Page Content -->
            <div :class="sidebarMinimized ? 'md:ml-[80px]' : 'md:ml-[280px]'" class="flex-1 flex flex-col min-w-0 h-screen transition-all duration-300 ease-in-out w-full">
                {{-- <livewire:layout.topbar :activepage="$title ?? 'Dashboard'" /> --}}
                <x-top-navbar :activepage="$title ?? 'Dashboards'" />
                
                <main class="flex-1 overflow-y-auto">
                    <livewire:components.global-announcement-banner />
                    {{ $slot }}
                </main>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
