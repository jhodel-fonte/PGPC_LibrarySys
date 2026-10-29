<div wire:key="book-detail-page-{{ $book['id'] ?? 'public' }}" class="min-h-full bg-[#F8FAFC] py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-[1380px] mx-auto w-full select-none">

        <!-- Top Navigation: Back Button -->
        <div class="mb-5 flex items-center justify-between">
            <a
                href="{{ (url()->previous() !== url()->current() && str_contains(url()->previous(), request()->getHost())) ? url()->previous() : route('opac.index') }}"
                class="inline-flex items-center gap-2 text-[14px] font-semibold text-[#102B70] hover:text-[#0B225E] transition-colors group"
            >
                <svg width="18" height="18" class="h-4.5 w-4.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Results</span>
            </a>

            @php
                $canEditBook = \App\Services\EmployeeRoleService::canEditBook();
                $editPrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
            @endphp
            @if($canEditBook && !empty($book['id']))
                <a
                    href="{{ route($editPrefix . 'book-management.edit', $book['id']) }}"
                    wire:navigate
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#102B70] text-white text-xs font-bold hover:bg-[#0B225E] transition-colors shadow-xs"
                >
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Edit Book</span>
                </a>
            @endif
        </div>

        @if(!$readyToLoad)
            <!-- FULL PAGE SKELETON LOADER -->
            <div class="animate-pulse space-y-8">
                <!-- 1. Top Card Skeleton -->
                <div class="rounded-2xl border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.05)]">
                    <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-10">
                        <!-- Cover Skeleton -->
                        <div class="w-[185px] sm:w-[205px] h-[270px] sm:h-[300px] shrink-0 rounded-xl bg-slate-200 flex items-center justify-center mx-auto lg:mx-0 shadow-sm">
                            <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Center Info Skeleton -->
                        <div class="flex-1 min-w-0 space-y-3.5 w-full">
                            <div class="h-8 w-3/4 bg-slate-200 rounded"></div>
                            <div class="h-4 w-1/2 bg-slate-200 rounded"></div>
                            <div class="h-5 w-44 bg-slate-200 rounded mt-2"></div>
                            <div class="h-4 w-60 bg-slate-200 rounded mt-3"></div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5 mt-5 border-t border-slate-100">
                                <div class="h-10 bg-slate-100 rounded-lg"></div>
                                <div class="h-10 bg-slate-100 rounded-lg"></div>
                                <div class="h-10 bg-slate-100 rounded-lg"></div>
                            </div>

                            <div class="pt-4 mt-4 border-t border-slate-100">
                                <div class="h-4 w-48 bg-slate-200 rounded"></div>
                            </div>
                        </div>

                        <!-- Right Actions Skeleton -->
                        <div class="w-full lg:w-[260px] xl:w-[280px] shrink-0 flex flex-col gap-3.5 border-t lg:border-t-0 lg:border-l border-slate-100 pt-6 lg:pt-0 lg:pl-8">
                            <div class="h-5 w-28 bg-slate-200 rounded"></div>
                            <div class="h-6 w-32 bg-slate-200 rounded"></div>
                            <div class="h-16 bg-slate-100 rounded-xl border border-slate-200"></div>
                            <div class="h-11 bg-slate-200 rounded-xl"></div>
                            <div class="h-10 bg-slate-100 rounded-xl border border-slate-200"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Bottom Card Skeleton -->
                <div class="rounded-2xl border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.05)]">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                        <div class="lg:col-span-6 space-y-4">
                            <div class="h-8 w-44 bg-slate-200 rounded"></div>
                            <div class="h-5 w-36 bg-slate-200 rounded mt-4"></div>
                            <div class="space-y-2">
                                <div class="h-3.5 w-full bg-slate-200 rounded"></div>
                                <div class="h-3.5 w-5/6 bg-slate-200 rounded"></div>
                                <div class="h-3.5 w-4/6 bg-slate-200 rounded"></div>
                            </div>
                            <div class="flex gap-2 pt-4">
                                <div class="h-6 w-16 bg-slate-200 rounded-full"></div>
                                <div class="h-6 w-20 bg-slate-200 rounded-full"></div>
                                <div class="h-6 w-24 bg-slate-200 rounded-full"></div>
                            </div>
                        </div>

                        <div class="lg:col-span-6 space-y-3">
                            <div class="h-6 w-48 bg-slate-200 rounded mb-3"></div>
                            <div class="rounded-xl border border-slate-200 overflow-hidden">
                                <div class="h-10 bg-slate-100 border-b border-slate-200"></div>
                                <div class="p-3 space-y-3">
                                    <div class="h-6 bg-slate-100 rounded"></div>
                                    <div class="h-6 bg-slate-100 rounded"></div>
                                    <div class="h-6 bg-slate-100 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($book)
            <!-- 1. Top Card: Book Hero & Summary Card -->
            <div class="rounded-2xl border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.05)] mb-8">
                <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-10">

                    <!-- 1.1 Left: Book Cover -->
                    <div
                        wire:key="book-cover-{{ $book['id'] ?? 'public' }}"
                        x-data="{
                            imgLoaded: false,
                            imgError: false,
                            fallbackError: false,
                            lightboxOpen: false,
                            coverSrc: @js($book['cover_url'] ?? null),
                            fallbackCover: @js($book['default_cover'] ?? asset('images/' . config('settings.book_covers.default', config('settings.coverFile_default', 'book-cover.webp'))))
                        }"
                        x-init="
                            if (!coverSrc) {
                                imgError = true;
                                imgLoaded = true;
                            } else {
                                $nextTick(() => {
                                    const img = $refs.mainCoverImg;
                                    if (img && img.complete) {
                                        if (img.naturalHeight === 0) {
                                            imgError = true;
                                        } else {
                                            imgLoaded = true;
                                        }
                                    }
                                });
                            }
                        "
                        class="group w-[185px] sm:w-[205px] h-[270px] sm:h-[300px] shrink-0 rounded-xl overflow-hidden shadow-[0_8px_24px_rgba(7,26,61,0.14)] border border-slate-200 bg-slate-100 relative mx-auto lg:mx-0 select-none"
                    >
                        <!-- Loading Shimmer State (Visible while image is loading) -->
                        <div
                            x-show="!imgLoaded && !imgError && Boolean(coverSrc)"
                            class="absolute inset-0 bg-slate-200/90 animate-pulse flex flex-col items-center justify-center text-slate-400 z-10"
                        >
                            <svg class="h-10 w-10 text-slate-300 animate-pulse mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-[11px] font-semibold text-slate-400 tracking-wide">Loading cover...</span>
                        </div>

                        <!-- Primary Book Cover Image -->
                        @if(!empty($book['cover_url']))
                            <img
                                x-ref="mainCoverImg"
                                :src="coverSrc"
                                alt="{{ $book['title'] ?? 'Book Cover' }}"
                                loading="eager"
                                decoding="async"
                                x-on:load="imgLoaded = true; imgError = false;"
                                x-on:error="imgError = true; imgLoaded = true;"
                                x-show="!imgError"
                                :class="imgLoaded ? 'opacity-100' : 'opacity-0'"
                                class="w-full h-full object-cover select-none group-hover:scale-105 transition-all duration-300"
                            >
                        @endif

                        <!-- Fallback State: Shown when coverSrc is missing OR primary image failed to load -->
                        <div
                            x-show="imgError || !coverSrc"
                            x-cloak
                            class="absolute inset-0 h-full w-full bg-slate-100 flex items-center justify-center overflow-hidden"
                        >
                            <img
                                :src="fallbackCover"
                                alt="{{ $book['title'] ?? 'Default Cover' }}"
                                x-on:error="fallbackError = true"
                                x-show="!fallbackError"
                                class="w-full h-full object-cover select-none pointer-events-none group-hover:scale-105 transition-transform duration-300"
                                draggable="false"
                            >

                            <!-- Ultimate SVG Fallback: In case the default-cover webp file itself is missing or cannot load -->
                            <div
                                x-show="fallbackError"
                                class="h-full w-full flex flex-col items-center justify-center p-4 bg-gradient-to-b from-[#EFF6FF] to-slate-100 text-center"
                            >
                                <div class="w-14 h-14 rounded-2xl bg-[#102B70]/10 flex items-center justify-center text-[#102B70] mb-2 shadow-xs">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="text-[12px] font-bold text-[#0B2454] line-clamp-2 leading-tight">
                                    {{ $book['title'] ?? 'PGPC Library' }}
                                </p>
                                <span class="text-[10px] text-[#64748B] mt-1 font-semibold uppercase tracking-wider">No Cover Image</span>
                            </div>
                        </div>

                        <!-- Hover Action: Zoom / Preview Cover Lightbox Trigger -->
                        <button
                            type="button"
                            @click="lightboxOpen = true"
                            class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-black/60 hover:bg-black/80 text-white text-[11px] font-semibold backdrop-blur-xs shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer"
                            title="View Full Cover"
                            aria-label="View Full Cover"
                        >
                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                            </svg>
                            <span>Zoom</span>
                        </button>

                        <!-- Cover Lightbox Modal -->
                        <template x-teleport="body">
                            <div
                                x-show="lightboxOpen"
                                x-cloak
                                @keydown.escape.window="lightboxOpen = false"
                                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                                aria-modal="true"
                                role="dialog"
                            >
                                <div
                                    @click="lightboxOpen = false"
                                    x-show="lightboxOpen"
                                    x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-150"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs"
                                ></div>

                                <div
                                    x-show="lightboxOpen"
                                    x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="relative z-10 max-w-lg w-full max-h-[90vh] flex flex-col items-center"
                                >
                                    <div class="relative bg-white rounded-2xl p-2.5 shadow-2xl border border-white/20 max-h-[85vh] overflow-hidden flex flex-col items-center">
                                        <button
                                            type="button"
                                            @click="lightboxOpen = false"
                                            class="absolute top-4 right-4 z-20 w-8 h-8 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center transition shadow-md cursor-pointer"
                                            title="Close"
                                            aria-label="Close"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <img
                                            :src="!imgError && coverSrc ? coverSrc : fallbackCover"
                                            alt="{{ $book['title'] ?? 'Book Cover' }}"
                                            class="max-h-[75vh] w-auto object-contain rounded-xl select-none"
                                        >
                                        <p class="mt-2 text-xs font-semibold text-[#0B2454] text-center px-4 line-clamp-1">
                                            {{ $book['title'] ?? 'Book Cover' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- 1.2 Center: Book Metadata & Identifiers -->
                    <div class="flex-1 min-w-0">
                        <!-- Title & Subtitle -->
                        <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#0B2454] leading-tight mt-2.5 tracking-tight">
                            {{ $book['title'] ?? 'Untitled Book' }}
                        </h1>
                        @if(!empty($book['subtitle']))
                            <p class="text-[15px] sm:text-[16px] text-[#64748B] mt-1 font-normal">
                                {{ $book['subtitle'] }}
                            </p>
                        @endif

                        <!-- Author with User Icon -->
                        <div class="mt-2.5 flex items-center gap-2">
                            <svg width="18" height="18" class="h-4.5 w-4.5 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-[15.5px] font-semibold text-[#102B70]">
                                {{ $book['author'] ?? 'Unknown Author' }}
                            </span>
                        </div>

                        <!-- Horizontal Metadata Row with Bullets -->
                        <div class="flex items-center flex-wrap gap-x-2.5 gap-y-1.5 mt-3.5 text-[14.5px] sm:text-[15px] text-[#475569] font-medium">
                            <!-- Year -->
                            <div class="flex items-center gap-1.5">
                                <svg width="16" height="16" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ $book['year'] ?? 'N/A' }}</span>
                            </div>
                            <span class="text-slate-300 font-bold">•</span>

                            <!-- Resource Type -->
                            <div class="flex items-center gap-1.5">
                                <svg width="16" height="16" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>{{ $book['type'] ?? 'Book' }}</span>
                            </div>
                            <span class="text-slate-300 font-bold">•</span>

                            <!-- Pages -->
                            <div class="flex items-center gap-1.5">
                                <svg width="16" height="16" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>{{ $book['pages'] ?? 'N/A' }}</span>
                            </div>
                            <span class="text-slate-300 font-bold">•</span>

                            <!-- Language -->
                            <div class="flex items-center gap-1.5">
                                <svg width="16" height="16" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span>{{ $book['language'] ?? 'English' }}</span>
                            </div>
                        </div>

                        <!-- 3 Identifiers: Call No, Accession No, ISBN -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5 mt-5 border-t border-slate-100">
                            <!-- Call No. -->
                            <div>
                                <span class="block text-[13px] font-semibold text-[#64748B]">Call No.</span>
                                <span class="block text-[15.5px] font-bold text-[#0B2454] mt-0.5">{{ $book['call_no'] ?? 'N/A' }}</span>
                            </div>

                            <!-- Accession No. -->
                            <div>
                                <span class="block text-[13px] font-semibold text-[#64748B]">Accession No.</span>
                                <span class="block text-[15.5px] font-bold text-[#0B2454] mt-0.5">{{ $book['accession_no'] ?? 'N/A' }}</span>
                            </div>

                            <!-- ISBN -->
                            <div>
                                <span class="block text-[13px] font-semibold text-[#64748B]">ISBN</span>
                                <span class="block text-[15.5px] font-bold text-[#0B2454] mt-0.5">{{ $book['isbn'] ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Subjects -->
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <span class="block text-[13.5px] font-bold text-[#334155] mb-1">Subjects</span>
                            <div class="text-[14.5px] text-[#102B70] font-medium leading-relaxed">
                                @if(!empty($book['subjects']))
                                    @foreach($book['subjects'] as $index => $sub)
                                        <a href="{{ route('opac.index', ['subject' => $sub]) }}" class="hover:underline cursor-pointer">
                                            {{ $sub }}
                                        </a>{{ !$loop->last ? ' | ' : '' }}
                                    @endforeach
                                @else
                                    <span class="text-slate-500">General Collection</span>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- 1.3 Right: Availability Status & Actions Panel -->
                    <div class="w-full lg:w-[260px] xl:w-[280px] shrink-0 flex flex-col gap-3.5 border-t lg:border-t-0 lg:border-l border-slate-100 pt-6 lg:pt-0 lg:pl-8">

                        <!-- Header: Availability Status -->
                        <div>
                            <span class="block text-[13.5px] font-semibold text-[#64748B]">
                                Availability Status
                            </span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[18px] font-bold {{ $book['status_color'] ?? 'text-emerald-700' }}">
                                    {{ $book['status_label'] ?? 'Available' }}
                                </span>
                            </div>
                        </div>

                        <!-- Notice Banner -->
                        @if(($book['status'] ?? '') === 'available')
                            <div class="rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-3 flex items-start gap-2.5">

                                <div>
                                    <p class="text-[13px] font-bold text-emerald-950 leading-tight">
                                        This item is available for borrowing.
                                    </p>
                                </div>
                            </div>
                        @elseif(($book['status'] ?? '') === 'checked_out')
                            <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-3 flex items-start gap-2.5">
                                <div>
                                    <p class="text-[13px] font-bold text-rose-950 leading-tight">
                                        All copies currently checked out.
                                    </p>
                                    <p class="text-[12.5px] text-rose-700 leading-tight mt-0.5">
                                        Check back after scheduled return dates.
                                    </p>
                                </div>
                            </div>
                        @elseif(($book['status'] ?? '') === 'reserved')
                            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-3 flex items-start gap-2.5">
                                <div>
                                    <p class="text-[13px] font-bold text-amber-950 leading-tight">
                                        Reserved by another borrower.
                                    </p>
                                    <p class="text-[12.5px] text-amber-700 leading-tight mt-0.5">
                                        Awaiting collection at circulation desk.
                                    </p>
                                </div>
                            </div>
                        @elseif(($book['status'] ?? '') === 'reference_only')
                            <div class="rounded-xl border border-blue-200 bg-blue-50/80 p-3 flex items-start gap-2.5">
                                <div>
                                    <p class="text-[13px] font-bold text-blue-950 leading-tight">
                                        Reference copy only.
                                    </p>
                                    <p class="text-[12.5px] text-blue-700 leading-tight mt-0.5">
                                        In-library reading only; not available for loan.
                                    </p>
                                </div>
                            </div>
                        @endif
                        
                        

                        <a href="{{ route('student.reservations', ['search' => $book['title'] ?? '']) }}"
                           class="flex h-[42px] w-full items-center justify-center rounded-xl bg-[#102B70] px-4 text-sm font-semibold text-white hover:bg-[#0B225E] focus:outline-none focus:ring-2 focus:ring-[#102B70] focus:ring-offset-2">
                            {{ ($book['available_copies'] ?? 0) > 0 ? 'Reserve this book' : 'Join waitlist' }}
                        </a>

                        <!-- Button 3: Share (Copies link to clipboard via Alpine) -->
                        <div x-data="{ copied: false }" class="w-full">
                            <button
                                type="button"
                                @click="
                                    navigator.clipboard.writeText(window.location.href);
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="w-full h-[42px] rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-[#102B70] font-semibold text-[14px] flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-[0.99] cursor-pointer"
                            >
                                <template x-if="!copied">
                                    <svg width="17" height="17" class="h-4 w-4 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                </template>
                                <template x-if="copied">
                                    <svg width="17" height="17" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <span :class="copied ? 'text-emerald-700 font-bold' : ''" x-text="copied ? 'Link Copied to Clipboard!' : 'Share'">Share</span>
                            </button>
                        </div>

                    </div>

                </div>
            </div>

            <!-- 2. Bottom Card: Tabs & Availability Table Card -->
            <div class="rounded-2xl border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.05)]">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                    <!-- 2.1 Left: Tabbed Information (Overview, Details) -->
                    <div class="lg:col-span-6">

                        <!-- Tabs Navigation -->
                        <div class="flex items-center gap-6 border-b border-[#E2E8F0] pb-0 text-[15px]">
                            <button
                                type="button"
                                wire:click="setTab('overview')"
                                class="pb-2.5 font-bold transition-all border-b-2 cursor-pointer {{ $activeTab === 'overview' ? 'border-[#FCC719] text-[#0B2454]' : 'border-transparent text-[#64748B] hover:text-[#0B2454]' }}"
                            >
                                Overview
                            </button>
                            <button
                                type="button"
                                wire:click="setTab('details')"
                                class="pb-2.5 font-bold transition-all border-b-2 cursor-pointer {{ $activeTab === 'details' ? 'border-[#FCC719] text-[#0B2454]' : 'border-transparent text-[#64748B] hover:text-[#0B2454]' }}"
                            >
                                Details
                            </button>
                        </div>

                        <!-- Tab 1: Overview -->
                        @if($activeTab === 'overview')
                            <div class="pt-5">
                                <!-- About this Book Subheading -->
                                <div class="flex items-center gap-2 text-[#0B2454] font-bold text-[16px] mb-3">
                                    <svg width="18" height="18" class="h-4.5 w-4.5 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>About this Book</span>
                                </div>

                                <!-- Description -->
                                <p class="text-[14.5px] text-[#475569] leading-relaxed font-normal">
                                    {{ $book['description'] ?? 'No detailed description cataloged for this resource.' }}
                                </p>

                                <!-- Keywords -->
                                <div class="mt-6">
                                    <h4 class="text-[13.5px] font-bold text-[#0B2454] mb-2.5">
                                        Keywords
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        @if(!empty($book['keywords']))
                                            @foreach($book['keywords'] as $keyword)
                                                <a
                                                    href="{{ route('opac.index', ['search' => $keyword]) }}"
                                                    class="inline-block px-3 py-1 rounded-full text-[12.5px] font-medium bg-[#F1F5F9] text-[#334155] hover:bg-[#E2E8F0] hover:text-[#102B70] transition cursor-pointer"
                                                >
                                                    {{ $keyword }}
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-slate-400">None</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        <!-- Tab 2: Details -->
                        @elseif($activeTab === 'details')
                            <div class="pt-5 space-y-3 text-[14px]">
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Format</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['format'] ?? 'Book' }}</span>
                                </div>
                                @if(!empty($book['publisher']) && $book['publisher'] !== 'N/A')
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Publisher</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['publisher'] }}</span>
                                </div>
                                @endif
                                @if(!empty($book['edition']) && $book['edition'] !== 'N/A')
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Edition</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['edition'] }}</span>
                                </div>
                                @endif
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Language</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['language'] ?? 'English' }}</span>
                                </div>
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Publication Year</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['year'] ?? 'N/A' }}</span>
                                </div>
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Pagination</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['pages'] ?? 'N/A' }}</span>
                                </div>
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">ISBN</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['isbn'] ?? 'N/A' }}</span>
                                </div>
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Call Number</span>
                                    <span class="col-span-2 text-[#0B2454] font-mono text-[13px] font-bold">{{ $book['call_no'] ?? 'N/A' }}</span>
                                </div>
                                @if(!empty($book['classification']) && $book['classification'] !== 'N/A')
                                <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                                    <span class="font-semibold text-slate-500">Classification</span>
                                    <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['classification'] }}</span>
                                </div>
                                @endif
                            </div>
                        @endif

                    </div>

                    <!-- 2.2 Right: Availability at PGPC Library Table -->
                    <div class="lg:col-span-6">

                        <!-- Table Header Bar -->
                        <div class="flex items-center justify-between mb-3.5">
                            <h3 class="text-base font-bold text-[#0B2454]">
                                Availability at PGPC Library
                            </h3>
                            <span class="text-sm font-semibold text-[#64748B]">
                                Total Copies: {{ $book['total_copies'] ?? 0 }} <span class="text-emerald-700">({{ $book['available_copies'] ?? 0 }} Available)</span>
                            </span>
                        </div>

                        <!-- Table Container with Vertical & Horizontal Scroll -->
                        <div class="overflow-x-auto overflow-y-auto max-h-[380px] rounded-xl border border-[#E2E8F0] shadow-2xs">
                            <table class="w-full text-left border-collapse">
                                <thead class="sticky top-0 z-10 bg-[#F8FAFC] shadow-2xs">
                                    <tr class="border-b border-[#E2E8F0]">
                                        <th class="py-3 px-3.5 text-xs font-bold text-[#475569] uppercase tracking-wider">
                                            Accession No.
                                        </th>
                                        <th class="py-3 px-3.5 text-xs font-bold text-[#475569] uppercase tracking-wider">
                                            Location
                                        </th>
                                        <th class="py-3 px-3.5 text-xs font-bold text-[#475569] uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="py-3 px-3.5 text-xs font-bold text-[#475569] uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th class="py-3 px-3.5 text-xs font-bold text-[#475569] uppercase tracking-wider text-right">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E2E8F0] text-base">
                                    @if(!empty($book['holdings']))
                                        @forelse($book['holdings'] as $copy)
                                            <tr class="hover:bg-slate-50/70 transition-colors">
                                                <!-- Accession No. -->
                                                <td class="py-3.5 px-3.5 font-mono text-base font-bold text-[#0B2454] whitespace-nowrap">
                                                    {{ $copy['accession_no'] ?? 'N/A' }}
                                                </td>

                                                <!-- Location -->
                                                <td class="py-3.5 px-3.5 font-medium text-base text-[#1E293B] whitespace-nowrap">
                                                    {{ $copy['location'] ?? 'Main Library' }}
                                                </td>

                                                <!-- Status with colored label -->
                                                <td class="py-3.5 px-3.5 whitespace-nowrap">
                                                    <div class="inline-flex items-center gap-1.5">
                                                        <span class="font-semibold text-base {{ $copy['status_color'] ?? 'text-emerald-700' }}">
                                                            {{ $copy['status_label'] ?? 'Available' }}
                                                        </span>
                                                    </div>
                                                </td>

                                                <!-- Due Date -->
                                                <td class="py-3.5 px-3.5 whitespace-nowrap text-base {{ ($copy['status'] ?? '') === 'reserved' ? 'text-amber-700 font-medium' : 'text-[#64748B]' }}">
                                                    {{ $copy['due_date'] ?? '—' }}
                                                </td>

                                                <!-- Action: Show QR Code -->
                                                <td class="py-3.5 px-3.5 text-right whitespace-nowrap">
                                                    <button
                                                        type="button"
                                                        @click="$dispatch('open-qr-modal', {
                                                            value: @js($copy['accession_no'] ?? ''),
                                                            title: 'Physical Copy QR Code',
                                                            subtitle: 'Accession / Barcode QR',
                                                            name: @js($book['title'] ?? 'Book Copy'),
                                                            label: 'Accession No.:',
                                                            secondaryInfo: @js('Code: ' . ($copy['code'] ?? 'N/A') . ($copy['location'] ? ' • Shelf: ' . $copy['location'] : '')),
                                                            instruction: 'Present this QR code during checkout, return, or inventory audit.'
                                                        })"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition-colors hover:border-[#102B70] hover:bg-[#F5F9FF] hover:text-[#102B70] active:translate-y-px focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#102B70] cursor-pointer"
                                                        title="Show QR Code"
                                                        aria-label="Show QR Code for copy {{ $copy['accession_no'] ?? '' }}"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-6 text-center text-slate-400 text-sm">
                                                    No individual holding copies cataloged.
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-slate-400 text-sm">
                                                No copies cataloged.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer text -->
                        <p class="text-xs text-[#94A3B8] mt-2.5">
                            Availability updates in real-time.
                        </p>

                    </div>

                </div>
            </div>

            <!-- 3. Recommendations / Similar Books Row -->
            <div class="mt-8">
                @if(auth()->check() && !empty($personalRecommendations))
                    <x-recommendation.book-view-recommendation
                        title="Picked for you"
                        subtitle="Recommendations based on your library activity"
                        :books="$personalRecommendations"
                        :limit="10"
                        class="border-[#DBEAFE] bg-[#F8FBFF]"
                    />
                @elseif(auth()->check() && !empty($recommendationError))
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                        {{ $recommendationError }}
                    </div>
                @else
                <x-recommendation.book-view-recommendation
                    :category="$book['category'] ?? ($book['subjects'][0] ?? null)"
                    :books="$similarBooks ?? []"
                    :limit="10"
                />
                @endif
            </div>

            <!-- 4. Reservation Modal Popup -->
            @if($reservationModalOpen)
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div
                        wire:click="closeReserveModal"
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    ></div>

                    <!-- Modal Window -->
                    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100">
                        <h4 class="text-lg font-bold text-[#0B2454] mb-2">
                            Confirm Book Reservation
                        </h4>
                        <p class="text-sm text-slate-600 mb-4">
                            Would you like to reserve a physical copy of <span class="font-bold text-[#0B2454]">"{{ $book['title'] ?? 'this item' }}"</span>?
                        </p>

                        @if($reserveStatus === 'success')
                            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 mb-4">
                                {{ $reserveMessage }}
                            </div>
                        @elseif($reserveStatus === 'error')
                            <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800 mb-4">
                                {{ $reserveMessage }}
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                type="button"
                                wire:click="closeReserveModal"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                            >
                                {{ $reserveStatus === 'success' ? 'Close' : 'Cancel' }}
                            </button>

                            @if($reserveStatus !== 'success')
                                <button
                                    type="button"
                                    wire:click="confirmReservation"
                                    wire:loading.attr="disabled"
                                    wire:target="confirmReservation"
                                    class="px-5 py-2 rounded-xl bg-[#FCC719] hover:bg-[#E9B500] text-[#071A3D] text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg wire:loading wire:target="confirmReservation" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-[#071A3D]" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="confirmReservation">Confirm Reservation</span>
                                    <span wire:loading wire:target="confirmReservation">Reserving...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        @else
            <!-- Book Not Found State -->
            <div class="rounded-2xl border border-[#DDE5EF] bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#0B2454]">Book Record Not Found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                    We couldn't find the catalog record you were looking for. It may have been moved or removed.
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('opac.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#102B70] text-white text-xs font-bold hover:bg-[#0B225E] transition-colors shadow-xs"
                    >
                        Browse Library Catalog
                    </a>
                </div>
            </div>
        @endif

    </div>

    @if($mlApiDebug)
        <script>
            console.log('[ML API Recommendations]', @js($mlApiDebug));
        </script>
    @endif
</div>
