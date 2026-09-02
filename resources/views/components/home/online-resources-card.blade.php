@props([
    'name' => '',
    'url' => '#',
    'logo' => null,
])

<a
    href="{{ $url }}"
    target="_blank"
    rel="noopener noreferrer"
    title="{{ $name }}"
    class="group relative flex h-[185px] sm:h-[200px] lg:h-[210px] w-full flex-col items-center justify-between rounded-2xl border border-white/10 bg-white p-5 sm:p-6 shadow-md transition-all duration-200 hover:-translate-y-2 hover:border-[#FCC719]/50 hover:shadow-2xl hover:shadow-black/50 focus:outline-none focus:ring-2 focus:ring-[#FCC719]/40 select-none"
>
    <!-- Subtle Gold Top Accent Bar (Activates on hover) -->
    <div class="absolute top-0 left-6 right-6 h-[3.5px] rounded-b-full bg-[#FCC719] opacity-0 transition-opacity duration-200 group-hover:opacity-100"></div>

    <!-- External Link Arrow Indicator (Appears on hover) -->
    <div class="absolute top-3.5 right-3.5 opacity-0 transition-opacity duration-200 group-hover:opacity-100 text-slate-400 group-hover:text-[#091b45]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
        </svg>
    </div>

    <!-- Center: Journal / Database Logo Container -->
    <div class="flex flex-1 w-full items-center justify-center py-2">
        @if ($logo)
            <img
                src="{{ $logo }}"
                alt="{{ $name }}"
                loading="lazy"
                decoding="async"
                class="max-h-[58px] sm:max-h-[66px] max-w-[145px] sm:max-w-[160px] w-auto h-auto object-contain transition-transform duration-200 group-hover:scale-105"
                onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
            >
            <!-- Fallback Icon if image fails to load -->
            <div style="display: none;" class="h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-[#091b45]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        @else
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-[#091b45]">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        @endif
    </div>

    <!-- Bottom: Name under the Logo in #091b45 Blue Text -->
    <div class="w-full pt-3.5 border-t border-slate-100 text-center">
        <span class="block text-[14.5px] sm:text-[15.5px] font-bold text-[#091b45] tracking-tight transition-colors duration-150 group-hover:text-[#102B70] truncate px-1">
            {{ $name }}
        </span>
    </div>
</a>
