@props(['overdueCount' => 0, 'pendingCount' => 0])

@php
    $routePrefix = \App\Services\EmployeeRoleService::getRoutePrefix();
    $overdueLink = route($routePrefix . 'circulation-desk.borrowed-books', ['filter' => 'overdue']);
    $reservationsLink = route($routePrefix . 'circulation-desk.index');
@endphp

<section class="h-full rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] flex flex-col justify-between min-h-[220px]">
    <!-- Header -->
    <div class="flex items-center justify-between mb-2">
        <div>
            <h2 class="text-base font-bold text-slate-900 leading-tight">
                Requires Attention
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Items requiring action
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
            <span class="w-1.5 h-1.5 rounded-full {{ ($overdueCount + $pendingCount) > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
            {{ $overdueCount + $pendingCount }} total
        </span>
    </div>

    <!-- Alert Items (Centered with comfortable breathing room) -->
    <div class="my-auto py-2 space-y-2.5">
        <!-- Overdue Items Alert -->
        <a href="{{ $overdueLink }}" wire:navigate class="group block">
            <div class="flex items-center justify-between gap-3 rounded-xl border border-red-100 bg-red-50/60 p-3 transition-all hover:bg-red-50 hover:shadow-xs">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 transition-transform group-hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[14px] font-semibold text-red-900 leading-snug group-hover:text-red-950 transition-colors">
                            Overdue Items
                        </span>
                        <span class="block text-[11px] text-red-600/80 font-medium">Past return deadline</span>
                    </div>
                </div>
                <span class="text-xl font-bold tabular-nums text-red-700 shrink-0">
                    {{ $overdueCount }}
                </span>
            </div>
        </a>

        <!-- Pending Reservations Alert -->
        <a href="{{ $reservationsLink }}" wire:navigate class="group block">
            <div class="flex items-center justify-between gap-3 rounded-xl border border-amber-100 bg-amber-50/60 p-3 transition-all hover:bg-amber-50 hover:shadow-xs">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 transition-transform group-hover:scale-105">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[14px] font-semibold text-amber-900 leading-snug group-hover:text-amber-950 transition-colors">
                            Pending Reservations
                        </span>
                        <span class="block text-[11px] text-amber-600/80 font-medium">Awaiting fulfillment</span>
                    </div>
                </div>
                <span class="text-xl font-bold tabular-nums text-amber-700 shrink-0">
                    {{ $pendingCount }}
                </span>
            </div>
        </a>
    </div>

    <!-- Footer -->
    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
        <span class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Updated live
        </span>
        <span class="font-semibold text-[#102B70]">Circulation Desk &rarr;</span>
    </div>
</section>
