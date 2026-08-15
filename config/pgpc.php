<?php

return [
    'general' => [
        'library_name' => 'Padre Garcia Polytechnic College Library',
        'email' => 'library@pgpc.edu.ph',
        'phone' => '+63 912 111 6789',
        
        'operating_hours' => [
            'monday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
            'tuesday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
            'wednesday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
            'thursday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
            'friday' => ['status' => 'Open', 'opens' => '08:00', 'closes' => '17:00'],
            'saturday' => ['status' => 'Closed', 'opens' => null, 'closes' => null],
            'sunday' => ['status' => 'Closed', 'opens' => null, 'closes' => null],
        ],
        
        'closures' => [
            ['date' => '2026-08-21', 'reason' => 'Ninoy Aquino Day'],
            ['date' => '2026-08-31', 'reason' => 'National Heroes Day'],
            ['date' => '2026-12-25', 'reason' => 'Christmas Day'],
        ]
    ],
    
    'circulation' => [
        'borrowing_limits' => [
            'Student' => 3,
            'Faculty' => 5,
        ],
        'loan_durations' => [
            'Textbooks' => 3,
            'General Collection' => 7,
            'Reference Materials' => 1,
        ],
        'fine_rules' => [
            'daily_fine' => 5.00,
        ],
        'renewal_limits' => [
            'max_consecutive' => 2,
        ]
    ],
    
    'content_legal' => [
        'terms_and_conditions' => 'These are the terms and conditions of the library...',
        'data_privacy_policy' => 'Information describing how member and account data is collected...',
        'announcements' => [
            'status' => 'Enabled',
            'title' => 'Scheduled System Maintenance',
            'message' => 'The library system will be unavailable Friday...',
            'style' => 'Information'
        ]
    ],
    
    'notifications' => [
        'channels' => [
            'email' => true,
            'in_app' => true,
        ],
        'templates' => [
            ['name' => 'Overdue Warning', 'channel' => 'Email', 'status' => 'Active'],
            ['name' => 'Reservation Ready for Pickup', 'channel' => 'Email', 'status' => 'Active'],
            ['name' => 'Reservation Expiring', 'channel' => 'In-App', 'status' => 'Active'],
        ],
        'daily_cron' => '01:00',
    ],
    
    'ai_integrations' => [
        'recommendation_service' => [
            'url' => 'http://127.0.0.1',
            'port' => '5001',
            'status' => 'Connected',
        ],
        'confidence_threshold' => 65,
    ],
    
    'backup' => [
        'last_backup' => '2026-08-14 23:30:00'
    ]
];
