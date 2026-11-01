<div x-data="{
    toasts: [],
    addToast(message, type = 'success', undoEvent = null, undoPayload = null, duration = null, expireEvent = null, expirePayload = null, variant = 'solid') {
        if (!message) return;
        const id = Date.now() + Math.random();
        const toastDuration = duration ? Number(duration) : (undoEvent ? 10000 : 4000);
        const item = {
            id,
            message,
            type,
            variant,
            undoEvent,
            undoPayload,
            expireEvent,
            expirePayload,
            duration: toastDuration,
            remaining: toastDuration,
            timer: null,
            startTime: Date.now(),
            isPaused: false,
            undone: false,
            expired: false
        };
        item.timer = setTimeout(() => {
            this.expireToast(id);
        }, toastDuration);
        this.toasts.push(item);
    },
    expireToast(id) {
        const item = this.toasts.find(t => t.id === id);
        if (!item) return;
        if (item.timer) {
            clearTimeout(item.timer);
            item.timer = null;
        }
        if (!item.undone && !item.expired) {
            item.expired = true;
            if (item.expireEvent) {
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.dispatch(item.expireEvent, item.expirePayload || {});
                }
                this.$dispatch(item.expireEvent, item.expirePayload || {});
            }
        }
        this.toasts = this.toasts.filter(t => t.id !== id);
    },
    removeToast(id) {
        this.expireToast(id);
    },
    pauseToast(id) {
        const item = this.toasts.find(t => t.id === id);
        if (item && item.timer) {
            clearTimeout(item.timer);
            item.timer = null;
            item.remaining = Math.max(0, item.remaining - (Date.now() - item.startTime));
            item.isPaused = true;
        }
    },
    resumeToast(id) {
        const item = this.toasts.find(t => t.id === id);
        if (item && !item.timer && !item.undone && !item.expired) {
            item.isPaused = false;
            item.startTime = Date.now();
            const rem = item.remaining > 0 ? item.remaining : 1000;
            item.timer = setTimeout(() => {
                this.expireToast(id);
            }, rem);
        }
    },
    handleUndo(t) {
        t.undone = true;
        if (t.timer) {
            clearTimeout(t.timer);
            t.timer = null;
        }
        if (t.undoEvent) {
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch(t.undoEvent, t.undoPayload || {});
            }
            this.$dispatch(t.undoEvent, t.undoPayload || {});
        }
        this.toasts = this.toasts.filter(item => item.id !== t.id);
    }
}"
@toast.window="
    (() => {
        const raw = (Array.isArray($event.detail) ? $event.detail[0] : $event.detail) || {};
        let message = '';
        let type = 'success';
        let undoEvent = null;
        let undoPayload = null;
        let duration = null;
        let expireEvent = null;
        let expirePayload = null;
        let variant = 'solid';

        if (typeof raw === 'string') {
            message = raw;
            type = ($event.detail?.[1] || 'success');
        } else {
            message = raw.message || raw.text || '';
            type = raw.type || 'success';
            undoEvent = raw.undoEvent || null;
            undoPayload = raw.undoPayload || null;
            duration = raw.duration || null;
            expireEvent = raw.expireEvent || null;
            expirePayload = raw.expirePayload || null;
            variant = raw.variant === 'inverted' ? 'inverted' : 'solid';
        }

        if (message) {
            addToast(message, type, undoEvent, undoPayload, duration, expireEvent, expirePayload, variant);
        }
    })()
"
@if(session()->has('successMessage'))
    x-init="addToast('{{ session('successMessage') }}', 'success')"
@elseif(session()->has('status'))
    x-init="addToast('{{ session('status') }}', 'success')"
@elseif(session()->has('errorMessage'))
    x-init="addToast('{{ session('errorMessage') }}', 'error')"
@endif
class="fixed bottom-4 left-1/2 z-[120] flex -translate-x-1/2 flex-col gap-2.5 w-[calc(100%-2rem)] max-w-lg pointer-events-none md:bottom-auto md:left-auto md:right-6 md:top-6 md:translate-x-0"
style="display: none;"
x-show="toasts.length > 0"
>
    <style>
        @keyframes toastCountdownProgress {
            0% { transform: scaleX(1); }
            100% { transform: scaleX(0); }
        }
    </style>

    <template x-for="t in toasts" :key="t.id">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             @mouseenter="pauseToast(t.id)"
             @mouseleave="resumeToast(t.id)"
             role="status" aria-live="polite"
             class="pointer-events-auto relative flex items-start justify-between gap-3 overflow-hidden rounded-xl border px-4 py-4 shadow-lg sm:px-5"
             :class="t.variant === 'inverted'
                 ? { 'bg-white text-slate-900 border-emerald-300': t.type === 'success', 'bg-white text-slate-900 border-red-300': t.type === 'error' || t.type === 'danger', 'bg-white text-slate-900 border-amber-400': t.type === 'warning', 'bg-white text-slate-900 border-blue-300': t.type === 'info' }
                 : { 'bg-emerald-800 text-white border-emerald-800': t.type === 'success', 'bg-red-800 text-white border-red-800': t.type === 'error' || t.type === 'danger', 'bg-amber-800 text-white border-amber-800': t.type === 'warning', 'bg-[#102B70] text-white border-[#102B70]': t.type === 'info' }"

        >
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <!-- Icon based on type -->
                <template x-if="t.type === 'success'">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="t.variant === 'inverted' ? 'bg-emerald-100 text-emerald-800' : 'bg-white/15 text-white'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
                <template x-if="t.type === 'error' || t.type === 'danger'">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="t.variant === 'inverted' ? 'bg-red-100 text-red-800' : 'bg-white/15 text-white'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </template>
                <template x-if="t.type === 'warning'">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="t.variant === 'inverted' ? 'bg-amber-100 text-amber-800' : 'bg-white/15 text-white'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </template>
                <template x-if="t.type === 'info'">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="t.variant === 'inverted' ? 'bg-blue-100 text-[#102B70]' : 'bg-white/15 text-white'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </template>

                <span class="min-w-0 pt-1 text-[0.9375rem] font-semibold leading-6 break-words [overflow-wrap:anywhere]" x-text="t.message"></span>
            </div>

            <!-- Right Side Controls: Undo & Close -->
            <div class="flex items-center gap-2 shrink-0">
                <template x-if="t.undoEvent">
                    <button
                        type="button"
                        @click.stop="handleUndo(t)"
                        class="inline-flex min-h-10 shrink-0 items-center gap-1.5 rounded-lg border border-[#102B70] bg-white px-3 text-sm font-bold text-[#102B70] transition-colors hover:bg-[#102B70] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#102B70]"
                        title="Undo this action"
                    >
                        <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        <span>Undo</span>
                    </button>
                </template>

                <!-- Close Button -->
                <button type="button" @click="removeToast(t.id)" aria-label="Dismiss notification" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" :class="t.variant === 'inverted' ? 'text-slate-700 hover:bg-slate-100 focus-visible:outline-[#102B70]' : 'text-white hover:bg-white/15 focus-visible:outline-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Countdown Bar (For Undoable Toasts) -->
            <template x-if="t.undoEvent">
                <div class="absolute bottom-0 left-0 right-0 h-1 overflow-hidden rounded-b-xl bg-black/10 pointer-events-none">
                    <div class="h-full origin-left" :class="t.variant === 'inverted' ? 'bg-[#102B70]/50' : 'bg-white/70'"
                         :style="{
                             animation: 'toastCountdownProgress ' + (t.duration || 10000) + 'ms linear forwards',
                             animationPlayState: t.isPaused ? 'paused' : 'running'
                         }"
                    ></div>
                </div>
            </template>
        </div>
    </template>
</div>
