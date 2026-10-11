<?php

return [

    /*
    |--------------------------------------------------------------------------
    | College & Institution Identity
    |--------------------------------------------------------------------------
    |
    | Core identity details for Padre Garcia Polytechnic College, including
    | official name, acronyms, campus location, and academic programs.
    |
    */
    'college' => [
        'name' => 'Padre Garcia Polytechnic College',
        'tagline' => 'Library System',
        'abbreviation' => 'PGPC',
        'acronym' => 'PGPC',
        'address' => 'Brgy. San Roque, Padre Garcia, Batangas, Philippines',

        'programs' => [
            'BSCS' => 'Bachelor of Science in Computer Science',
            'BS Crim' => 'Bachelor of Science in Criminology',
            'BSMA' => 'Bachelor of Science in Management Accounting',
            'BPA' => 'Bachelor of Public Administration',
        ],
    ],

    // Top-level aliases for UI branding & header components
    'name' => 'Padre Garcia Polytechnic College',
    'tagline' => 'Library System',
    'acronym' => 'PGPC',

    /*
    |--------------------------------------------------------------------------
    | Book Covers & File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Upload parameters, validation limits, base asset URLs, and fallback images
    | for book covers stored in the library catalog.
    |
    */
    'book_covers' => [
        'max_size' => env('COVERFILE_MAX_SIZE', '5MB'),
        'allowed_types' => env('COVERFILE_TYPES', 'png,jpg,jpeg,webp'),
        'url' => env('COVERFILE_URL', '/storage/book_cover/'),
        'default' => 'book-cover.webp',
    ],

    // Backward-compatibility aliases for existing catalog references
    'coverFile_max_size' => env('COVERFILE_MAX_SIZE', '5MB'),
    'coverFile_types' => env('COVERFILE_TYPES', 'png,jpg,jpeg,webp'),
    'coverFile_url' => env('COVERFILE_URL', '/storage/book_cover/'),
    'coverFile_default' => 'book-cover.webp',

    /*
    |--------------------------------------------------------------------------
    | System Email Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for password reset expiration and transactional email headers.
    |
    */
    'email' => [
        'reset_link_expiration' => 30, // in minutes
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@pgpc.edu.ph'),
        'from_name' => env('MAIL_FROM_NAME', 'PGPC Support'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Online Resources File Path
    |--------------------------------------------------------------------------
    |
    | Path to JSON repository index for external digital library resources.
    |
    */
    'online_resources' => 'storage/public/online_resources/list.json',

    /*
    |--------------------------------------------------------------------------
    | Scanner Accepted Formats (Barcodes & QR Codes)
    |--------------------------------------------------------------------------
    |
    | Regular expression patterns and keywords for distinguishing patron IDs
    | from catalog book barcodes in live circulation scanning.
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | QR Code Generator & Theme Customization
    |--------------------------------------------------------------------------
    |
    | Layout and design parameters for generating member/book QR badges.
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | General Schedule Defaults
    |--------------------------------------------------------------------------
    |
    | Default operating hours and closure schedule fallbacks.
    |
    */
    'general' => [
        'operating_hours' => [
            'monday_friday' => '8:00 AM - 5:00 PM',
            'saturday' => '8:00 AM - 12:00 PM',
            'sunday' => 'Closed',
        ],
        'closures' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels & Templates
    |--------------------------------------------------------------------------
    |
    | Default delivery channels and system messaging templates.
    |
    */
    'notifications' => [
        'channels' => ['email', 'in_app'],
        'templates' => [
            'due_reminder' => 'Your borrowed book is due soon.',
            'overdue_notice' => 'Your borrowed book is overdue.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default User Account Generation Rules
    |--------------------------------------------------------------------------
    |
    | Rules for generating default usernames and initial passwords when creating
    | new accounts for students and librarians. Adjusting these rules changes
    | how accounts are auto-generated across the entire system.
    |
    | Supported Pattern Placeholders:
    |   {school_id}  - Student or Librarian ID number (e.g. "23-39114")
    |   {lastname}   - User's Last Name (e.g. "Santos")
    |   {firstname}  - User's First Name (e.g. "Juan")
    |   {email}      - User's Email Address (e.g. "student@pgpc.edu.ph")
    |   {year}       - Current Year (e.g. "2026")
    |
    */
    'default_user_account' => [
        
        // Username generation rules
        'username' => [
            // Strategy: 'school_id' (e.g. 23-39114), 'email', or custom pattern '{school_id}'
            'strategy' => env('DEFAULT_ACCOUNT_USERNAME_STRATEGY', 'school_id'),
            'case' => 'lowercase', // 'lowercase', 'uppercase', or 'original'
            'fallback' => 'email', // fallback attribute if primary ID is not available
        ],

        // Password generation rules
        // Default pattern '{lastname}{school_id}' produces e.g. "santos23-39114"
        'password' => [
            'pattern' => env('DEFAULT_ACCOUNT_PASSWORD_PATTERN', '{lastname}{school_id}'),
            'case' => 'lowercase', // 'lowercase', 'uppercase', 'capitalize', or 'original'
            'strip_spaces' => true, // removes spaces from names (e.g. "Dela Cruz" -> "delacruz")
            'fallback' => env('DEFAULT_ACCOUNT_PASSWORD_FALLBACK', 'Pgpc12345!'), // fallback password
            'force_change_on_first_login' => true, // require password update on initial login
        ],
    ],
];
