@props(['variant' => 'primary', 'mobile' => false, 'href' => null, 'isButton' => false])

@php
    $baseClasses = 'transition-all duration-300 text-center ';
    
    // Shape differences between Desktop and Mobile
    $baseClasses .= $mobile ? 'px-5 py-3 rounded-xl w-full block ' : 'px-5 py-2.5 rounded-full inline-block ';

    if ($variant === 'primary') {
        $classes = $baseClasses . 'bg-[#fcc719] text-[#102b70] font-bold hover:bg-[#ffd84c] hover:shadow-md dark:bg-[#fcc719] dark:text-gray-900';
    } else {
        $classes = $baseClasses . 'border border-white/20 text-white font-semibold hover:bg-white hover:text-[#102b70] hover:border-white cursor-pointer dark:border-gray-600 dark:hover:bg-gray-800 dark:hover:text-white';
    }
@endphp

@if($isButton)
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@endif