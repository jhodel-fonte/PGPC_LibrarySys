<a href="#" class="flex items-center gap-3 group" aria-label="PGPC Library home">
                <div class="w-10 h-10 shrink-0 rounded-full flex items-center justify-center overflow-hidden shadow-inner group-hover:scale-105 transition-transform duration-300">
                    <img src="{{ asset('images/logo.webp') }}" alt="PGPC Logo" class="w-full h-full object-cover" />
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm tracking-wide text-white leading-tight">{{ config('settings.name') }}</span>
                    <span class="text-[10px] text-white/60 font-semibold tracking-wider uppercase">{{ config('settings.tagline') }}</span>
                </div>
            </a>