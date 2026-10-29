<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#102B70">

    <title>Registration Status | Padre Garcia Polytechnic College Library System</title>
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>

<body class="min-h-dvh flex flex-col justify-between bg-[#F8FAFC] font-sans text-[#0F172A] antialiased">

    <main class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl">
            {{ $slot }}
        </div>
    </main>

    <footer class="py-5 px-4 text-center text-xs sm:text-sm text-[#64748B]">
        &copy; {{ date('Y') }} Padre Garcia Polytechnic College Library System. All rights reserved.
    </footer>

    @livewireScripts
</body>

</html>

