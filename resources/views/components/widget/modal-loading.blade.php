@props(['target', 'title'])

<div wire:loading.flex wire:target="{{ $target }}" class="fixed inset-0 z-50 items-center justify-center bg-[#071943]/60 p-3 sm:p-4">
    <div role="status" aria-live="polite" aria-busy="true" class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:p-7">
        <h3 class="text-xl font-bold text-[#102B70]">{{ $title }}</h3>
        <p class="mt-1 text-sm text-slate-600">Loading details…</p>
        <div aria-hidden="true" class="mt-6 space-y-4 motion-safe:animate-pulse">
            <div class="h-24 rounded-xl bg-slate-100"></div>
            <div class="h-4 w-2/3 rounded bg-slate-100"></div>
            <div class="h-12 rounded-xl bg-slate-100"></div>
            <div class="h-12 rounded-xl bg-slate-100"></div>
        </div>
    </div>
</div>
