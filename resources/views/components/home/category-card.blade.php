@props([
    'title',
    'description',
    'action' => 'Explore',
    'url' => '#',
    'icon' => null,
])

<a
    href="{{ $url }}"
    class="group relative flex h-full min-h-[200px] sm:min-h-[220px] lg:min-h-[235px] flex-col justify-between rounded-2xl border border-white/10 bg-[#102B70] p-6 sm:p-7 lg:p-8 shadow-md transition-all duration-200 hover:-translate-y-1.5 hover:bg-[#0B225E] hover:border-[#FCC719]/40 hover:shadow-2xl hover:shadow-[#102B70]/35 focus:outline-none focus:ring-2 focus:ring-[#FCC719]/40 select-none"
>
    <!-- Subtle Gold Top Accent Bar (Activates on hover) -->
    <div class="absolute top-0 left-6 right-6 h-[3.5px] rounded-b-full bg-[#FCC719] opacity-0 transition-opacity duration-200 group-hover:opacity-100"></div>

    <!-- Top Card Content -->
    <div>
        @if ($icon || !$slot->isEmpty())
            <!-- Icon Container (renders only if icon is provided) -->
            <div class="flex h-[52px] w-[52px] sm:h-[58px] sm:w-[58px] items-center justify-center rounded-2xl bg-white/10 border border-white/15 text-[#FCC719] shadow-xs transition-all duration-200 group-hover:bg-[#FCC719] group-hover:border-[#FCC719] group-hover:text-[#102B70] mb-4.5">
                {!! $icon ?? $slot !!}
            </div>
        @endif

        <!-- Title (Larger & Bold) -->
        <h3 class="text-[21px] sm:text-[23px] lg:text-[24px] font-extrabold tracking-tight text-white transition-colors duration-150 group-hover:text-[#FCC719]">
            {{ $title }}
        </h3>

        <!-- Description (Larger text) -->
        <p class="mt-2.5 text-[15px] sm:text-[16px] leading-relaxed text-blue-100/80 font-normal">
            {{ $description }}
        </p>
    </div>

    <!-- Bottom Action Row (Constrained Arrow & Non-wrapping Text) -->
    <div class="mt-6 flex items-center justify-between gap-3 border-t border-white/10 pt-4 text-[14.5px] sm:text-[15px] font-bold text-[#FCC719] transition-colors">
        <span class="whitespace-nowrap transition-colors group-hover:text-white group-hover:underline">
            {{ $action }}
        </span>
        <svg class="h-5 w-5 shrink-0 text-[#FCC719] transition-all duration-200 group-hover:translate-x-1.5 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
    </div>
</a>
