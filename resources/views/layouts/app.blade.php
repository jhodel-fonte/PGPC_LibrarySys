<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PGPC Library') }}</title>

    <!-- 1. PRELOAD THE LOGO FIRST -->
    <link rel="preload" as="image" href="{{ asset('logo.webp') }}">

    <!-- 2. Favicons (Will reuse the preloaded image) -->
    <link rel="icon" href="{{ asset('logo.webp') }}" type="image/webp">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>

<!-- 1. Removed opacity-0 and portal-content ID from the body -->
<body class="antialiased font-sans min-h-dvh flex flex-col relative overflow-x-hidden bg-gray-50">
    
    <!-- 2. Preloader is now visible because the body is not hidden -->
    <x-preloader />
    
    <!-- 3. Wrapped ALL page content inside the hidden portal-content div -->
    <div id="portal-content" class="opacity-0 transition-opacity duration-700 ease-in-out flex flex-col flex-1 w-full relative">
        
        <div class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat pointer-events-none"
             {{-- style="background-image: url('{{ asset('images/pgpc-ng.webp') }}')" --}}>
        </div>
            
        <div class="fixed inset-0 z-10 bg-gradient-hero backdrop-blur-sm pointer-events-none"></div>

        <div id="main-app-wrapper" class="relative z-20 flex flex-col min-h-dvh w-full">
            <livewire:global-announcement-banner />
            <div class="flex-1 flex items-center justify-center w-full px-4 py-12 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </div>

    </div>
    @livewireScripts
</body>
</html>