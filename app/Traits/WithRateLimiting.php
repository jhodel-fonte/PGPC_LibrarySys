<?php

namespace App\Traits;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait WithRateLimiting
{
    /**
     * Rate limit a specific action per authenticated user or IP address.
     *
     * @param string $action A unique key for the action (e.g., 'search', 'save', 'generateBarcode')
     * @param int $maxAttempts Maximum allowed attempts within the decay window
     * @param int $decaySeconds Time window in seconds before attempts reset
     * @throws ValidationException
     */
    protected function rateLimit(string $action, int $maxAttempts = 60, int $decaySeconds = 60): void
    {
        $key = $this->getRateLimitKey($action);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $message = "Too many requests. Please try again in {$seconds} second" . ($seconds === 1 ? '' : 's') . '.';

            // Flash toast notification if Livewire component supports dispatch
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('toast', message: $message, type: 'warning');
            }

            // Add component error bag entry if supported
            if (method_exists($this, 'addError')) {
                $this->addError($action, $message);
            }

            if (app()->bound('session.store') && session()->isStarted()) {
                session()->flash('error', $message);
            }

            throw ValidationException::withMessages([
                $action => $message,
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Clear the rate limiter for a specific action.
     *
     * @param string $action
     * @return void
     */
    protected function clearRateLimit(string $action): void
    {
        RateLimiter::clear($this->getRateLimitKey($action));
    }

    /**
     * Get the remaining attempts for a specific action.
     *
     * @param string $action
     * @param int $maxAttempts
     * @return int
     */
    protected function getRateLimitRemaining(string $action, int $maxAttempts = 60): int
    {
        return RateLimiter::remaining($this->getRateLimitKey($action), $maxAttempts);
    }

    /**
     * Get the number of seconds until the rate limit is reset.
     *
     * @param string $action
     * @return int
     */
    protected function getRateLimitAvailableIn(string $action): int
    {
        return RateLimiter::availableIn($this->getRateLimitKey($action));
    }

    /**
     * Generate a unique rate limiter cache key for this component and user/IP.
     *
     * @param string $action
     * @return string
     */
    protected function getRateLimitKey(string $action): string
    {
        $identifier = auth()->check() ? auth()->id() : (request()->ip() ?? '127.0.0.1');

        return 'rate_limit:' . class_basename($this) . ':' . $action . ':' . $identifier;
    }
}
