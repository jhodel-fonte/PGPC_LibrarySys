@php
    $rawMsg = $message ?? ($exception ? $exception->getMessage() : null);
    $isDeactivated = ($errorCode ?? null) === 'account_deactivated'
        || ($exception instanceof \App\Exceptions\AccountDeactivatedException)
        || request()->routeIs('account.deactivated')
        || request()->is('account/deactivated')
        || request()->is('account-deactivated')
        || ($rawMsg && (str_contains(strtolower($rawMsg), 'deactivated') || str_contains(strtolower($rawMsg), 'deleted')));

    $effectiveCode = 401;
    $effectiveType = $isDeactivated ? 'invalid_request_error' : ($errorType ?? 'auth_error');
    $effectiveErrorCode = $isDeactivated ? 'account_deactivated' : ($errorCode ?? 'unauthorized');
    $effectiveTitle = $isDeactivated ? 'Account Deactivated' : ($title ?? 'Unauthorized');
    $effectiveMessage = $isDeactivated
        ? 'You do not have an account because it has been deleted or deactivated.'
        : ($rawMsg ?: 'Sorry, you are not authorized to access this page.');
@endphp

<x-layouts.error
    :code="$effectiveCode"
    :title="$effectiveTitle"
    :bodyTitle="$effectiveTitle"
    :message="null"
>
    @if($isDeactivated)
        <!-- ERROR BADGES -->
        <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ">
                <span>401</span>
                <span class="text-rose-300">·</span>
                <span class="font-mono">{{ $effectiveType }}</span>
            </span>

            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-slate-600 font-mono text-xs font-semibold ">
                <strong class="text-rose-700">{{ $effectiveErrorCode }}</strong>
            </span>
        </div>

        <!-- DEACTIVATED NOTICE CARD -->
        <div class="mt-5 w-full max-w-md rounded-2xl border border-rose-200 bg-rose-50/70 p-4 sm:p-5 text-center shadow-xs space-y-2.5">

            <p class="font-bold text-sm sm:text-base text-rose-950 leading-snug">
                {{ $effectiveMessage }}
            </p>

            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                Your account privileges have been suspended or soft-deleted by the library administration. If you believe this is an error, please visit the library or contact the system administrator to re-enable your account.
            </p>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3 w-full">
            <a
                href="{{ route('login') }}"
                class="h-11 px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Back to Sign In</span>
            </a>

            <a
                href="{{ url('/') }}"
                class="h-11 px-6 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-sm font-bold text-slate-700 shadow-xs transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer whitespace-nowrap"
            >
                <span>Library Home</span>
            </a>
        </div>
    @else
        <p class="mt-2.5 sm:mt-3.5 max-w-md text-sm sm:text-base leading-relaxed text-slate-900">
            {{ $effectiveMessage }}
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3 w-full">
            <a
                href="{{ route('login') }}"
                class="h-11 px-6 rounded-2xl bg-[#102B70] hover:bg-[#0B225E] text-sm font-bold text-white shadow-sm transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer whitespace-nowrap"
            >
                <span>Sign In</span>
            </a>
            <a
                href="{{ url('/') }}"
                class="h-11 px-6 rounded-2xl border border-[#CBD5E1] bg-white hover:bg-slate-50 text-sm font-bold text-slate-700 shadow-xs transition active:scale-[0.98] inline-flex items-center justify-center cursor-pointer whitespace-nowrap"
            >
                <span>Home</span>
            </a>
        </div>
    @endif
</x-layouts.error>
