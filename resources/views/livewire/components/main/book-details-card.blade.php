<div class="w-full select-none">

    <!-- Top Navigation: Back to Results -->
    <div class="mb-5">
        <a
            href="{{ url()->previous() !== url()->current() ? url()->previous() : route('opac.index') }}"
            class="inline-flex items-center gap-2 text-[14px] font-semibold text-[#102B70] hover:text-[#0B225E] transition-colors group"
        >
            <svg width="18" height="18" class="h-4.5 w-4.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Results</span>
        </a>
    </div>

    <!-- 1. Top Card: Book Hero & Summary Card -->
    <div class="rounded-2xl border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.05)] mb-8">
        <div class="flex flex-col lg:flex-row items-start gap-8 lg:gap-10">

            <!-- 1.1 Left: Book Cover (Matching the uploaded image) -->
            <div class="w-[185px] sm:w-[205px] h-[270px] sm:h-[300px] shrink-0 rounded-xl overflow-hidden shadow-[0_8px_24px_rgba(7,26,61,0.14)] border border-slate-200 relative mx-auto lg:mx-0">
                @if(!empty($book['cover_url']))
                    <img
                        src="{{ $book['cover_url'] }}"
                        alt="{{ $book['title'] }}"
                        class="w-full h-full object-cover"
                    >
                @else
                    <!-- High-Fidelity Custom SVG Cover replicating 'Programming Fundamentals - An Introduction - John Smith' -->
                    <svg class="w-full h-full" viewBox="0 0 210 305" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="bgGrad" x1="0" y1="0" x2="210" y2="305" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#0E234A" />
                                <stop offset="0.5" stop-color="#081630" />
                                <stop offset="1" stop-color="#040B19" />
                            </linearGradient>
                            <linearGradient id="goldGrad" x1="0" y1="0" x2="100%" y2="100%" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFE27A" />
                                <stop offset="0.6" stop-color="#FCC719" />
                                <stop offset="1" stop-color="#F39C12" />
                            </linearGradient>
                            <linearGradient id="cyberGlow" x1="0" y1="0" x2="0" y2="100%" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#38BDF8" stop-opacity="0.8" />
                                <stop offset="1" stop-color="#1D4ED8" stop-opacity="0.2" />
                            </linearGradient>
                            <radialGradient id="chipGlow" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#081630" stop-opacity="0" />
                            </radialGradient>
                        </defs>

                        <!-- Background -->
                        <rect width="210" height="305" fill="url(#bgGrad)" />

                        <!-- Subtle Outer Page Edge Effect -->
                        <rect x="0" y="0" width="8" height="305" fill="#000000" fill-opacity="0.25" />
                        <line x1="8" y1="0" x2="8" y2="305" stroke="#FFFFFF" stroke-opacity="0.08" stroke-width="1" />

                        <!-- Book Title: PROGRAMMING -->
                        <text x="105" y="48" text-anchor="middle" font-family="'Open Sans', 'Inter', sans-serif" font-weight="900" font-size="15" fill="url(#goldGrad)" letter-spacing="1.2">
                            PROGRAMMING
                        </text>
                        <!-- Book Title: FUNDAMENTALS -->
                        <text x="105" y="68" text-anchor="middle" font-family="'Open Sans', 'Inter', sans-serif" font-weight="900" font-size="15" fill="url(#goldGrad)" letter-spacing="1.2">
                            FUNDAMENTALS
                        </text>

                        <!-- Subtitle: AN INTRODUCTION -->
                        <text x="105" y="86" text-anchor="middle" font-family="'Open Sans', 'Inter', sans-serif" font-weight="600" font-size="7.5" fill="#93C5FD" letter-spacing="2">
                            AN INTRODUCTION
                        </text>

                        <!-- Central Cyber Tech Graphic (Glow & Circuitry) -->
                        <circle cx="105" cy="165" r="55" fill="url(#chipGlow)" />

                        <!-- Isometric Board / Grid lines -->
                        <g opacity="0.65" stroke="#38BDF8" stroke-width="0.8">
                            <polygon points="105,120 160,150 105,180 50,150" fill="#0A1E40" fill-opacity="0.6" stroke="#0284C7" stroke-width="1.2" />
                            <polygon points="105,130 148,154 105,178 62,154" fill="#0369A1" fill-opacity="0.3" stroke="#38BDF8" stroke-width="0.7" />

                            <!-- Glowing Inner Chip -->
                            <polygon points="105,142 128,155 105,168 82,155" fill="#FCC719" fill-opacity="0.15" stroke="#FBBF24" stroke-width="1" />

                            <!-- Circuit Tracks -->
                            <path d="M 65 142 L 40 128 L 25 128" />
                            <path d="M 145 142 L 170 128 L 185 128" />
                            <path d="M 105 180 L 105 210 L 80 225" />
                            <path d="M 105 180 L 105 210 L 130 225" />
                            <path d="M 60 160 L 45 170 L 30 170" />
                            <path d="M 150 160 L 165 170 L 180 170" />
                        </g>

                        <!-- Nodes / Dots -->
                        <circle cx="25" cy="128" r="1.8" fill="#38BDF8" />
                        <circle cx="185" cy="128" r="1.8" fill="#38BDF8" />
                        <circle cx="80" cy="225" r="1.8" fill="#FBBF24" />
                        <circle cx="130" cy="225" r="1.8" fill="#FBBF24" />
                        <circle cx="30" cy="170" r="1.5" fill="#38BDF8" />
                        <circle cx="180" cy="170" r="1.5" fill="#38BDF8" />

                        <!-- Center Microchip Core -->
                        <rect x="97" y="150" width="16" height="10" rx="1.5" fill="#0F172A" stroke="#38BDF8" stroke-width="1" />
                        <line x1="94" y1="153" x2="97" y2="153" stroke="#FCC719" stroke-width="0.8" />
                        <line x1="94" y1="157" x2="97" y2="157" stroke="#FCC719" stroke-width="0.8" />
                        <line x1="113" y1="153" x2="116" y2="153" stroke="#FCC719" stroke-width="0.8" />
                        <line x1="113" y1="157" x2="116" y2="157" stroke="#FCC719" stroke-width="0.8" />

                        <!-- Author: JOHN SMITH -->
                        <text x="105" y="268" text-anchor="middle" font-family="'Open Sans', 'Inter', sans-serif" font-weight="700" font-size="10.5" fill="#FFFFFF" letter-spacing="2">
                            JOHN SMITH
                        </text>
                    </svg>
                @endif
            </div>

            <!-- 1.2 Center: Book Metadata & Identifiers -->
            <div class="flex-1 min-w-0">
                <!-- Title & Subtitle -->
                <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#0B2454] leading-tight mt-2.5 tracking-tight">
                    {{ $book['title'] }}
                </h1>
                @if(!empty($book['subtitle']))
                    <p class="text-[15px] sm:text-[16px] text-[#64748B] mt-1 font-normal">
                        {{ $book['subtitle'] }}
                    </p>
                @endif

                <!-- Author Link with User Icon -->
                <div class="mt-2.5 flex items-center gap-2">
                    <svg width="16" height="16" class="h-4 w-4 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-[14.5px] font-semibold text-[#102B70] hover:underline cursor-pointer">
                        {{ $book['author'] }}
                    </span>
                </div>

                <!-- Horizontal Metadata Row with Bullets -->
                <div class="flex items-center flex-wrap gap-x-2.5 gap-y-1.5 mt-3.5 text-[13.5px] text-[#475569] font-medium">
                    <!-- Year -->
                    <div class="flex items-center gap-1.5">
                        <svg width="15" height="15" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $book['year'] }}</span>
                    </div>
                    <span class="text-slate-300 font-bold">•</span>

                    <!-- Resource Type -->
                    <div class="flex items-center gap-1.5">
                        <svg width="15" height="15" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>{{ $book['type'] }}</span>
                    </div>
                    <span class="text-slate-300 font-bold">•</span>

                    <!-- Pages -->
                    <div class="flex items-center gap-1.5">
                        <svg width="15" height="15" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>{{ $book['pages'] }}</span>
                    </div>
                    <span class="text-slate-300 font-bold">•</span>

                    <!-- Language -->
                    <div class="flex items-center gap-1.5">
                        <svg width="15" height="15" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span>{{ $book['language'] }}</span>
                    </div>
                </div>

                <!-- 3 Identifiers: Call No, Accession No, ISBN -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5 mt-5 border-t border-slate-100">
                    <!-- Call No. -->
                    <div>
                        <span class="block text-[11.5px] font-semibold text-[#64748B]">Call No.</span>
                        <span class="block text-[13.5px] font-bold text-[#0B2454] mt-0.5">{{ $book['call_no'] }}</span>
                    </div>

                    <!-- Accession No. -->
                    <div>
                        <span class="block text-[11.5px] font-semibold text-[#64748B]">Accession No.</span>
                        <span class="block text-[13.5px] font-bold text-[#0B2454] mt-0.5">{{ $book['accession_no'] }}</span>
                    </div>

                    <!-- ISBN -->
                    <div>
                        <span class="block text-[11.5px] font-semibold text-[#64748B]">ISBN</span>
                        <span class="block text-[13.5px] font-bold text-[#0B2454] mt-0.5">{{ $book['isbn'] }}</span>
                    </div>
                </div>

                <!-- Subjects -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="block text-[12px] font-bold text-[#334155] mb-1">Subjects</span>
                    <div class="text-[13.5px] text-[#102B70] font-medium leading-relaxed">
                        @foreach($book['subjects'] as $index => $sub)
                            <a href="{{ route('opac.index', ['subject' => $sub]) }}" class="hover:underline cursor-pointer">
                                {{ $sub }}
                            </a>{{ !$loop->last ? ' | ' : '' }}
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- 1.3 Right: Availability Status & Actions Panel -->
            <div class="w-full lg:w-[260px] xl:w-[280px] shrink-0 flex flex-col gap-3.5 border-t lg:border-t-0 lg:border-l border-slate-100 pt-6 lg:pt-0 lg:pl-8">

                <!-- Header: Availability Status -->
                <div>
                    <span class="block text-[12.5px] font-semibold text-[#64748B]">
                        Availability Status
                    </span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[17px] font-bold {{ $book['status_color'] ?? 'text-emerald-700' }}">
                            {{ $book['status_label'] ?? 'Available' }}
                        </span>
                    </div>
                </div>

                <!-- Notice Banner -->
                @if(($book['status'] ?? '') === 'available')
                    <div class="rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-3 flex items-start gap-2.5">
                        <svg width="18" height="18" class="h-4.5 w-4.5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-[12px] font-bold text-emerald-950 leading-tight">
                                This item is available for borrowing.
                            </p>
                            <p class="text-[11.5px] text-emerald-700 leading-tight mt-0.5">
                                You may reserve this item.
                            </p>
                        </div>
                    </div>
                @elseif(($book['status'] ?? '') === 'checked_out')
                    <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-3 flex items-start gap-2.5">
                        <svg width="18" height="18" class="h-4.5 w-4.5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-[12px] font-bold text-rose-950 leading-tight">
                                All copies currently checked out.
                            </p>
                            <p class="text-[11.5px] text-rose-700 leading-tight mt-0.5">
                                Check back after scheduled return dates.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-3 flex items-start gap-2.5">
                        <svg width="18" height="18" class="h-4.5 w-4.5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-[12px] font-bold text-amber-950 leading-tight">
                                Reserved by another borrower.
                            </p>
                            <p class="text-[11.5px] text-amber-700 leading-tight mt-0.5">
                                Awaiting collection at circulation desk.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Button 1: Reserve This Item (Primary Strongest Element: Gold #FCC719) -->
                @if(!empty($book['can_reserve']))
                    <button
                        type="button"
                        wire:click="openReserveModal"
                        class="w-full h-[44px] rounded-xl bg-[#FCC719] hover:bg-[#E9B500] text-[#071A3D] font-bold text-[14px] flex items-center justify-center gap-2 shadow-xs hover:shadow transition-all active:scale-[0.99] cursor-pointer"
                    >
                        <svg width="17" height="17" class="h-4 w-4 fill-current text-[#071A3D]" viewBox="0 0 24 24">
                            <path d="M5 4a2 2 0 012-2h10a2 2 0 012 2v18l-7-4-7 4V4z" />
                        </svg>
                        <span>Reserve This Item</span>
                    </button>
                @else
                    <button
                        type="button"
                        disabled
                        class="w-full h-[44px] rounded-xl bg-slate-100 text-slate-400 font-bold text-[14px] flex items-center justify-center gap-2 cursor-not-allowed border border-slate-200"
                    >
                        <svg width="17" height="17" class="h-4 w-4 fill-current text-slate-400" viewBox="0 0 24 24">
                            <path d="M5 4a2 2 0 012-2h10a2 2 0 012 2v18l-7-4-7 4V4z" />
                        </svg>
                        <span>Unavailable to Reserve</span>
                    </button>
                @endif

                <!-- Button 2: Add to My List -->
                <button
                    type="button"
                    wire:click="toggleBookmark"
                    class="w-full h-[42px] rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-[#102B70] font-semibold text-[13.5px] flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-[0.99] cursor-pointer"
                    :class="{'bg-[#EFF6FF] border-[#3B82F6]': @json($isBookmarked)}"
                >
                    <svg width="17" height="17" class="h-4 w-4 {{ $isBookmarked ? 'fill-current text-[#102B70]' : 'text-[#102B70]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    <span>{{ $isBookmarked ? 'In My List' : 'Add to My List' }}</span>
                </button>

                <!-- Button 3: Share -->
                <div x-data="{ copied: false }" class="w-full">
                    <button
                        type="button"
                        @click="
                            if (navigator.share) {
                                navigator.share({ title: '{{ addslashes($book['title']) }}', url: window.location.href });
                            } else {
                                navigator.clipboard.writeText(window.location.href);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            }
                        "
                        class="w-full h-[42px] rounded-xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-[#102B70] font-semibold text-[13.5px] flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-[0.99] cursor-pointer"
                    >
                        <svg width="17" height="17" class="h-4 w-4 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <span x-text="copied ? 'Link Copied!' : 'Share'">Share</span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- 2. Bottom Card: Tabs & Availability Table Card -->
    <div class="rounded-2xl border border-[#DDE5EF] bg-white p-6 sm:p-8 lg:p-10 shadow-[0_4px_16px_rgba(7,26,61,0.05)]">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

            <!-- 2.1 Left: Tabbed Information (Overview, Details, Table of Contents, Reviews) -->
            <div class="lg:col-span-6">

                <!-- Tabs Navigation -->
                <div class="flex items-center gap-6 border-b border-[#E2E8F0] pb-0 text-[14.5px]">
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

                <!-- Tab 1: Overview (Default) -->
                @if($activeTab === 'overview')
                    <div class="pt-5">
                        <!-- About this Book Subheading -->
                        <div class="flex items-center gap-2 text-[#0B2454] font-bold text-[15.5px] mb-3">
                            <svg width="18" height="18" class="h-4.5 w-4.5 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>About this Book</span>
                        </div>

                        <!-- Description -->
                        <p class="text-[13.5px] text-[#475569] leading-relaxed font-normal">
                            {{ $book['description'] }}
                        </p>

                        <!-- Keywords -->
                        <div class="mt-6">
                            <h4 class="text-[13px] font-bold text-[#0B2454] mb-2.5">
                                Keywords
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($book['keywords'] as $keyword)
                                    <span class="inline-block px-3 py-1 rounded-full text-[12px] font-medium bg-[#F1F5F9] text-[#334155] hover:bg-[#E2E8F0] transition cursor-pointer">
                                        {{ $keyword }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                <!-- Tab 2: Details -->
                @elseif($activeTab === 'details')
                    <div class="pt-5 space-y-3 text-[13.5px]">
                        <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                            <span class="font-semibold text-slate-500">Format</span>
                            <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['format'] }}</span>
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
                            <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['language'] }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                            <span class="font-semibold text-slate-500">Publication Year</span>
                            <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['year'] }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                            <span class="font-semibold text-slate-500">Pagination</span>
                            <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['pages'] }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                            <span class="font-semibold text-slate-500">ISBN</span>
                            <span class="col-span-2 text-[#0B2454] font-medium">{{ $book['isbn'] }}</span>
                        </div>
                        <div class="grid grid-cols-3 py-1.5 border-b border-slate-100">
                            <span class="font-semibold text-slate-500">Call Number</span>
                            <span class="col-span-2 text-[#0B2454] font-mono text-xs font-bold">{{ $book['call_no'] }}</span>
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
                    <h3 class="text-[15.5px] font-bold text-[#0B2454]">
                        Availability at PGPC Library
                    </h3>
                    <span class="text-[12.5px] font-semibold text-[#64748B]">
                        Total Copies: {{ $book['total_copies'] ?? 3 }}
                    </span>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto rounded-xl border border-[#E2E8F0]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                                <th class="py-2.5 px-3.5 text-[11.5px] font-bold text-[#475569] uppercase tracking-wider">
                                    Location
                                </th>
                                <th class="py-2.5 px-3.5 text-[11.5px] font-bold text-[#475569] uppercase tracking-wider">
                                    Collection
                                </th>
                                <th class="py-2.5 px-3.5 text-[11.5px] font-bold text-[#475569] uppercase tracking-wider">
                                    Call No.
                                </th>
                                <th class="py-2.5 px-3.5 text-[11.5px] font-bold text-[#475569] uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="py-2.5 px-3.5 text-[11.5px] font-bold text-[#475569] uppercase tracking-wider">
                                    Due Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0] text-[12.5px]">
                            @forelse($book['holdings'] as $copy)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Location -->
                                    <td class="py-3 px-3.5 font-medium text-[#1E293B] whitespace-nowrap">
                                        {{ $copy['location'] }}
                                    </td>

                                    <!-- Collection -->
                                    <td class="py-3 px-3.5 text-[#64748B] whitespace-nowrap">
                                        {{ $copy['collection'] }}
                                    </td>

                                    <!-- Call No. -->
                                    <td class="py-3 px-3.5 font-mono text-[12px] text-[#334155] whitespace-nowrap">
                                        {{ $copy['call_no'] }}
                                    </td>

                                    <!-- Status with colored dot -->
                                    <td class="py-3 px-3.5 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full {{ $copy['dot_color'] }}"></span>
                                            <span class="font-semibold {{ $copy['status_color'] }}">
                                                {{ $copy['status_label'] }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Due Date -->
                                    <td class="py-3 px-3.5 whitespace-nowrap {{ $copy['status'] === 'reserved' ? 'text-amber-700 font-medium text-[11.5px]' : 'text-[#64748B]' }}">
                                        {{ $copy['due_date'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400 text-xs">
                                        No individual holding copies cataloged.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer text -->
                <p class="text-[11.5px] text-[#94A3B8] mt-2.5">
                    Availability updates in real-time.
                </p>

            </div>

        </div>
    </div>

    <!-- 3. Reservation Modal Popup -->
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
                    Would you like to reserve a physical copy of <span class="font-bold text-[#0B2454]">"{{ $book['title'] }}"</span>?
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
                            class="px-5 py-2 rounded-xl bg-[#FCC719] hover:bg-[#E9B500] text-[#071A3D] text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5"
                        >
                            <span>Confirm Reservation</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
