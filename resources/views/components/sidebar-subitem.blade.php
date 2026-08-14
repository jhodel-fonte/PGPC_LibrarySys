@props([
    'active' => false,
    'href' => '#'
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'block px-3 py-2 text-sm rounded-lg transition-colors ' . ($active ? 'text-[#FCC719] font-bold bg-white/5' : 'text-white/60 hover:text-white hover:bg-white/5')]) }}>
    {{ $slot }}
</a>
