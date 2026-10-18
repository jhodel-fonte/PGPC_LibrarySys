@props([
    'model' => 'form.turnstileToken',
    'action' => 'login',
    'theme' => 'light',
])

@php
    $siteKey = config('services.turnstile.key');
@endphp

<div
    wire:ignore
    x-data="{
        widgetId: null,
        siteKey: @js($siteKey),
        action: @js($action),
        theme: @js($theme),
        modelName: @js($model),
        initTurnstile() {
            const renderWidget = () => {
                if (!window.turnstile || !this.$refs.turnstileContainer) return;
                if (this.widgetId !== null) {
                    try { window.turnstile.remove(this.widgetId); } catch (e) {}
                    this.widgetId = null;
                }
                try {
                    this.widgetId = window.turnstile.render(this.$refs.turnstileContainer, {
                        sitekey: this.siteKey,
                        action: this.action,
                        theme: this.theme,
                        callback: (token) => {
                            if (typeof $wire !== 'undefined') {
                                $wire.set(this.modelName, token);
                            }
                        },
                        'expired-callback': () => {
                            if (typeof $wire !== 'undefined') {
                                $wire.set(this.modelName, '');
                            }
                        },
                        'error-callback': () => {
                            if (typeof $wire !== 'undefined') {
                                $wire.set(this.modelName, '');
                            }
                        }
                    });
                } catch (err) {
                    console.error('Turnstile render error:', err);
                }
            };

            if (window.turnstile) {
                renderWidget();
            } else {
                const interval = setInterval(() => {
                    if (window.turnstile) {
                        clearInterval(interval);
                        renderWidget();
                    }
                }, 100);
            }
        },
        resetWidget() {
            if (window.turnstile && this.widgetId !== null) {
                try {
                    window.turnstile.reset(this.widgetId);
                    if (typeof $wire !== 'undefined') {
                        $wire.set(this.modelName, '');
                    }
                } catch (e) {}
            }
        }
    }"
    x-init="initTurnstile()"
    x-on:login-failed.window="resetWidget()"
    x-on:livewire:error.window="resetWidget()"
    x-on:turnstile-reset.window="resetWidget()"
    class="flex flex-col items-center justify-center min-h-[65px] my-3 select-none"
>
    <div x-ref="turnstileContainer" class="w-full flex justify-center"></div>
</div>

@error($model)
    <p class="mt-1 text-center text-xs font-medium text-red-600 select-text">{{ $message }}</p>
@enderror

