@props([
    'code' => '404',
    'title' => 'Not Found',
    'message' => 'Sorry, the page you are looking for could not be found.'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | PGPC Library</title>
    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
</head>

<body class="antialiased font-sans min-h-dvh flex flex-col relative overflow-x-hidden bg-gray-50">
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
        <h1 class="text-6xl font-bold text-gray-800">{{ $code }}</h1>
        <p class="mt-4 text-xl text-gray-600">{{ $title }}</p>
        <p class="mt-2 text-gray-500">{{ $message }}</p>
    </div>
</body>
</html>
