<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    env('APP_ENV') == 'local' ? App\Providers\TelescopeServiceProvider::class : null,
];
