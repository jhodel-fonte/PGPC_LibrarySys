@props([
    'href' => '#'
])

<a href="{{ $href }}" class="flex items-center gap-3 group" aria-label="{{ config('settings.name') }} home" :class="typeof sidebarMinimized !== 'undefined' && sidebarMinimized ? 'justify-center w-full' : ''">
    <div class="w-10 h-10 shrink-0 rounded-full flex items-center justify-center overflow-hidden shadow-inner group-hover:scale-105 transition-transform duration-300">
        <img src="{{ asset('images/logo.webp') }}" alt="{{ config('settings.acronym') }} Logo" class="w-full h-full object-cover" />
    </div>
    <div class="flex flex-col" x-show="typeof sidebarMinimized === 'undefined' || !sidebarMinimized">
        <span class="font-bold text-sm tracking-wide text-white leading-tight" title="{{ config('settings.name') }}">{{ config('settings.name') }}</span>
        <span class="text-[10px] text-white/60 font-semibold tracking-wider uppercase mt-0.5" title="{{ config('settings.tagline') }}">{{ config('settings.tagline') }}</span>
    </div>
</a>