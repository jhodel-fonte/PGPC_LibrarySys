@props([
    'type' => null,
    'title' => null,
    'message' => null,
    'dismissible' => true,
])

@php
    // Determine default server-side state if not explicitly passed
    $initialHasError = $errors->any() || session('error');
    $initialHasSuccess = session('status') || session('success');

    $defaultType = $type ?? ($initialHasError ? 'error' : ($initialHasSuccess ? 'success' : 'info'));
    $defaultTitle = $title ?? ($initialHasError ? "We couldn't sign you in." : ($initialHasSuccess ? 'Success' : 'Notice'));
    $defaultMessage = $message ?? ($errors->first() ?: (session('status') ?: (session('success') ?: (session('error') ?: ''))));
    $initialShow = ($type !== null || $message !== null || $initialHasError || $initialHasSuccess) && ! empty($defaultMessage);
@endphp

<div
    x-data="{
        show: {{ $initialShow ? 'true' : 'false' }},
        type: '{{ $defaultType }}',
        title: '{{ addslashes($defaultTitle) }}',
        message: '{{ addslashes($defaultMessage) }}',
        dismiss() {
            this.show = false;
        },
        trigger(type, title, message) {
            this.type = type || 'error';
            this.title = title || (this.type === 'error' ? 'We couldn\'t sign you in.' : 'Notice');
            this.message = message || '';
            this.show = Boolean(this.message);
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-250 transform"
    x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
    x-on:auth-response.window="trigger($event.detail.type, $event.detail.title, $event.detail.message)"
    x-on:auth-error.window="trigger('error', $event.detail.title || 'We couldn\'t sign you in.', $event.detail.message || $event.detail)"
    x-on:auth-success.window="trigger('success', $event.detail.title || 'Success', $event.detail.message || $event.detail)"
    x-cloak
    role="alert"
    class="mb-6 flex items-start gap-3 rounded-2xl border p-4 text-sm shadow-xs transition-all duration-200"
    :class="{
        'border-red-200 bg-red-50 text-red-700': type === 'error',
        'border-emerald-200 bg-emerald-50 text-emerald-800': type === 'success',
        'border-[#BFDBFE] bg-[#EFF6FF] text-[#102B70]': type === 'info' || type === 'status',
        'border-amber-200 bg-amber-50 text-amber-800': type === 'warning'
    }"
    {{ $attributes }}
>
    <!-- Error Icon -->
    <template x-if="type === 'error'">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3l-6.93-12a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z" />
        </svg>
    </template>

    <!-- Success Icon -->
    <template x-if="type === 'success'">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </template>

    <!-- Info / Status Icon -->
    <template x-if="type === 'info' || type === 'status'">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#102B70]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </template>

    <!-- Warning Icon -->
    <template x-if="type === 'warning'">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </template>

    <!-- Message Content Area -->
    <div class="min-w-0 flex-1">
        <template x-if="title">
            <p class="font-bold leading-snug" x-text="title"></p>
        </template>
        <p class="mt-0.5 leading-relaxed text-[13px] opacity-90" x-text="message"></p>
        {{ $slot }}
    </div>

    <!-- Dismiss Button -->
    @if ($dismissible)
        <button
            type="button"
            @click="dismiss()"
            class="-mr-1 -mt-1 p-1 rounded-xl hover:bg-black/5 focus:outline-none transition-colors shrink-0"
            aria-label="Dismiss alert"
        >
            <svg class="h-4 w-4 opacity-50 hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>

