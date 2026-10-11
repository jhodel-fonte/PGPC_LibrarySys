<?php

/*
|--------------------------------------------------------------------------
| PGPC Library Configuration (Backward Compatibility Bridge)
|--------------------------------------------------------------------------
|
| Configuration has been unified and moved into config/settings.php.
| This file delegates directly to settings.php so existing calls
| continue to resolve without issue.
|
*/

return require __DIR__ . '/settings.php';
