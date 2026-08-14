<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PGPC Library') }}</title>

        <!-- Logo -->
        <link rel="icon" href="{{ asset('logo.webp') }}" type="image/webp">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <tallstackui:script />
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-preloader />
        {{-- <livewire:components.top-loader /> --}}

        <div x-data="{ sidebarOpen: false, sidebarMinimized: false }" class="min-h-screen bg-gray-100 flex overflow-hidden relative">
            <livewire:layout.sidebar-nav />
            
            <!-- Page Content -->
            <div :class="sidebarMinimized ? 'md:ml-[80px]' : 'md:ml-[280px]'" class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-300 ease-in-out w-full">
                <livewire:layout.topbar />
                
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
