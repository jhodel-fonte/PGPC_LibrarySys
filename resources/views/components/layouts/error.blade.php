@props([
    'code' => null,
    'title' => 'Error',
    'bodyTitle' => null,
    'message' => 'Sorry, the page you are looking for could not be found.'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | Padre Garcia Polytechnic College Library</title>
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

    <!-- Open Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
</head>

<body class="antialiased font-sans h-full w-full overflow-hidden bg-slate-50 text-slate-900 selection:bg-[#FCC719] selection:text-[#102B70] flex items-center justify-center">
    <main class="relative flex h-full w-full flex-col items-center justify-center overflow-hidden px-4 py-6 text-center">
        <!-- Subtle Ambient Background Accents (confined inside overflow-hidden) -->
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-blue-100/60 blur-3xl sm:h-96 sm:w-96"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-amber-100/50 blur-3xl sm:h-96 sm:w-96"></div>

        <div class="relative z-10 flex w-full max-w-lg flex-col items-center justify-center">
            <!-- PGPC Logo -->
            <a href="{{ url('/') }}" class="mb-4 inline-flex items-center gap-2.5 transition-transform hover:scale-105 active:scale-95" wire:navigate>
                <img src="{{ asset('logo.webp') }}" alt="PGPC Logo" class="h-28 w-28 sm:h-32 sm:w-32 md:h-36 md:w-36 object-contain drop-shadow-sm" onerror="this.src='{{ asset('images/logo.webp') }}'">
            </a>

            <!-- Body Title / Heading -->
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl md:text-4xl">
                {{ $bodyTitle ?? $title }}
            </h1>

            <!-- Message -->
            <p class="mt-2.5 sm:mt-3.5 max-w-md text-sm sm:text-base leading-relaxed text-slate-500">
                {{ $message }}
            </p>

            {{ $slot ?? '' }}
        </div>
    </main>
</body>
</html>
