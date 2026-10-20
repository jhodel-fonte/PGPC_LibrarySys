<div x-data="{
    toasts: [],
    addToast(message, type = 'success', undoEvent = null, undoPayload = null, duration = null) {
        if (!message) return;
        const id = Date.now() + Math.random();
        const toastDuration = duration ? Number(duration) : (undoEvent ? 10000 : 4000);
        const timer = setTimeout(() => { this.removeToast(id); }, toastDuration);
        this.toasts.push({
            id,
            message,
            type,
            undoEvent,
            undoPayload,
            duration: toastDuration,
            timer,
            isPaused: false
        });
    },
    removeToast(id) {
        const item = this.toasts.find(t => t.id === id);
        if (item && item.timer) {
            clearTimeout(item.timer);
        }
        this.toasts = this.toasts.filter(t => t.id !== id);
    },
    pauseToast(id) {
        const item = this.toasts.find(t => t.id === id);
        if (item && item.timer) {
            clearTimeout(item.timer);
            item.timer = null;
            item.isPaused = true;
        }
    },
    resumeToast(id) {
        const item = this.toasts.find(t => t.id === id);
        if (item && !item.timer) {
            item.isPaused = false;
            item.timer = setTimeout(() => { this.removeToast(id); }, 5000);
        }
    },
    handleUndo(t) {
        if (t.undoEvent) {
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch(t.undoEvent, t.undoPayload || {});
            }
            this.$dispatch(t.undoEvent, t.undoPayload || {});
        }
        this.removeToast(t.id);
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

        if (typeof raw === 'string') {
            message = raw;
            type = ($event.detail?.[1] || 'success');
        } else {
            message = raw.message || raw.text || '';
            type = raw.type || 'success';
            undoEvent = raw.undoEvent || null;
            undoPayload = raw.undoPayload || null;
            duration = raw.duration || null;
        }

        if (message) {
            addToast(message, type, undoEvent, undoPayload, duration);
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
class="fixed bottom-6 right-6 z-[120] flex flex-col gap-2.5 max-w-md w-full pointer-events-none px-4"
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
             class="pointer-events-auto relative overflow-hidden flex items-center justify-between gap-3 p-4 rounded-2xl shadow-xl border backdrop-blur-sm text-sm font-semibold transition-all select-none"
             :class="{
                 'bg-emerald-50/95 border-emerald-200 text-emerald-900': t.type === 'success',
                 'bg-red-50/95 border-red-200 text-red-900': t.type === 'error' || t.type === 'danger',
                 'bg-amber-50/95 border-amber-200 text-amber-900': t.type === 'warning',
                 'bg-[#EFF6FF]/95 border-[#BFDBFE] text-[#102B70]': t.type === 'info'
             }"
        >
            <div class="flex items-center gap-3 min-w-0 pr-1">
                <!-- Icon based on type -->
                <template x-if="t.type === 'success'">
                    <div class="h-8 w-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
                <template x-if="t.type === 'error' || t.type === 'danger'">
                    <div class="h-8 w-8 rounded-xl bg-red-100 text-red-700 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </template>
                <template x-if="t.type === 'warning'">
                    <div class="h-8 w-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </template>
                <template x-if="t.type === 'info'">
                    <div class="h-8 w-8 rounded-xl bg-[#DBEAFE] text-[#102B70] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </template>

                <span class="text-xs font-semibold leading-snug break-words" x-text="t.message"></span>
            </div>

            <!-- Right Side Controls: Undo & Close -->
            <div class="flex items-center gap-2 shrink-0">
                <template x-if="t.undoEvent">
                    <button
                        type="button"
                        @click.stop="handleUndo(t)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-[#102B70] text-[#102B70] hover:text-white border border-[#102B70]/20 hover:border-[#102B70] active:scale-95 font-bold text-xs transition-all shadow-sm shrink-0 cursor-pointer select-none"
                        title="Undo this action"
                    >
                        <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        <span>Undo</span>
                    </button>
                </template>

                <!-- Close Button -->
                <button type="button" @click="removeToast(t.id)" class="text-slate-400 hover:text-slate-700 font-bold p-1 rounded-lg transition-colors shrink-0 select-none cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Countdown Bar (For Undoable Toasts) -->
            <template x-if="t.undoEvent">
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-200/50 rounded-b-2xl overflow-hidden pointer-events-none">
                    <div class="h-full bg-[#102B70]/40 origin-left"
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
