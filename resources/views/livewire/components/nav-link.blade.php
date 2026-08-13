@props(['active' => false, 'mobile' => false, 'href' => '#'])

@php
    $baseClasses = 'font-medium transition-colors ';

    if ($mobile) {
        // Mobile Styling
        $stateClasses = $active 
            ? 'text-white font-bold dark:text-[#fcc719]' 
            : 'text-gray-300 hover:text-[#fcc719] dark:text-gray-400 dark:hover:text-white';
        $classes = $baseClasses . $stateClasses . ' py-2 border-b border-white/10 block';
    } else {
        // Desktop Styling
        $stateClasses = $active 
            ? 'text-[#fcc719] dark:text-[#fcc719]' 
            : 'text-gray-100 hover:text-[#fcc719] dark:text-gray-300 dark:hover:text-white nav-text';
        $classes = $baseClasses . $stateClasses;
    }
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>