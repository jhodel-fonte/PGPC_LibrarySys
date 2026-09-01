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
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon" alt="Padre Garcia Polytechnic College Campus"
        loading="lazy"
        decoding="async"
    fetchpriority="low">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>

<body class="min-h-dvh bg-slate-100 font-sans text-slate-900 antialiased selection:bg-[#FCC719] selection:text-[#102B70] select-none cursor-default">

    <!-- Preloader -->
    <x-preloader />

    @persist('top-loader')
        <livewire:components.top-loader />
    @endpersist

    <main id="portal-content"
        class="opacity-0 transition-opacity duration-700 ease-in-out relative min-h-dvh lg:h-dvh overflow-hidden lg:grid lg:grid-cols-12">

        <!-- Left Hero Branding Side -->
        <x-auth.login-hero />

        <!-- Right Form Card Side -->
        <x-auth.login-form>
            {{ $slot }}
        </x-auth.login-form>

    </main>

    <script>
        document.addEventListener('pointerdown', function (e) {
            if (!e.target.closest('input, textarea, select, button, a, label, [contenteditable="true"]')) {
                if (document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA')) {
                    document.activeElement.blur();
                }
            }
        });
    </script>

    @livewireScripts
</body>

</html>
