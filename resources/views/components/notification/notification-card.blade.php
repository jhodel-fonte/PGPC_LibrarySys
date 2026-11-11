<div x-data="{
    open: false, unreadCount: 0,
    show() { this.$dispatch('close-details-drawer'); this.open = true; this.$dispatch('notification-drawer-opened'); this.$nextTick(() => this.$refs.close.focus()); },
    close() { this.$dispatch('notification-drawer-closed'); this.open = false; this.$nextTick(() => this.$refs.bell.focus()); },
    trap(event) {
        const controls = [...this.$refs.panel.querySelectorAll('button, a[href], input, select, textarea, [tabindex]:not([tabindex=&quot;-1&quot;])')].filter(el => !el.disabled && el.offsetParent !== null);
        if (!controls.length) return;
        if (event.shiftKey && document.activeElement === controls[0]) { event.preventDefault(); controls[controls.length - 1].focus(); }
        else if (!event.shiftKey && document.activeElement === controls[controls.length - 1]) { event.preventDefault(); controls[0].focus(); }
    }
}" @notifications-updated.window="unreadCount = $event.detail.unreadCount" @open-details-drawer.window="if (open) { open = false; $dispatch('notification-drawer-closed') }" @keydown.escape.window="if (open) close()" class="relative">
    <button x-ref="bell" type="button" @click="show()" :aria-expanded="open" aria-controls="notification-panel" aria-label="View notifications" class="relative flex min-h-11 min-w-11 items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700 max-md:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
        <span x-cloak x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount" class="absolute -top-1 -right-1 min-w-[18px] rounded-sm bg-slate-700 px-1 text-xs font-bold text-white"></span>
    </button>
    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-[100]" role="dialog" aria-modal="true" aria-labelledby="notification-title" id="notification-panel">
            <div class="absolute inset-0 bg-slate-900/50" @click="close()"></div>
            <aside x-ref="panel" @keydown.tab="trap($event)" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col bg-white shadow-xl motion-reduce:transition-none">
                <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div><h2 id="notification-title" class="text-lg font-bold text-[#102B70]">Notifications</h2><p class="text-sm text-slate-600" x-text="unreadCount + ' unread'"></p></div>
                    <button x-ref="close" type="button" @click="close()" aria-label="Close notifications" class="rounded-md p-2 text-slate-600 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" /></svg></button>
                </header>
                <div class="flex-1 overflow-y-auto"><livewire:components.notification.notification-content /></div>
            </aside>
        </div>
    </template>
</div>
