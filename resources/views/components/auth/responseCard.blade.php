@props([
    'type' => null,
    'title' => null,
    'message' => null,
    'dismissible' => true,
    'countdown' => null,
    'autoDismiss' => false,
    'timeout' => null,
])

@php
    // Determine default server-side state if not explicitly passed
    $initialHasError = $errors->any() || session('error');
    $initialHasSuccess = session('status') || session('success');

    $defaultType = $type ?? ($initialHasError ? 'error' : ($initialHasSuccess ? 'success' : 'info'));
    $defaultTitle = $title ?? ($initialHasError ? "We couldn't sign you in." : ($initialHasSuccess ? 'Success' : 'Notice'));
    $defaultMessage = $message ?? ($errors->first() ?: (session('status') ?: (session('success') ?: (session('error') ?: ''))));
    $initialShow = ($type !== null || $message !== null || $initialHasError || $initialHasSuccess) && ! empty($defaultMessage);
    $initialTimeout = $timeout ? (int)$timeout : ($autoDismiss ? 5000 : null);
@endphp

<div
    x-data="{
        show: {{ $initialShow ? 'true' : 'false' }},
        type: {{ json_encode($defaultType) }},
        title: {{ json_encode($defaultTitle) }},
        rawMessage: {{ json_encode($defaultMessage) }},
        message: {{ json_encode($defaultMessage) }},
        timerInterval: null,
        autoDismissTimer: null,
        autoDismissRemaining: 0,
        autoDismissStart: 0,
        autoDismissDuration: {{ $initialTimeout ? (int)$initialTimeout : 'null' }},
        remainingSeconds: 0,
        hasCountdown: false,
        messageTemplate: null,

        init() {
            if (this.show && this.message) {
                this.setupCountdown(this.message, {{ $countdown ? (int)$countdown : 'null' }});
                if (this.autoDismissDuration) {
                    this.startAutoDismiss(this.autoDismissDuration);
                }
            }
            if (typeof this.$cleanup === 'function') {
                this.$cleanup(() => {
                    this.clearTimer();
                    this.clearAutoDismiss();
                });
            }
        },

        clearTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        clearAutoDismiss() {
            if (this.autoDismissTimer) {
                clearTimeout(this.autoDismissTimer);
                this.autoDismissTimer = null;
            }
        },

        startAutoDismiss(duration) {
            this.clearAutoDismiss();
            let ms = Number(duration);
            if (isNaN(ms) || ms <= 0) return;
            // If passed in seconds (e.g., 5 instead of 5000), convert to ms
            if (ms < 100) {
                ms = ms * 1000;
            }
            this.autoDismissDuration = ms;
            this.autoDismissRemaining = ms;
            this.autoDismissStart = Date.now();

            this.autoDismissTimer = setTimeout(() => {
                this.dismiss();
            }, ms);
        },

        pauseAutoDismiss() {
            if (this.autoDismissTimer && this.autoDismissRemaining > 0) {
                clearTimeout(this.autoDismissTimer);
                this.autoDismissTimer = null;
                const elapsed = Date.now() - this.autoDismissStart;
                this.autoDismissRemaining = Math.max(0, this.autoDismissRemaining - elapsed);
            }
        },

        resumeAutoDismiss() {
            if (!this.autoDismissTimer && this.autoDismissRemaining > 0 && this.show) {
                this.autoDismissStart = Date.now();
                this.autoDismissTimer = setTimeout(() => {
                    this.dismiss();
                }, this.autoDismissRemaining);
            }
        },

        dismiss() {
            this.clearTimer();
            this.clearAutoDismiss();
            this.show = false;
        },

        resolvePayload(detail) {
            let data = detail;
            if (Array.isArray(detail) && detail.length > 0) {
                data = detail[0];
            }
            if (typeof data === 'string') {
                return { title: null, message: data, type: null, countdown: null, autoDismiss: null, timeout: null };
            }
            if (typeof data === 'object' && data !== null) {
                const autoDismissVal = data.autoDismiss !== undefined ? data.autoDismiss : (data.auto_dismiss !== undefined ? data.auto_dismiss : (data.dismiss !== undefined ? data.dismiss : null));
                const timeoutVal = data.timeout !== undefined ? data.timeout : (data.duration !== undefined ? data.duration : null);
                return {
                    title: data.title || null,
                    message: (typeof data.message === 'string') ? data.message : (typeof data.text === 'string' ? data.text : null),
                    type: data.type || null,
                    countdown: data.countdown !== undefined ? Number(data.countdown) : (data.seconds !== undefined ? Number(data.seconds) : null),
                    autoDismiss: autoDismissVal,
                    timeout: timeoutVal
                };
            }
            return { title: null, message: null, type: null, countdown: null, autoDismiss: null, timeout: null };
        },

        trigger(type, title, message, explicitCountdown = null, autoDismissOption = null, timeoutOption = null) {
            this.clearTimer();
            this.clearAutoDismiss();
            this.type = type || 'error';
            this.title = title || '';
            this.rawMessage = (typeof message === 'string') ? message : '';
            this.message = this.rawMessage;
            this.show = Boolean(this.title || this.message);

            if (this.show) {
                this.setupCountdown(this.rawMessage, explicitCountdown);

                let duration = null;
                if (timeoutOption) {
                    duration = timeoutOption;
                } else if (autoDismissOption === true || (autoDismissOption === null && {{ $autoDismiss ? 'true' : 'false' }})) {
                    duration = {{ $timeout ? (int)$timeout : 5000 }};
                }

                if (duration) {
                    this.startAutoDismiss(duration);
                }
            }
        },

        setupCountdown(msg, explicitSeconds) {
            let seconds = (typeof explicitSeconds === 'number' && !isNaN(explicitSeconds) && explicitSeconds > 0) ? explicitSeconds : null;
            this.messageTemplate = null;

            if (msg) {
                const match = msg.match(/(.*?\b)(\d+)\s*(seconds?|secs?|s\b)(.*)/i);
                if (match) {
                    const parsedSecs = parseInt(match[2], 10);
                    if (seconds === null && !isNaN(parsedSecs) && parsedSecs > 0) {
                        seconds = parsedSecs;
                    }
                    this.messageTemplate = {
                        prefix: match[1],
                        suffix: match[4]
                    };
                }
            }

            if (seconds && seconds > 0) {
                this.remainingSeconds = seconds;
                this.hasCountdown = true;
                this.updateDisplayMessage();

                this.timerInterval = setInterval(() => {
                    this.remainingSeconds--;
                    if (this.remainingSeconds <= 0) {
                        this.remainingSeconds = 0;
                        this.updateDisplayMessage();
                        this.clearTimer();
                        this.onCountdownFinished();
                    } else {
                        this.updateDisplayMessage();
                    }
                }, 1000);
            } else {
                this.hasCountdown = false;
                this.remainingSeconds = 0;
                this.message = this.rawMessage;
            }
        },

        updateDisplayMessage() {
            if (!this.hasCountdown) {
                this.message = this.rawMessage;
                return;
            }

            const secText = this.remainingSeconds === 1 ? '1 second' : `${this.remainingSeconds} seconds`;

            if (this.messageTemplate) {
                if (this.remainingSeconds === 0) {
                    this.message = `${this.messageTemplate.prefix}0 seconds${this.messageTemplate.suffix}`;
                } else {
                    this.message = `${this.messageTemplate.prefix}${secText}${this.messageTemplate.suffix}`;
                }
            } else if (this.rawMessage) {
                if (this.remainingSeconds > 0) {
                    this.message = `${this.rawMessage} (${secText} remaining)`;
                } else {
                    this.message = this.rawMessage;
                }
            }
        },

        onCountdownFinished() {
            window.dispatchEvent(new CustomEvent('auth-timer-finished', {
                detail: { type: this.type, title: this.title }
            }));
        }
    }"
    @mouseenter="pauseAutoDismiss()"
    @mouseleave="resumeAutoDismiss()"
    x-show="show"
    x-transition:enter="transition ease-out duration-250 transform"
    x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
    x-on:auth-response.window="
        const p = resolvePayload($event.detail);
        trigger(p.type || 'info', p.title, p.message, p.countdown, p.autoDismiss, p.timeout);
    "
    x-on:auth-error.window="
        const p = resolvePayload($event.detail);
        trigger('error', p.title || 'We couldn\'t sign you in.', p.message, p.countdown, p.autoDismiss, p.timeout);
    "
    x-on:auth-success.window="
        const p = resolvePayload($event.detail);
        trigger('success', p.title || 'Success', p.message, p.countdown, p.autoDismiss !== null ? p.autoDismiss : true, p.timeout);
    "
    x-on:auth-clear.window="dismiss()"
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
        <template x-if="message">
            <p class="mt-0.5 leading-relaxed text-[13px] opacity-90" x-text="message"></p>
        </template>
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
