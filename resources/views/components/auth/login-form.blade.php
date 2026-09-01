<section
            class="relative flex flex-col min-h-dvh lg:min-h-0 lg:h-dvh lg:overflow-y-auto bg-slate-100/80 px-4 py-8 sm:px-8 lg:px-8 xl:px-12 lg:order-2 lg:col-span-6 xl:col-span-5 outline-none focus:outline-none select-none cursor-default">

            <!-- Top Right Back to Home Button -->
            <div class="absolute right-4 top-4 sm:right-6 sm:top-6 lg:right-7 lg:top-7 z-30 select-none">
                <a
                    href="{{ url('/') }}"
                    class="group inline-flex h-[40px] min-w-[128px] items-center justify-center gap-2 rounded-xl border border-slate-200/90 bg-white px-3.5 text-[14px] font-semibold text-slate-600 shadow-xs transition hover:border-slate-300 hover:bg-slate-50 hover:text-[#102b70] hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-100 select-none"
                    wire:navigate
                >
                    <svg class="h-[18px] w-[18px] text-slate-400 transition-colors group-hover:text-[#102b70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Back to Home</span>
                </a>
            </div>

            <div
                class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#102b70] via-[#fcc719] to-[#102b70] lg:hidden pointer-events-none select-none">
            </div>
            <div class="absolute right-0 top-0 h-64 w-64 rounded-bl-full bg-blue-50/80 pointer-events-none select-none"></div>

            <div class="relative z-10 w-full max-w-[520px] m-auto py-8">
                <!-- Mobile Logo Header -->
                <div class="mb-8 flex items-center justify-between lg:hidden pr-32 select-none">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 outline-none focus:outline-none" wire:navigate>
                        <img src="{{ asset('logo.webp') }}" alt="PGPC logo"
                            class="h-9 w-9 object-contain"
                            onerror="this.src='{{ asset('images/logo.webp') }}'">
                        <div class="text-left">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-[#102b70] leading-none">Padre Garcia Polytechnic College Library</span>
                            <span class="block text-[9px] font-semibold text-slate-500 uppercase tracking-widest mt-0.5 leading-none">System</span>
                        </div>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </section>
