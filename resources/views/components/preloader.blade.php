<div 
    x-data="{ 
        isLoading: true,
        barProgress: 'w-0', // Starts at 0% width
        minDelay: 1000, // Minimum time in milliseconds (1.5 seconds)
        init() {
            // 1. Kick off the loading bar animation immediately
            setTimeout(() => {
                this.barProgress = 'w-[85%]'; // Slowly crawl to 85% width
            }, 50);

            const startTime = Date.now();
            
            const hidePreloader = () => {
                const elapsedTime = Date.now() - startTime;
                const remainingTime = Math.max(0, this.minDelay - elapsedTime);
                
                setTimeout(() => {
                    // 2. Zip to 100% width when loading is done
                    this.barProgress = 'w-full'; 
                    
                    // 3. Trigger the 3D zoom and fade exit
                    this.isLoading = false; 
                    document.getElementById('portal-content')?.classList.replace('opacity-0', 'opacity-100'); 
                }, remainingTime);
            };

            if (document.readyState === 'complete') {
                hidePreloader();
            } else {
                window.addEventListener('load', hidePreloader);
            }
        }
    }"
>
    <!-- Top Navigation Progress Bar -->
    <div 
        class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-[#fcc719] via-[#3b82f6] to-[#fcc719] z-[999999] pointer-events-none transition-all ease-out shadow-[0_0_10px_rgba(252,199,25,0.7)]"
        :class="[isLoading ? 'opacity-100 duration-[2000ms]' : 'opacity-0 duration-300', barProgress]"
    >
        <div class="absolute right-0 w-[120px] h-full opacity-100 rotate-3 -translate-y-1 shadow-[0_0_12px_#fcc719,0_0_6px_#fcc719]"></div>
    </div>

    <!-- Main Background (Handles only the fade out) -->
    <div 
        :class="isLoading ? 'opacity-100 visible' : 'opacity-0 invisible pointer-events-none'"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#102b70] transition-all duration-700 ease-in-out motion-reduce:transition-none"
        role="status" 
        aria-label="Loading PGPC Library"
    >
        
        <!-- EXTREME 3D ZOOM WRAPPER (Handles the logo flying at the camera) -->
        <div 
            :class="isLoading ? 'scale-100' : 'scale-[2] opacity-0'"
            class="transition-all duration-700 ease-in-out origin-center flex items-center justify-center"
        >
            <!-- Logo -->
            <img 
                src="{{ asset('logo.webp') }}"
                alt="PGPC logo" 
                class="rounded-full object-cover shadow-[0_22px_55px_rgba(0,0,0,0.22)] animate-pgpc-pulse motion-reduce:animate-none w-[clamp(130px,18dvw,210px)] h-[clamp(130px,18dvw,210px)]"
            >
        </div>

    </div>
</div>