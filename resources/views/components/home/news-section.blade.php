@props([
    'title' => 'News & Updates',
    'subtitle' => 'Stay updated with announcements, activities, and library events.',
    'facebookPageUrl' => 'https://www.facebook.com/PadreGarciaPolytechnicCollege/',
    'newsBlogUrl' => 'https://padregarcianews.blogspot.com',
])

<section id="news" class="relative w-full bg-[#F8FAFC] py-10 sm:py-14 lg:py-15 select-none">
    <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-8 sm:mb-10 lg:mb-12">
            <div>

                <!-- Main Heading -->
                <h2 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold tracking-tight text-[#0F172A]">
                    {{ $title }}
                </h2>

                <!-- Subtitle -->
                <p class="mt-2 text-[14.5px] sm:text-[15.5px] text-slate-500 max-w-xl leading-relaxed">
                    {{ $subtitle }}
                </p>
            </div>

            <!-- Facebook Page Pill Button -->
            <a
                href="{{ $facebookPageUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-[13.5px] font-semibold text-slate-700 shadow-xs transition-all duration-200 hover:border-[#1877F2] hover:text-[#1877F2] hover:bg-slate-50 hover:shadow-sm self-start sm:self-auto"
            >
                <!-- Facebook Official Icon -->
                <svg class="h-4 w-4 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Visit our Facebook Page</span>
            </a>
        </div>

        <!-- Two-Column Layout (Snug Facebook Column on Left, Expanded Campus News on Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <!-- Left Column: Compact Facebook Feed Card (Col 4) -->
            <div class="lg:col-span-4 xl:col-span-4 flex flex-col">
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[13px] font-bold uppercase tracking-wider text-[#102B70]">Official Facebook</span>
                    </div>
                    <a href="{{ $facebookPageUrl }}" target="_blank" rel="noopener noreferrer" class="text-[12px] font-semibold text-[#1877F2] hover:underline flex items-center gap-1">
                        <span>@PadreGarciaPolytechnicCollege</span>
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                <!-- Contained Box with Snug Width & Embedded Facebook Plugin -->
                <div class="w-full max-w-[420px] mx-auto lg:mx-0 h-[600px] flex justify-center bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <iframe
                        src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FPadreGarciaPolytechnicCollege%2F&tabs=timeline&width=420&height=600&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=true"
                        class="w-full h-full border-none"
                        scrolling="no"
                        frameborder="0"
                        allowfullscreen="true"
                        loading="lazy"
                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                    </iframe>
                </div>
            </div>

            <!-- Right Column: Expanded Campus News Blogspot Site (Col 8) -->
            <div class="lg:col-span-8 xl:col-span-8 flex flex-col">
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[13px] font-bold uppercase tracking-wider text-[#102B70]">News and Events</span>
                    </div>
                    <a
                        href="https://www.padregarcia.gov.ph/government/padre-garcia-polytechnic-college"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-[12.5px] font-semibold text-[#102B70] hover:underline flex items-center gap-1"
                    >
                        <span>padregarcia.gov.ph</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                <!-- Contained Box with Expanded Width & Embedded Blogspot Site -->
                <div class="w-full h-[600px] bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <iframe
                        id="innerFrame"
                        name="innerFrame"
                        sandbox="allow-scripts allow-popups allow-forms allow-same-origin allow-popups-to-escape-sandbox allow-downloads allow-storage-access-by-user-activation"
                        frameborder="0"
                        allowfullscreen
                        src="{{ $newsBlogUrl }}"
                        loading="lazy"
                        class="w-full h-full border-none"
                        style="overflow: auto;"
                    ></iframe>
                </div>
            </div>

        </div>

    </div>
</section>
