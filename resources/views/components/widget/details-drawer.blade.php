<style>[x-cloak] { display: none !important; }</style>
<div x-data="{
        open: false, title: '', fields: {}, sections: {}, trigger: null,
        show(detail) {
            this.trigger = document.activeElement;
            this.title = detail.title || 'Details';
            this.fields = detail.fields || {};
            this.sections = detail.sections || {};
            this.open = true;
            this.$nextTick(() => this.$refs.close.focus());
        },
        close() { this.open = false; this.$nextTick(() => this.trigger?.focus()); },
        format(value) { return value == null ? '—' : typeof value === 'object' ? JSON.stringify(value, null, 2) : String(value); },
        trap(event) {
            const controls = [...this.$refs.panel.querySelectorAll('button, a[href], input, select, textarea, [tabindex]:not([tabindex=&quot;-1&quot;])')].filter(el => !el.disabled && el.offsetParent !== null);
            if (!controls.length) return;
            if (event.shiftKey && document.activeElement === controls[0]) { event.preventDefault(); controls[controls.length - 1].focus(); }
            else if (!event.shiftKey && document.activeElement === controls[controls.length - 1]) { event.preventDefault(); controls[0].focus(); }
        }
    }" @open-details-drawer.window="show($event.detail)" @close-details-drawer.window="if (open) open = false" @keydown.escape.window="if (open) close()" x-cloak x-show="open" class="fixed inset-0 z-[100]" role="dialog" aria-modal="true" aria-labelledby="details-drawer-title">
    <div class="absolute inset-0 bg-slate-900/50" x-show="open" x-transition.opacity @click="close()"></div>
    <aside x-ref="panel" @keydown.tab="trap($event)" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col bg-white shadow-xl motion-reduce:transition-none">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
            <h2 id="details-drawer-title" class="text-lg font-bold text-[#102B70]" x-text="title"></h2>
            <button x-ref="close" type="button" @click="close()" class="rounded-md p-2 text-slate-600 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700" aria-label="Close details">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
        </header>
        <div class="flex-1 space-y-7 overflow-y-auto p-6">
            <dl class="space-y-4"><template x-for="(value, label) in fields" :key="label"><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-600" x-text="label"></dt><dd class="mt-1 break-words text-sm text-slate-900" x-text="format(value)"></dd></div></template></dl>
            <template x-for="(values, label) in sections" :key="label"><section><h3 class="border-b border-slate-200 pb-2 text-sm font-bold text-[#102B70]" x-text="label"></h3><template x-if="values && Object.keys(values).length"><dl class="mt-4 space-y-4"><template x-for="(value, name) in values" :key="name"><div><dt class="text-xs font-semibold text-slate-600" x-text="name.replaceAll('_', ' ')"></dt><dd class="mt-1 whitespace-pre-wrap break-words text-sm text-slate-900" x-text="format(value)"></dd></div></template></dl></template><p x-show="!values || !Object.keys(values).length" class="mt-4 text-sm text-slate-600">No values recorded.</p></section></template>
        </div>
    </aside>
</div>
