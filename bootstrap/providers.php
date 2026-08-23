<?php

return [
    App\Providers\AppServiceProvider::class,
    env('APP_ENV') == 'local' ? App\Providers\TelescopeServiceProvider::class : null,
    App\Providers\VoltServiceProvider::class,
];
