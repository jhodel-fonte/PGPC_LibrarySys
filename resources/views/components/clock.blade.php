        <!-- Live Clock powered entirely by Alpine.js -->
        <div 
            x-data="{ 
                time: '', 
                date: '',
                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                },
                updateClock() {
                    const now = new Date();
                    this.time = now.toLocaleTimeString('en-US', { hour12: true });
                    this.date = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                }
            }" 
            class="hidden lg:flex flex-col items-end leading-tight"
        >
            <span x-text="time" class="text-sm font-bold text-gray-800 leading-tight"></span>
            <span x-text="date" class="text-[10px] text-gray-500 font-medium uppercase tracking-wider"></span>
        </div>