<div x-data="{
        isLoading: false,
        barProgress: 'w-0',
        transitionSpeed: 'duration-500',
        
        startLoading() {
            this.isLoading = true;
            this.transitionSpeed = 'duration-[3000ms]';
            // Start creeping slowly to 85% width
            this.barProgress = 'w-[85%]';
        },
        
        stopLoading() {
            // Zip to 100% quickly
            this.transitionSpeed = 'duration-300';
            this.barProgress = 'w-full';
            
            // Fade out after reaching 100%
            setTimeout(() => {
                this.isLoading = false;
                // Reset width after fade out
                setTimeout(() => {
                    this.barProgress = 'w-0';
                }, 300);
            }, 300);
        }
    }"
    x-on:livewire:navigate.window="startLoading()"
    x-on:livewire:navigated.window="stopLoading()"
>
    <div 
        class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-[#fcc719] via-[#3b82f6] to-[#fcc719] z-[999999] pointer-events-none transition-all ease-out shadow-[0_0_10px_rgba(252,199,25,0.7)]"
        :class="[isLoading ? 'opacity-100' : 'opacity-0', transitionSpeed, barProgress]"
    >
        <div class="absolute right-0 w-[120px] h-full opacity-100 rotate-3 -translate-y-1 shadow-[0_0_12px_#fcc719,0_0_6px_#fcc719]"></div>
    </div>
</div>