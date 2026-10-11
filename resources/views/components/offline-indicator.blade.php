<script>
    if (typeof window.offlineIndicatorHandler === 'undefined') {
        window.offlineIndicatorHandler = function() {
            return {
                isOffline: !navigator.onLine,
                showOfflineNotification: !navigator.onLine,
                showOnlineNotification: false,
                isChecking: false,
                shake: false,

                init() {
                    this.isOffline = !navigator.onLine;
                    if (this.isOffline) {
                        this.showOfflineNotification = true;
                    }

                    // Browser online / offline events
                    window.addEventListener('offline', () => {
                        this.isOffline = true;
                        this.showOfflineNotification = true;
                        this.showOnlineNotification = false;
                        this.triggerShake();
                    });

                    window.addEventListener('online', () => {
                        this.isOffline = false;
                        this.showOfflineNotification = false;
                        this.showOnlineNotification = true;
                        setTimeout(() => {
                            this.showOnlineNotification = false;
                        }, 3500);
                    });

                    // Hook Livewire 3 request failures
                    if (window.Livewire) {
                        this.bindLivewire();
                    } else {
                        document.addEventListener('livewire:init', () => {
                            this.bindLivewire();
                        });
                    }

                    // Global listener for interactive button/link clicks when offline
                    document.addEventListener('click', (e) => {
                        if (!navigator.onLine || this.isOffline) {
                            const btn = e.target.closest('button, input[type="submit"], input[type="button"], a[wire\\:click], a[href]:not([href^="#"])');
                            if (btn) {
                                this.isOffline = true;
                                this.showOfflineNotification = true;
                                this.triggerShake();
                            }
                        }
                    }, true);
                },

                bindLivewire() {
                    if (window.Livewire && typeof window.Livewire.hook === 'function') {
                        window.Livewire.hook('request', ({ fail }) => {
                            fail(({ status, preventDefault }) => {
                                if (!navigator.onLine || status === 0) {
                                    this.isOffline = true;
                                    this.showOfflineNotification = true;
                                    this.triggerShake();
                                    if (typeof preventDefault === 'function') {
                                        preventDefault();
                                    }
                                }
                            });
                        });
                    }
                },

                triggerShake() {
                    this.shake = true;
                    setTimeout(() => { this.shake = false; }, 600);
                },

                checkConnection() {
                    this.isChecking = true;
                    if (!navigator.onLine) {
                        setTimeout(() => {
                            this.isChecking = false;
                            this.isOffline = true;
                            this.showOfflineNotification = true;
                            this.triggerShake();
                        }, 400);
                        return;
                    }

                    fetch(window.location.href, { method: 'HEAD', cache: 'no-cache' })
                        .then(() => {
                            this.isChecking = false;
                            this.isOffline = false;
                            this.showOfflineNotification = false;
                            this.showOnlineNotification = true;
                            setTimeout(() => { this.showOnlineNotification = false; }, 3500);
                        })
                        .catch(() => {
                            this.isChecking = false;
                            this.isOffline = true;
                            this.showOfflineNotification = true;
                            this.triggerShake();
                        });
                }
            };
        };
    }
</script>

<div
    x-data="offlineIndicatorHandler()"
    class="pointer-events-none fixed inset-x-0 top-0 z-[9999] select-none"
    x-cloak
>
    <!-- Top Ambient Offline Status Bar -->
    <div
        x-show="isOffline"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="w-full h-1 bg-gradient-to-r from-[#EF4444] via-[#F59E0B] to-[#EF4444] shadow-xs"
    ></div>

    <!-- Top Right Notification Container -->
    <div class="fixed top-4 right-4 sm:top-6 sm:right-6 flex flex-col gap-3 max-w-sm w-[calc(100vw-2rem)] sm:w-full">
        
        <!-- Offline Notification Popup Card -->
        <div
            x-show="showOfflineNotification"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
            :class="{ 'animate-shake': shake }"
            class="pointer-events-auto bg-white border border-[#FECACA] rounded-2xl shadow-[0_10px_25px_-5px_rgba(239,68,68,0.15),0_8px_10px_-6px_rgba(0,0,0,0.05)] p-4 flex items-start gap-3.5"
            role="alert"
        >
            <!-- Wifi Off Icon Badge -->
            <div class="w-10 h-10 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] flex items-center justify-center shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M1 9l2.42 2.42M8.5 8.5a13.9 13.9 0 0112.5.5M5.05 13.05a10 10 0 0110.9-1.05M10.7 18.7a4 4 0 012.6 0M12 20h.01" />
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <h4 class="text-[15px] font-bold text-[#0F172A] leading-tight">No Internet Connection</h4>
                    </div>
                </div>

                <p class="text-[13px] text-[#64748B] font-medium leading-relaxed mt-1">
                    You are currently offline. Actions and changes cannot be processed until your connection is restored.
                </p>

                <!-- Actions Row -->
                <div class="flex items-center gap-2 mt-2.5">
                    <button
                        type="button"
                        @click="checkConnection()"
                        :disabled="isChecking"
                        class="h-7 px-2.5 bg-[#EFF6FF] hover:bg-[#DBEAFE] active:scale-[0.98] border border-[#BFDBFE] text-[#102B70] rounded-lg text-[11px] font-bold transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                    >
                        <template x-if="isChecking">
                            <svg class="animate-spin w-3 h-3 text-[#102B70]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!isChecking">
                            <svg class="w-3 h-3 text-[#102B70]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </template>
                        <span x-text="isChecking ? 'Checking...' : 'Retry'"></span>
                    </button>

                    <button
                        type="button"
                        @click="showOfflineNotification = false"
                        class="h-7 px-2 text-[#64748B] hover:text-[#0F172A] text-[11px] font-semibold transition-colors cursor-pointer"
                    >
                        Dismiss
                    </button>
                </div>
            </div>

            <!-- Close Button -->
            <button
                type="button"
                @click="showOfflineNotification = false"
                class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-md shrink-0 cursor-pointer"
                aria-label="Close notification"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Back Online Notification Card -->
        <div
            x-show="showOnlineNotification"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
            class="pointer-events-auto bg-white border border-[#BBF7D0] rounded-2xl shadow-[0_10px_25px_-5px_rgba(16,185,129,0.15),0_8px_10px_-6px_rgba(0,0,0,0.05)] p-3.5 flex items-center gap-3"
            role="status"
        >
            <div class="flex-1 min-w-0">
                <h4 class="text-[14px] font-bold text-[#0F172A] leading-tight">Connection Restored</h4>
                <p class="text-[12px] text-[#15803D] font-semibold mt-0.5">You are back online. All features are active.</p>
            </div>

            <button
                type="button"
                @click="showOnlineNotification = false"
                class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-md shrink-0 cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

    </div>
</div>

<style>
    @keyframes shake-anim {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-6px); }
        40%, 80% { transform: translateX(6px); }
    }
    .animate-shake {
        animation: shake-anim 0.4s ease-in-out;
    }
</style>
