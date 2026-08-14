@props([
    'active' => false,
    'href' => '#'
])

@if (isset($subitems))
    <div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="w-full">
        <!-- Accordion Toggle Button -->
        <button 
            @click="open = !open; if(sidebarMinimized) sidebarMinimized = false" 
            class="w-full group flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-300 {{ $active ? 'bg-white/10 text-white font-semibold shadow-sm' : 'text-white/70 hover:bg-white/5 hover:text-white font-medium' }}"
        >
            <div class="flex items-center w-full overflow-hidden" :class="sidebarMinimized ? 'justify-center' : 'gap-3'">
                @if (isset($icon))
                    <span class="{{ $active ? 'text-[#FCC719]' : 'text-white/70 group-hover:text-white' }} flex shrink-0 transition-colors">
                        {{ $icon }}
                    </span>
                @endif
                <span class="whitespace-nowrap" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>{{ $slot }}</span>
            </div>
            
            <span class="flex shrink-0 transition-transform duration-300" :class="open ? 'rotate-90' : ''" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        </button>

        <!-- Nested Subitems Panel -->
        <div 
            class="grid transition-all duration-300 ease-in-out"
            :class="open && !sidebarMinimized ? 'grid-rows-[1fr] opacity-100 mt-1' : 'grid-rows-[0fr] opacity-0 mt-0'"
        >
            <div class="overflow-hidden">
                <div class="pl-11 pr-3 py-1 space-y-1">
                    {{ $subitems }}
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Standard Link Button -->
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-300 ' . ($active ? 'bg-white/10 text-white font-semibold shadow-sm' : 'text-white/70 hover:bg-white/5 hover:text-white font-medium')]) }}>
        <div class="flex items-center w-full overflow-hidden" :class="sidebarMinimized ? 'justify-center' : 'gap-3'">
            @if (isset($icon))
                <span class="{{ $active ? 'text-[#fcc719]' : 'text-white/70 group-hover:text-white' }} flex shrink-0 transition-colors">
                    {{ $icon }}
                </span>
            @endif
            <span class="whitespace-nowrap" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>{{ $slot }}</span>
        </div>
        @if (isset($trailing))
            <span class="flex shrink-0 opacity-50" x-show="!sidebarMinimized" x-transition.opacity.duration.300ms>
                {{ $trailing }}
            </span>
        @endif
    </a>
@endif