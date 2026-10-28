@props([
    'active' => false,
    'href' => '#'
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'block px-3 py-2 text-sm rounded-xl transition-all duration-200 ' . ($active ? 'bg-white text-[#102B70] font-bold shadow-sm' : 'text-white/80 hover:text-white hover:bg-white/10 font-medium')]) }}>
    {{ $slot }}
</a>
