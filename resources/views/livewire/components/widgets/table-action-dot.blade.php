@props([
    'align' => 'right',
    'width' => 'w-44'
])

<div class="relative inline-block text-left" x-data="{ open: false }">
    <button
        @click="open = !open"
        type="button"
        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none"
        aria-label="More actions"
    >
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute {{ $align === 'left' ? 'left-0' : 'right-0' }} z-30 mt-1 {{ $width }} rounded-xl border border-[#E2E8F0] bg-white p-1.5 text-left shadow-xl focus:outline-none"
    >
        {{ $slot ?? '' }}
    </div>
</div>
