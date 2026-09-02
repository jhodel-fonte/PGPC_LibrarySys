<section
    class="relative flex min-h-[660px] lg:h-[720px] xl:h-[760px] 2xl:h-[780px] w-full items-center overflow-hidden bg-[#071943] text-white select-none -mt-[96px] sm:-mt-[100px] pt-[96px] sm:pt-[100px]"
>
    <!-- Background Image with Lazy Loading -->
    <img
        src="{{ asset('images/pgpc-ng.webp') }}"
        alt="Padre Garcia Polytechnic College Campus"
        loading="lazy"
        decoding="async"
        fetchpriority="low"
        class="absolute inset-0 h-full w-full object-cover object-center pointer-events-none select-none"
    >

    <div class="absolute inset-0 bg-gradient-to-r from-[#071943] via-[#071943]/92 to-[#071943]/65 pointer-events-none"></div>

    <div class="relative z-10 mx-auto w-full max-w-[1380px] px-3 sm:px-6 lg:px-8 py-14 sm:py-16 lg:py-20 xl:py-24 pb-28 sm:pb-32 lg:pb-36">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-12 lg:gap-8 xl:gap-12">

            <div class="lg:col-span-7 xl:col-span-7">

                <h1 class="mt-3 text-3xl sm:text-5xl lg:text-[48px] xl:text-[54px] font-extrabold leading-[1.14] tracking-tight text-white">
                    Dream Big, <span class="text-[#FCC719]">Learn More</span><br class="hidden sm:inline">
                    Achieve Greater
                </h1>

                <!-- Supporting Text -->
                <p class="mt-4 max-w-xl text-[15px] sm:text-[16px] leading-relaxed text-slate-200/90 font-normal">
                    Search the catalog, access digital resources, reserve materials, and manage your library journey — all in one place.
                </p>

                <!-- Search Bar Container -->
                <form action="{{ route('opac.index') }}" method="GET" class="relative mt-7 max-w-xl">
                    <div class="flex items-center rounded-2xl bg-white p-1.5 shadow-2xl shadow-black/40 border border-white/20">
                        <div class="flex flex-1 items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                type="text"
                                name="search"
                                placeholder="Search books, journals, author, or subject..."
                                class="w-full border-0 border-none bg-transparent px-3 py-2.5 text-[14.5px] text-slate-900 placeholder:text-slate-400 focus:border-0 focus:border-none focus:outline-none focus:ring-0 shadow-none"
                            >
                        </div>

                        {{-- <!-- Resource Filter Dropdown -->
                        <div class="hidden sm:flex items-center border-l border-slate-200 px-3">
                            <select name="type" class="bg-transparent text-[13px] font-semibold text-slate-600 focus:outline-none cursor-pointer pr-1">
                                <option value="all">All Resources</option>
                                <option value="books">Books</option>
                                <option value="journals">Journals</option>
                                <option value="theses">Theses</option>
                            </select>
                        </div> --}}

                        <!-- Search Button -->
                        <button type="submit"
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#FCC719] text-[#071943] shadow-sm transition hover:bg-[#ffd84c] active:scale-95 focus:outline-none cursor-pointer"
                            aria-label="Submit search"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Popular Searches Pills -->
                <div class="mt-3.5 flex flex-wrap items-center gap-2 text-[12px] text-slate-300">
                    <span class="font-medium text-slate-400">Popular searches:</span>
                    @foreach (['Data Structures', 'Python Programming', 'Educational Research', 'Management'] as $term)
                        <a
                            href="{{ Route::has('opac.index') ? route('opac.index', ['search' => $term]) : url('/#opac?search=' . urlencode($term)) }}"
                            class="rounded-full bg-white/10 px-3 py-1 text-slate-200 backdrop-blur-xs transition hover:bg-[#FCC719] hover:text-[#071943] hover:font-semibold"
                        >
                            {{ $term }}
                        </a>
                    @endforeach
                </div>

            </div>

            <!-- Right Column: Frosted Glass "Library Services" Card -->
            <div class="lg:col-span-5 xl:col-span-5 flex justify-center lg:justify-end">
                <div class="w-full max-w-md rounded-2xl border border-white/15 bg-white/10 p-6 sm:p-7 backdrop-blur-md shadow-2xl">

                    <h2 class="text-[19px] font-bold text-white tracking-tight">
                        Library Services
                    </h2>

                    <div class="mt-5 space-y-4">

                        <!-- Service 1: Search the Catalog -->
                        <a href="{{ Route::has('opac.index') ? route('opac.index') : url('/#opac') }}" class="group flex items-start gap-3.5 rounded-xl p-2.5 transition hover:bg-white/10">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 border border-white/15 text-[#FCC719] transition group-hover:bg-[#FCC719] group-hover:text-[#071943]">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-[14.5px] font-bold text-white transition group-hover:text-[#FCC719]">Search the Catalog</h3>
                                <p class="mt-0.5 text-[12.5px] text-slate-300">Find books, e-books, journals, and more.</p>
                            </div>
                        </a>

                        <!-- Service 2: Check Availability -->
                        <div class="flex items-start gap-3.5 rounded-xl p-2.5 transition hover:bg-white/10">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 border border-white/15 text-[#FCC719]">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-[14.5px] font-bold text-white">Check Availability</h3>
                                <p class="mt-0.5 text-[12.5px] text-slate-300">See which items are currently available.</p>
                            </div>
                        </div>

                        <!-- Service 3: Reserve Items -->
                        <div class="flex items-start gap-3.5 rounded-xl p-2.5 transition hover:bg-white/10">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 border border-white/15 text-[#FCC719]">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-[14.5px] font-bold text-white">Reserve Items</h3>
                                <p class="mt-0.5 text-[12.5px] text-slate-300">Place holds on books and materials.</p>
                            </div>
                        </div>

                        <!-- Service 4: View Transactions -->
                        <div class="flex items-start gap-3.5 rounded-xl p-2.5 transition hover:bg-white/10">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 border border-white/15 text-[#FCC719]">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-[14.5px] font-bold text-white">View Transactions</h3>
                                <p class="mt-0.5 text-[12.5px] text-slate-300">Track your borrowed and return history.</p>
                            </div>
                        </div>

                    </div>

                    <!-- Card Footer Link -->
                    <div class="mt-5 border-t border-white/10 pt-4 text-right">
                        <a
                            href="{{ url('/#services') }}"
                            class="inline-flex items-center gap-1.5 text-[13.5px] font-bold text-[#FCC719] transition hover:text-[#ffd84c] hover:underline"
                        >
                            <span>View all services</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Organic Bottom Wave Divider with Gold Accent Line -->
    <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none pointer-events-none z-10">
        <svg class="relative block w-full h-[48px] sm:h-[64px]" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none">
            <!-- Smooth Wave Fill (Transitions into page background #F8FAFC) -->
            <path d="M0,45 C280,75 560,15 900,48 C1150,72 1320,35 1440,45 L1440,80 L0,80 Z" fill="#F8FAFC" />
            <!-- Gold Accent Line along the Wave Curve -->
            <path d="M0,45 C280,75 560,15 900,48 C1150,72 1320,35 1440,45" stroke="#FCC719" stroke-width="3.5" fill="none" />
        </svg>
    </div>
</section>
