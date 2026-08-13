@props([
    'href' => '#',
    'active' => false,
])

@php
$classes = $active
            ? 'group flex items-center justify-between px-4 py-3 bg-white/10 rounded-lg transition-colors font-bold text-white'
            : 'group flex items-center justify-between px-4 py-2.5 rounded-lg text-white/70 hover:bg-white/5 hover:text-white transition-colors font-medium';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-center gap-3">
        @if (isset($icon))
            <span class="{{ $active ? 'text-[#fcc719]' : 'text-white/70 group-hover:text-white' }} flex shrink-0 transition-colors">
                {{ $icon }}
            </span>
        @endif
        <span>{{ $slot }}</span>
    </div>
    @if (isset($trailing))
        <span class="flex shrink-0 opacity-50">
            {{ $trailing }}
        </span>
    @endif
</a>