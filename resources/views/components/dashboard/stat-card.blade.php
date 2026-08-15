@props(['title', 'value', 'subtext'])

<div class="bg-white rounded-2xl border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] px-6 py-5 flex flex-col justify-center h-[120px]">
    <h3 class="text-slate-500 text-[11px] font-bold tracking-[0.05em] uppercase mb-1">{{ $title }}</h3>
    <div class="text-[27px] font-bold leading-tight text-slate-900 mb-1">{{ $value }}</div>
    <p class="text-xs font-bold text-slate-500">{{ $subtext }}</p>
</div>
