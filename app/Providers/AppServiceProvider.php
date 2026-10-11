<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Synchronize password reset token expiration with settings configuration
        if ($expiration = config('settings.email.reset_link_expiration')) {
            config(['auth.passwords.users.expire' => (int) $expiration]);
        }
    }
}
