@props([
    // Brand link
    'homeUrl' => url('/'),

    // Quick Links List
    'quickLinks' => [
        ['label' => 'Home', 'url' => url('/')],
        ['label' => 'About Us', 'url' => url('/#about')],
        ['label' => 'OPAC Search', 'url' => url('/opac.index')],
        ['label' => 'Privacy Policy', 'url' => url('/#privacy')],
        ['label' => 'Terms of Service', 'url' => url('/#terms')],
        // ['label' => 'Student Portal', 'url' => url('/student')],
    ],

    // Online Resources List (loads dynamically from list.json if not provided)
    'onlineResources' => null,

    // Contact info & URLs
    'address' => 'Poblacion, Padre Garcia, Batangas',
    'phone' => '(043) 515 7722',
    'phoneUrl' => 'tel:+63435157722',
    'email' => 'info@padregarcia.gov.ph',
    'emailUrl' => 'mailto:info@padregarcia.gov.ph',
    'websiteUrl' => 'https://www.padregarcia.gov.ph/government/padre-garcia-polytechnic-college',
    'websiteText' => 'www.padregarcia.gov.ph',
])

@php
    if ($onlineResources === null) {
        try {
            $resourcesPath = storage_path('app/public/online_resources/list.json');
            if (file_exists($resourcesPath)) {
                $content = file_get_contents($resourcesPath);
                if ($content !== false) {
                    $decoded = json_decode($content, true);
                    $onlineResources = $decoded['online_resources'] ?? [];
                }
            }
        } catch (\Throwable $e) {
            $onlineResources = [];
        }

        if (empty($onlineResources)) {
            $onlineResources = [
                ['name' => 'ScienceDirect', 'external_url' => 'https://www.sciencedirect.com'],
                ['name' => 'Emerald Insight', 'external_url' => 'https://www.emerald.com'],
                ['name' => 'SpringerLink', 'external_url' => 'https://link.springer.com/'],
                ['name' => 'JSTOR', 'external_url' => 'https://www.jstor.org/'],
                ['name' => 'Wiley Online Library', 'external_url' => 'https://onlinelibrary.wiley.com/'],
                ['name' => 'IEEEXplore', 'external_url' => 'https://ieeexplore.ieee.org/'],
            ];
        }
    }
@endphp

<!-- Footer -->
<footer id="contact" class="relative w-full bg-[#071943] text-slate-300 pt-16 pb-12 border-t border-white/10 select-none">
    <div class="mx-auto max-w-[1380px] px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">

            <!-- Brand Column -->
            <div>
                <a href="{{ $homeUrl }}" class="inline-flex items-center gap-3 mb-5 group">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center p-0.5 shadow-sm">
                        <img
                            src="{{ asset('images/logo.webp') }}"
                            alt="PGPC Logo"
                            class="w-full h-full object-cover rounded-full"
                            onerror="this.src='{{ asset('logo.webp') }}'"
                        />
                    </div>
                    <div>
                        <span class="font-extrabold text-xl text-white tracking-tight block group-hover:text-[#FCC719] transition-colors">PGPC Library</span>
                    </div>
                </a>
                <p class="text-sm text-blue-100/70 leading-relaxed mb-4 italic">
                    "Taga-PGPC Ako: Matalino, Disiplinado, Mabuting Tao, Ipinagmamalaki ko!"
                </p>
                <p class="text-[13px] text-slate-400 leading-relaxed">
                    Empowering students and faculty through accessible knowledge, academic research, and collaborative learning.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-bold mb-5 text-base tracking-wide flex items-center gap-2">
                    <span>Quick Links</span>
                </h4>
                <ul class="space-y-2.5 text-[14px]">
                    @foreach ($quickLinks as $key => $item)
                        @php
                            $url = is_array($item) ? ($item['url'] ?? '#') : $item;
                            $label = is_array($item) ? ($item['label'] ?? $key) : $key;
                        @endphp
                        <li>
                            <a href="{{ $url }}" class="hover:text-[#FCC719] transition-colors">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Online Resources (Replaced Top Categories) -->
            <div>
                <h4 class="text-white font-bold mb-5 text-base tracking-wide flex items-center gap-2">
                    <span>Online Resources</span>
                </h4>
                <ul class="space-y-2.5 text-[14px]">
                    @foreach ($onlineResources as $key => $item)
                        @php
                            $url = is_array($item) ? ($item['external_url'] ?? $item['url'] ?? '#') : $item;
                            $name = is_array($item) ? ($item['name'] ?? $item['label'] ?? $key) : $key;
                        @endphp
                        <li>
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="hover:text-[#FCC719] transition-colors inline-flex items-center gap-1.5 group/res"
                            >
                                <span>{{ $name }}</span>
                                <svg class="h-3 w-3 text-slate-400 opacity-60 transition-all duration-150 group-hover/res:opacity-100 group-hover/res:text-[#FCC719]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Contact Us -->
            <div>
                <h4 class="text-white font-bold mb-5 text-base tracking-wide flex items-center gap-2">
                    <span>Contact Us</span>
                </h4>
                <ul class="space-y-3.5 text-[13.5px]">
                    <li class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $address }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="{{ $phoneUrl }}" class="hover:text-[#FCC719] transition-colors">{{ $phone }}</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="{{ $emailUrl }}" class="hover:text-[#FCC719] transition-colors">{{ $email }}</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <a
                            href="{{ $websiteUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="hover:text-[#FCC719] hover:underline transition-colors"
                        >
                            {{ $websiteText }}
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Copyright Line -->
        <div class="border-t border-white/10 mt-14 pt-8 text-center text-xs sm:text-sm text-slate-400">
            <p>&copy; {{ date('Y') }} Padre Garcia Polytechnic College Library. All rights reserved.</p>
        </div>
    </div>
</footer>
