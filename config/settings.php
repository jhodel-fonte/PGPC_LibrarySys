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
        'library_address' => 'Main Building, 2nd Floor Library',

        'programs' => [
            'BSCS' => 'Bachelor of Science in Computer Science',
            'BS Crim' => 'Bachelor of Science in Criminology',
            'BSMA' => 'Bachelor of Science in Management Accounting',
            'BPA' => 'Bachelor of Public Administration',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Library Configuration & Circulation Parameters
    |--------------------------------------------------------------------------
    |
    | Central configuration for loan durations, penalty fees, borrowing limits,
    | operating schedules, and physical library facility location.
    |
    */
    'library_config' => [
        'loan_period' => 5, // extendable (days)
        'penalty_fee' => 50, // pesos per day
        'max_loan_limit' => 3, // max active loans per student
        'desk_hours' => '8:00 AM – 5:00 PM',
        'operating_days' => 'Mon - Fri',
        'location' => 'Main Building, 2nd Floor Library',
    ],

    // Top-level aliases for UI branding & circulation components
    'name' => 'Padre Garcia Polytechnic College',
    'tagline' => 'Library System',
    'acronym' => 'PGPC',
    'library_location' => 'Main Building, 2nd Floor Library',
    'loan_period' => 5,
    'penalty_fee' => 50,

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

    'profile_image_dir' => [
        'student' => env('STUDENT_PROFILE_IMAGE_DIR', '/storage/image/profile/student/'),
        'librarian' => env('LIBRARIAN_PROFILE_IMAGE_DIR', '/storage/image/profile/librarian/'),
        'default' => [
            'male' => 'images/default-male.webp',
            'female' => 'images/default-female.webp',
        ],
    ],
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
    | iProgSMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and endpoint for sending transactional SMS notifications.
    |
    */
    'sms' => [
        'provider'   => 'iprogsms',
        'api_token'  => env('IPROG_SMS_API_TOKEN', env('IPROGSMS_API_TOKEN', env('SMS_API_TOKEN', ''))),
        'api_url'    => env('IPROG_SMS_URL', 'https://www.iprogsms.com/api/v1/sms_messages'),
        'enabled'    => env('SMS_ENABLED', true),
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
            'primary_color' => env('QRCODE_PRIMARY_COLOR', '#102B70'),
            'background_color' => env('QRCODE_BG_COLOR', '#FFFFFF'),
            'accent_color' => env('QRCODE_ACCENT_COLOR', '#FCC719'),
        ],
        'display' => [
            'size' => 400,
            'margin' => 1,
            'error_correction_level' => 'H', // 'L', 'M', 'Q', 'H'
            'format' => 'png', // 'svg', 'png'
            'style' => 'square', // 'square', 'round', 'dot'
            'eye_style' => 'square', // 'square', 'circle', 'pointy'
        ],
        'branding' => [
            'embed_logo' => true,
            'logo_path' => 'images/logo.svg',
            'logo_size_percent' => 30, // 15 to 30% recommended
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
    | Default User Account Generation Rules (Students & Patrons)
    |--------------------------------------------------------------------------
    |
    | Rules for generating default usernames and initial passwords when creating
    | new accounts for students. Adjusting these rules changes how accounts
    | are auto-generated across the entire system.
    |
    | Supported Pattern Placeholders:
    |   {id}         - Student ID number (e.g. "23-1234")
    |   {school_id}  - Alias for Student ID number (e.g. "23-1234")
    |   {lastname}   - User's Last Name (e.g. "Santos")
    |   {firstname}  - User's First Name (e.g. "Juan")
    |   {email}      - User's Email Address (e.g. "student@pgpc.edu.ph")
    |   {year}       - Current Year (e.g. "2026")
    |
    */
    'default_user_account' => [
        
        // Username generation rules
        'username' => [
            // Strategy: 'school_id' (e.g. 23-1234), 'id', 'email', or custom pattern '{id}'
            'strategy' => env('DEFAULT_ACCOUNT_USERNAME_STRATEGY', 'school_id'),
            'case' => 'lowercase', // 'lowercase', 'uppercase', or 'original'
            'fallback' => 'email', // fallback attribute if primary ID is not available
        ],

        // Password generation rules
        // Default pattern '{lastname}{id}' produces e.g. "santos23-1234"
        'password' => [
            'pattern' => env('DEFAULT_ACCOUNT_PASSWORD_PATTERN', '{lastname}{id}'),
            'case' => 'lowercase', // 'lowercase', 'uppercase', 'capitalize', or 'original'
            'strip_spaces' => true, // removes spaces from names (e.g. "Dela Cruz" -> "delacruz")
            'fallback' => env('DEFAULT_ACCOUNT_PASSWORD_FALLBACK', 'Pgpc12345!'), // fallback password
            'force_change_on_first_login' => true, // require password update on initial login
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Librarian Account Generation Rules (Library Staff & Head Librarians)
    |--------------------------------------------------------------------------
    |
    | Rules for generating default usernames and initial passwords when creating
    | new accounts for library staff and librarians.
    |
    | Supported Pattern Placeholders:
    |   {id}           - Librarian Employee ID number (e.g. "LIB-2026-001" or "23-1234")
    |   {school_id}    - Alias for Librarian Employee ID number
    |   {employee_id}  - Alias for Librarian Employee ID number
    |   {lastname}     - Staff Last Name (e.g. "Santos")
    |   {firstname}    - Staff First Name (e.g. "Juan")
    |   {email}        - Staff Email Address (e.g. "librarian@pgpc.edu.ph")
    |   {year}         - Current Year (e.g. "2026")
    |
    */
    'default_librarian_account' => [
        
        // Username generation rules
        'username' => [
            // Strategy: 'school_id' (e.g. LIB-2026-001), 'id', 'email', or custom pattern '{id}'
            'strategy' => env('DEFAULT_LIBRARIAN_USERNAME_STRATEGY', 'school_id'),
            'case' => 'lowercase', // 'lowercase', 'uppercase', or 'original'
            'fallback' => 'email', // fallback attribute if primary ID is not available
        ],

        // Password generation rules
        // Default pattern '{lastname}{id}' produces e.g. "santos23-1234"
        'password' => [
            'pattern' => env('DEFAULT_LIBRARIAN_PASSWORD_PATTERN', '{lastname}{id}'),
            'case' => 'lowercase', // 'lowercase', 'uppercase', 'capitalize', or 'original'
            'strip_spaces' => true, // removes spaces from names (e.g. "Dela Cruz" -> "delacruz")
            'fallback' => env('DEFAULT_LIBRARIAN_PASSWORD_FALLBACK', 'PgpcLib12345!'), // fallback password
            'force_change_on_first_login' => true, // require password update on initial login
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Librarian Role Permissions
    |--------------------------------------------------------------------------
    |
    | Defines the default set of permissions assigned when creating a
    | Librarian or Head Librarian account. Matches names in permissions table.
    |
    */
    'librarian_role_permission' => [
        'librarian_head' => [
            'manage_users',
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'manage_librarian',
            'manage_catalog',
            'view_catalog',
            'create_book',
            'edit_book',
            'delete_book',
            'manage_circulation',
            'manage_fines',
            'manage_system',
            'view_reports',
        ],
        'head_librarian' => [
            'manage_users',
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            'manage_librarian',
            'manage_catalog',
            'view_catalog',
            'create_book',
            'edit_book',
            'delete_book',
            'manage_circulation',
            'manage_fines',
            'manage_system',
            'view_reports',
        ],
        'librarian' => [
            'manage_users',
            'view_students',
            'create_students',
            'edit_students',
            'manage_catalog',
            'view_catalog',
            'create_book',
            'edit_book',
            'manage_circulation',
            'manage_fines',
            'view_reports',
        ],
    ],
];
