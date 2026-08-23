public function register(): void
{
    // Register Telescope only if the class exists (local environment)
    if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(\App\Providers\TelescopeServiceProvider::class);
    }
}
