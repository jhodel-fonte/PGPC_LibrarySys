@props([
    'href' => '#',
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-navy-primary transition-colors font-medium']) }}>
    @if(isset($icon))
        <span class="w-5 h-5 text-gray-400 group-hover:text-navy-primary transition-colors flex items-center justify-center">
            {{ $icon }}
        </span>
    @endif
    {{ $slot }}
</a>
