@props([
    'name' => '',
    'logo' => null,
])

<a
    href="{{ route('online-resources.show', \Illuminate\Support\Str::slug($name)) }}"
    title="Learn about {{ $name }}"
    class="group relative flex h-[185px] w-full flex-col items-center justify-between rounded-2xl border border-white/10 bg-white p-5 shadow-md transition-all duration-200 hover:-translate-y-2 hover:border-[#FCC719]/50 hover:shadow-2xl hover:shadow-black/50 focus:outline-none focus:ring-2 focus:ring-[#FCC719]/40 sm:h-[200px] sm:p-6 lg:h-[210px]"
>
    <div class="absolute left-6 right-6 top-0 h-[3.5px] rounded-b-full bg-[#FCC719] opacity-0 transition-opacity duration-200 group-hover:opacity-100"></div>
    <span class="absolute right-3.5 top-3.5 text-slate-400 opacity-0 transition-opacity duration-200 group-hover:opacity-100" aria-hidden="true">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01" />
        </svg>
    </span>

    <div class="flex w-full flex-1 items-center justify-center py-2">
        @if ($logo)
            <img src="{{ $logo }}" alt="{{ $name }}" loading="lazy" decoding="async" class="h-auto w-auto max-w-[145px] object-contain transition-transform duration-200 group-hover:scale-105 sm:max-h-[66px] sm:max-w-[160px]" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
            <div style="display: none;" class="h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-[#091b45]" aria-hidden="true">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.426-4.5 1.253" /></svg>
            </div>
        @else
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-[#091b45]" aria-hidden="true">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.426 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.426-4.5 1.253" /></svg>
            </div>
        @endif
    </div>

    <div class="w-full border-t border-slate-100 pt-3.5 text-center">
        <span class="block truncate px-1 text-[14.5px] font-bold tracking-tight text-[#091b45] transition-colors duration-150 group-hover:text-[#102B70] sm:text-[15.5px]">{{ $name }}</span>
        <span class="mt-1 block text-xs font-medium text-slate-500">Learn about this resource</span>
    </div>
</a>
