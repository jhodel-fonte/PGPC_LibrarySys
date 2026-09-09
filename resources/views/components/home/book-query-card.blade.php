@props([
    'book' => null,
    'isLoggedIn' => auth()->check(),
])

@php
    $isAlpine = is_null($book);
    $bladeDetailUrl = !$isAlpine ? route('opac.book.detail', (!empty($book['accession_no']) && $book['accession_no'] !== 'N/A') ? $book['accession_no'] : $book['id']) : '';
@endphp

<!-- Book Result Card Component -->
<article
    @if ($isAlpine)
        @click="window.location.href = '{{ route('opac.book.detail', '') }}/' + (book.accession_no && book.accession_no !== 'N/A' ? encodeURIComponent(book.accession_no) : book.id)"
    @else
        onclick="window.location.href = '{{ $bladeDetailUrl }}'"
    @endif
    class="group cursor-pointer rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs hover:border-[#102B70]/30 hover:shadow-md transition-all duration-200"
>
    <div class="flex flex-col sm:flex-row items-start gap-5">

        <!-- 1. Book Cover (110–125px wide × 150–175px high) with Smooth Lazy Loading -->
        <div
            x-data="{ imgLoaded: false, imgError: false }"
            class="w-[110px] sm:w-[120px] h-[155px] sm:h-[170px] shrink-0 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shadow-xs relative"
        >
            @if ($isAlpine)
                <!-- Alpine.js Dynamic Image -->
                <template x-if="book.cover">
                    <div class="h-full w-full relative">
                        <div
                            x-show="!imgLoaded && !imgError"
                            class="absolute inset-0 bg-slate-200/80 animate-pulse"
                        ></div>
                        <img
                            :src="book.cover"
                            :alt="book.title"
                            loading="lazy"
                            decoding="async"
                            x-on:load="imgLoaded = true"
                            x-on:error="imgError = true; imgLoaded = false"
                            x-show="!imgError"
                            :class="imgLoaded ? 'opacity-100' : 'opacity-0'"
                            class="h-full w-full object-cover transition-opacity duration-300"
                        >
                    </div>
                </template>
                <div
                    x-show="imgError || !book.cover"
                    class="h-full w-full flex items-center justify-center bg-[#EFF6FF] border border-[#DBEAFE]"
                >
                    <img
                        src="{{ asset('images/logo.webp') }}"
                        alt="No cover available"
                        class="h-20 w-20 object-contain opacity-30 select-none pointer-events-none"
                        draggable="false"
                    >
                </div>
            @else
                <!-- Blade Static Image -->
                @if (!empty($book['cover']))
                    <div
                        x-show="!imgLoaded && !imgError"
                        class="absolute inset-0 bg-slate-200/80 animate-pulse"
                    ></div>
                    <img
                        src="{{ $book['cover'] }}"
                        alt="{{ $book['title'] }}"
                        loading="lazy"
                        decoding="async"
                        x-on:load="imgLoaded = true"
                        x-on:error="imgError = true; imgLoaded = false"
                        x-show="!imgError"
                        :class="imgLoaded ? 'opacity-100' : 'opacity-0'"
                        class="h-full w-full object-cover transition-opacity duration-300"
                    >
                @endif
                <div
                    x-show="imgError || !'{{ $book['cover'] ?? '' }}'"
                    style="{{ !empty($book['cover']) ? 'display: none;' : '' }}"
                    class="h-full w-full flex items-center justify-center bg-[#EFF6FF] border border-[#DBEAFE]"
                >
                    <img
                        src="{{ asset('images/logo.webp') }}"
                        alt="No cover available"
                        class="h-20 w-20 object-contain opacity-30 select-none pointer-events-none"
                        draggable="false"
                    >
                </div>
            @endif
        </div>

        <!-- 2. Middle: Book Metadata & Information Hierarchy -->
        <div class="flex-1 min-w-0 pr-0 sm:pr-2">

            <!-- Level 1: Book Title -->
            <h3 class="text-[16px] sm:text-[17.5px] font-bold text-[#0B2454] group-hover:text-[#3B82F6] leading-snug tracking-tight transition-colors">
                @if ($isAlpine)
                    <a
                        :href="'{{ route('opac.book.detail', '') }}/' + (book.accession_no && book.accession_no !== 'N/A' ? encodeURIComponent(book.accession_no) : book.id)"
                        class="hover:underline"
                        x-text="book.title"
                    ></a>
                @else
                    <a
                        href="{{ $bladeDetailUrl }}"
                        class="hover:underline"
                    >
                        {{ $book['title'] }}
                    </a>
                @endif
            </h3>

            <!-- Level 2: Author -->
            <p class="mt-1 text-[13.5px] sm:text-[14px] font-medium text-slate-600">
                @if ($isAlpine)
                    <span x-text="book.author"></span>
                @else
                    {{ $book['author'] }}
                @endif
            </p>

            <!-- Level 3: Year · Format · Pages -->
            <div class="mt-3 flex flex-wrap items-center gap-3 text-[12.5px] sm:text-[13px] text-slate-500 font-medium">
                <span class="inline-flex items-center gap-1.5">
                    <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    @if ($isAlpine)
                        <span x-text="book.year"></span>
                    @else
                        <span>{{ $book['year'] }}</span>
                    @endif
                </span>

                <span class="text-slate-300">•</span>

                <span class="inline-flex items-center gap-1.5">
                    <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    @if ($isAlpine)
                        <span x-text="book.format"></span>
                    @else
                        <span>{{ $book['format'] }}</span>
                    @endif
                </span>

                <span class="text-slate-300">•</span>

                <span class="inline-flex items-center gap-1.5">
                    <svg width="14" height="14" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    @if ($isAlpine)
                        <span x-text="book.pages"></span>
                    @else
                        <span>{{ $book['pages'] }}</span>
                    @endif
                </span>
            </div>

            <!-- Level 4: Call Number & Location -->
            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1 text-[12.5px] sm:text-[13px]">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700 w-16 shrink-0">Call No.</span>
                    @if ($isAlpine)
                        <span class="font-semibold text-slate-800 font-mono text-[12px] sm:text-[12.5px]" x-text="book.call_no"></span>
                    @else
                        <span class="font-semibold text-slate-800 font-mono text-[12px] sm:text-[12.5px]">{{ $book['call_no'] }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700 w-16 shrink-0">Location</span>
                    @if ($isAlpine)
                        <span class="text-slate-600" x-text="book.location"></span>
                    @else
                        <span class="text-slate-600">{{ $book['location'] }}</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- 3. Right Side: Availability Badge & Actions -->
        <div class="w-full sm:w-auto flex sm:flex-col items-start sm:items-end justify-between sm:justify-start gap-4 shrink-0 sm:self-stretch pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">

            <!-- Availability Status -->
            <div class="text-left sm:text-right">
                @if ($isAlpine)
                    <div class="inline-flex items-center gap-1.5 font-bold text-[13px]" :class="book.status_color">
                        <span x-text="book.status_label"></span>
                    </div>

                    <template x-if="isLoggedIn">
                        <div>
                            <p x-show="book.due_date" x-text="book.due_date" class="text-[11.5px] text-rose-600 mt-0.5 font-semibold"></p>
                            <p x-show="book.pickup_date" x-text="book.pickup_date" class="text-[11.5px] text-amber-700 mt-0.5 font-semibold"></p>
                            <p x-text="'Accession No. ' + book.accession_no" class="text-[11px] text-slate-400 mt-0.5 font-mono"></p>
                        </div>
                    </template>
                @else
                    <div class="inline-flex items-center gap-1.5 font-bold text-[13px] {{ $book['status_color'] }}">
                        <span class="h-2 w-2 rounded-full {{ $book['dot_color'] }} shrink-0"></span>
                        <span>{{ $book['status_label'] }}</span>
                    </div>

                    @if ($isLoggedIn)
                        @if (!empty($book['due_date']))
                            <p class="text-[11.5px] text-rose-600 mt-0.5 font-semibold">
                                {{ $book['due_date'] }}
                            </p>
                        @elseif (!empty($book['pickup_date']))
                            <p class="text-[11.5px] text-amber-700 mt-0.5 font-semibold">
                                {{ $book['pickup_date'] }}
                            </p>
                        @endif

                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono">
                            Accession No. {{ $book['accession_no'] }}
                        </p>
                    @else
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Sign in for copy details
                        </p>
                    @endif
                @endif
            </div>

            <div class="flex items-center gap-2 mt-auto">
                @if ($isAlpine)
                    <a
                        :href="'{{ route('opac.book.detail', '') }}/' + (book.accession_no && book.accession_no !== 'N/A' ? encodeURIComponent(book.accession_no) : book.id)"
                        class="inline-flex items-center justify-center py-2 px-3.5 rounded-xl border border-slate-300 bg-white text-[13px] font-semibold text-[#0B2454] shadow-2xs group-hover:border-[#102B70] group-hover:bg-[#102B70] group-hover:text-white transition-all whitespace-nowrap"
                    >
                        View Details
                    </a>
                @else
                    <a
                        href="{{ $bladeDetailUrl }}"
                        class="inline-flex items-center justify-center py-2 px-3.5 rounded-xl border border-slate-300 bg-white text-[13px] font-semibold text-[#0B2454] shadow-2xs group-hover:border-[#102B70] group-hover:bg-[#102B70] group-hover:text-white transition-all whitespace-nowrap"
                    >
                        View Details
                    </a>
                @endif
            </div>

        </div>

    </div>
</article>

