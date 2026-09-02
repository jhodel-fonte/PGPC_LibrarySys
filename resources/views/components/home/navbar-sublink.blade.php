@props([
    'title' => '',
    'url' => '#',
    'active' => false,
    'items' => [],
    'align' => 'left',
])

@php
    $alignmentClasses = match ($align) {
        'center' => 'left-1/2 -translate-x-1/2',
        'right' => 'right-0',
        default => 'left-0',
    };
@endphp

<div
    x-data="{
        open: false,
        timer: null,
        show() {
            clearTimeout(this.timer);
            this.open = true;
        },
        hide() {
            this.timer = setTimeout(() => {
                this.open = false;
            }, 180);
        }
    }"
    @mouseenter="show()"
    @mouseleave="hide()"
    @keydown.escape.window="open = false"
    class="relative group"
>
    <!-- Main Top Link / Trigger Button -->
    <a
        href="{{ $url }}"
        class="relative flex items-center gap-1.5 py-2 text-[16px] whitespace-nowrap transition-colors focus:outline-none {{ $active ? 'text-white font-semibold' : 'text-slate-200/90 hover:text-white font-medium' }}"
        :class="{ 'text-white': open }"
    >
        <span>{{ $title }}</span>

        <!-- Animated Caret Icon -->
        <svg
            class="h-3.5 w-3.5 transition-transform duration-200"
            :class="open ? 'rotate-180 text-[#FCC719]' : 'text-slate-400 group-hover:text-white'"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2.2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>

        @if ($active)
            <!-- Gold Active Indicator Bar -->
            <span class="absolute -bottom-1.5 left-0 right-0 h-[3px] rounded-full bg-[#FCC719]"></span>
        @else
            <!-- Hover Indicator Bar -->
            <span
                class="absolute -bottom-1.5 left-1/2 h-[3px] w-0 -translate-x-1/2 rounded-full bg-[#FCC719] transition-all duration-200 group-hover:w-full"
                :class="{ 'w-full': open }"
            ></span>
        @endif
    </a>

    <!-- Invisible Hover Bridge (prevents mouse pointer loss across the gap) -->
    <div
        x-show="open"
        class="absolute left-0 right-0 h-3 top-full"
        style="display: none;"
    ></div>

    <!-- Dropdown Panel (Solid #091b45 Opaque Card) -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        @click.outside="open = false"
        style="display: none; background-color: #091b45;"
        class="absolute top-[calc(100%+6px)] {{ $alignmentClasses }} z-50 min-w-[280px] max-w-sm rounded-2xl border border-white/20 bg-[#091b45] p-2.5 shadow-2xl shadow-black/50 select-none"
    >

        @if (!empty($items))
            <div class="space-y-1">
                @foreach ($items as $item)
                    @php
                        $itemUrl = $item['url'] ?? '#';
                        $itemName = $item['name'] ?? '';
                        $itemDesc = $item['desc'] ?? null;
                        $itemBadge = $item['badge'] ?? null;
                        $isExternal = str_starts_with($itemUrl, 'http://') || str_starts_with($itemUrl, 'https://');
                    @endphp
                    <a
                        href="{{ $itemUrl }}"
                        @if ($isExternal) target="_blank" rel="noopener noreferrer" @endif
                        class="group/item flex items-start justify-between gap-3 rounded-xl p-2.5 transition-all duration-150 hover:bg-white/10"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2 text-[13.5px] font-semibold text-white transition-colors group-hover/item:text-[#FCC719]">
                                <span>{{ $itemName }}</span>
                                @if ($itemBadge)
                                    <span class="rounded-full bg-[#FCC719]/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#FCC719]">
                                        {{ $itemBadge }}
                                    </span>
                                @endif
                            </div>
                            @if ($itemDesc)
                                <div class="mt-0.5 text-[11.5px] leading-snug text-slate-300/90 font-normal">
                                    {{ $itemDesc }}
                                </div>
                            @endif
                        </div>

                        <!-- Right Chevron Indicator -->
                        <svg class="mt-1 h-3.5 w-3.5 shrink-0 text-white/30 transition-transform duration-150 group-hover/item:translate-x-0.5 group-hover/item:text-[#FCC719]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endforeach
            </div>
        @else
            <!-- Custom Slot Content if passed directly -->
            <div class="space-y-1">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
