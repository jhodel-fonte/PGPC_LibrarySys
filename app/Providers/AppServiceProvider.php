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

        // Synchronize password reset token expiration with pgpc configuration
        if ($expiration = config('pgpc.email.reset_link_expiration')) {
            config(['auth.passwords.users.expire' => (int) $expiration]);
        }
    }
}
