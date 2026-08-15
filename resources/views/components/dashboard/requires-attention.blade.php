@props(['overdueCount' => 0, 'pendingCount' => 0])

<section class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_1px_3px_rgba(15,23,42,0.03)] lg:p-6">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-slate-900">
            Requires Attention
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            Items that may need librarian action
        </p>
    </div>

    <div class="space-y-3">
        <!-- Overdue Items Alert -->
        <a href="#" class="group block">
            <div class="flex min-h-[60px] items-center gap-3 rounded-xl border border-red-100 bg-red-50/50 px-4 transition-colors hover:bg-red-50">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span class="flex-1 text-sm font-semibold text-red-700 group-hover:text-red-800 transition-colors">
                    Overdue Items
                </span>
                <span class="text-lg font-bold tabular-nums text-red-700">
                    {{ $overdueCount }}
                </span>
            </div>
        </a>

        <!-- Pending Reservations Alert -->
        <a href="#" class="group block">
            <div class="flex min-h-[60px] items-center gap-3 rounded-xl border border-amber-100 bg-amber-50/50 px-4 transition-colors hover:bg-amber-50">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="flex-1 text-sm font-semibold text-amber-700 group-hover:text-amber-800 transition-colors">
                    Pending Reservations
                </span>
                <span class="text-lg font-bold tabular-nums text-amber-700">
                    {{ $pendingCount }}
                </span>
            </div>
        </a>
    </div>
</section>
