<?php

return [
    'name' => 'Padre Garcia Polytechnic College',
    'tagline' => 'Library System',
    'acronym' => 'PGPC',
    
    'coverFile_max_size' => env('COVERFILE_MAX_SIZE', '5MB'),
    'coverFile_types' => env('COVERFILE_TYPES', 'png,jpg,jpeg,webp'),
    'coverFile_url' => env('COVERFILE_URL', '/storage/book_cover/'), 
    'coverFile_default' => 'book-cover.webp',
    
];
