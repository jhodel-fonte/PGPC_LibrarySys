<!-- Footer -->
<footer id="contact" class="bg-[#071943] text-slate-300 border-t border-white/10 py-16">
    <div class="container mx-auto px-6 md:px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">

            <!-- Brand Column -->
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-3 mb-6 group">
                    <div class="w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center overflow-hidden p-1 shadow-sm group-hover:border-[#FCC719] transition-colors">
                        <img src="{{ asset('logo.webp') }}" alt="PGPC Logo" class="w-full h-full object-contain"
                            onerror="this.src='{{ asset('images/logo.webp') }}'" />
                    </div>
                    <span class="font-bold text-xl text-white group-hover:text-[#FCC719] transition-colors">PGPC Library</span>
                </a>
                <p class="text-sm text-slate-400 leading-relaxed mb-4">
                    Taga-PGPC Ako: Matalino, Disiplinado, Mabuting Tao, Ipinagmamalaki ko!
                </p>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Official Library Management & Cataloging System of Padre Garcia Polytechnic College.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg tracking-tight">Quick Links</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-[#FCC719] transition-colors">Home</a></li>
                    <li><a href="#about" class="hover:text-[#FCC719] transition-colors">About Us</a></li>
                    <li><a href="{{ Route::has('opac.index') ? route('opac.index') : '#about' }}" class="hover:text-[#FCC719] transition-colors">Catalog Search</a></li>
                    <li><a href="{{ Route::has('privacy') ? route('privacy') : '#' }}" class="hover:text-[#FCC719] transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ Route::has('terms') ? route('terms') : '#' }}" class="hover:text-[#FCC719] transition-colors">Terms of Service</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-[#FCC719] transition-colors font-semibold text-[#FCC719]/90">Student Portal</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg tracking-tight">Top Categories</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ Route::has('opac.index') ? route('opac.index', ['category' => 'Computer Science']) : '#' }}" class="hover:text-[#FCC719] transition-colors">Computer Science</a></li>
                    <li><a href="{{ Route::has('opac.index') ? route('opac.index', ['category' => 'Engineering']) : '#' }}" class="hover:text-[#FCC719] transition-colors">Engineering</a></li>
                    <li><a href="{{ Route::has('opac.index') ? route('opac.index', ['category' => 'Education']) : '#' }}" class="hover:text-[#FCC719] transition-colors">Education</a></li>
                    <li><a href="{{ Route::has('opac.index') ? route('opac.index', ['category' => 'Business & Accountancy']) : '#' }}" class="hover:text-[#FCC719] transition-colors">Business & Accountancy</a></li>
                    <li><a href="{{ Route::has('opac.index') ? route('opac.index', ['category' => 'General References']) : '#' }}" class="hover:text-[#FCC719] transition-colors">General References</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-white font-bold mb-6 text-lg tracking-tight">Contact Us</h4>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0 mt-0.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Poblacion, Padre Garcia, Batangas</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:+63435157722" class="hover:text-[#FCC719] transition-colors">(043) 515 7722</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="mailto:info@padregarcia.gov.ph" class="hover:text-[#FCC719] transition-colors">info@padregarcia.gov.ph</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#FCC719] shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <a href="https://www.padregarcia.gov.ph/government/padre-garcia-polytechnic-college"
                            target="_blank" rel="noopener noreferrer"
                            class="hover:text-[#FCC719] hover:underline transition-colors">
                            www.padregarcia.gov.ph
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="border-t border-white/10 mt-16 pt-8 text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} Padre Garcia Polytechnic College Library. All rights reserved.</p>
        </div>
    </div>
</footer>
