<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Limits
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default pagination limits for various sections
    | of the application. This ensures consistency across different views.
    |
    */

    'pagination' => [
        'catalog' => 15,
        'users' => 15,
        'transactions' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported File Extensions
    |--------------------------------------------------------------------------
    |
    | Define the allowed file extensions for various upload types within the
    | system to enforce strict validation rules.
    |
    */

    'uploads' => [
        'book_covers' => ['jpg', 'jpeg', 'png', 'webp'],
        'legal_documents' => ['pdf', 'txt'],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Timezone Enforcement
    |--------------------------------------------------------------------------
    |
    | This enforces the strict timezone for accurate fine calculations and
    | transactional consistency. It should match the app.timezone.
    |
    */

    'timezone' => 'Asia/Manila',

];
