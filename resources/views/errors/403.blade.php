<x-layouts.error
    code="403"
    title="Access Restricted"
    bodyTitle="Access Restricted"
    :message="__($exception ? $exception->getMessage() : 'You do not have permission to access this portal or resource.')"
>
    <!-- ACTION BUTTONS -->
    <div class="mt-6 flex flex-wrap items-center justify-center gap-3 w-full">
        @auth
            @php
                $user = auth()->user();
                $roleName = strtolower(str_replace(' ', '', $user->role?->name ?? ''));
                $dashboardUrl = match($roleName) {
                    'admin' => route('admin.dashboard'),
                    'librarian', 'headlibrarian' => route('librarian.dashboard'),
                    default => route('student.dashboard'),
                };
            @endphp
            <a
                href="{{ $dashboardUrl }}"
                class="h-11 px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Go to My Dashboard</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="h-11 px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap"
            >
                <span>Sign In</span>
            </a>
        @endauth

        <a
            href="{{ url('/') }}"
            class="h-11 px-6 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-sm font-bold text-slate-700 shadow-xs transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer whitespace-nowrap"
        >
            <span>Library Home</span>
        </a>
    </div>
</x-layouts.error>
