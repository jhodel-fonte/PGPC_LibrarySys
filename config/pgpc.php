<?php

return [
    'college' => [
        'name' => 'Padre Garcia Polytechnic College',
        'abbreviation' => 'PGPC',
        'address' => 'Brgy. San Roque, Padre Garcia, Batangas, Philippines',

        'programs' => [
            'BSCS' => 'Bachelor of Science in Computer Science',
            'BS Crim' => 'Bachelor of Science in Criminology',
            'BSMA' => 'Bachelor of Science in Management Accounting',
            'BPA' => 'Bachelor of Public Administration',
        ],

    ],

    'email' => [
        'reset_link_expiration' => 30, // in minutes
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@pgpc.edu.ph'),
        'from_name' => env('MAIL_FROM_NAME', 'PGPC Support'),
    ],

    'online_resources' => 'storage/public/online_resources/list.json',

    'accepted_formats' => [
        'member' => [
            'patterns' => [
                '^(?:LIB-|SA-|20\d{2}-)',
            ],
            'keywords' => [
                'member',
                'student',
            ],
        ],
        'book' => [
            'patterns' => [
                '^\d+$',
                '^(?:BK-|ISBN-|ACC-)',
            ],
        ],
    ],

    'qrcode_customization' => [
        'theme' => [
            'primary_color' => '#102B70',
            'background_color' => '#FFFFFF',
            'accent_color' => '#FCC719',
        ],
        'display' => [
            'size' => 300,
            'margin' => 2,
            'error_correction_level' => 'H',
            'format' => 'png',
        ],
        'branding' => [
            'embed_logo' => true,
            'logo_path' => 'logo.ico',
            'logo_size_percent' => 20,
        ],
    ],
];
