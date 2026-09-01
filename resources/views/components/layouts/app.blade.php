<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? "{$title} | PGPC Library" : config('app.name', 'PGPC Library') }}</title>

    <!-- icon-->
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>

<body class="antialiased font-sans min-h-dvh flex flex-col relative overflow-x-hidden bg-gray-50">

    <!-- Preloader -->
    <x-preloader />

    @persist('top-loader')
        <livewire:components.top-loader />
    @endpersist


    <div id="portal-content" class="opacity-0 transition-opacity duration-700 ease-in-out flex flex-col flex-1 w-full relative">

        <div class="fixed inset-0 z-10 bg-gradient-hero backdrop-blur-sm pointer-events-none"></div>

        <div id="main-app-wrapper" class="relative z-20 flex flex-col min-h-dvh w-full">

            <div class="flex-1 flex items-center justify-center w-full px-4 py-12 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </div>

    </div>
    @livewireScripts
</body>
</html>
