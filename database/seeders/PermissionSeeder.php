<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $permissions = [
            // ==========================================
            // USER (PATRON) MANAGEMENT
            // ==========================================
            [
                'name' => 'view_users',
                'description' => 'Can view the list and details of library users.',
            ],
            [
                'name' => 'add_user',
                'description' => 'Can register new library users.',
            ],
            [
                'name' => 'edit_user',
                'description' => 'Can update existing user information.',
            ],
            [
                'name' => 'delete_user',
                'description' => 'Can deactivate or delete library users.',
            ],

            // ==========================================
            // LIBRARIAN / STAFF MANAGEMENT
            // ==========================================
            [
                'name' => 'view_librarians',
                'description' => 'Can view the list of librarians and staff.',
            ],
            [
                'name' => 'add_librarian',
                'description' => 'Can create new librarian accounts.',
            ],
            [
                'name' => 'edit_librarian',
                'description' => 'Can update librarian account details.',
            ],
            [
                'name' => 'delete_librarian',
                'description' => 'Can deactivate or remove librarian accounts.',
            ],

            // ==========================================
            // CATALOG / BOOK MANAGEMENT
            // ==========================================
            [
                'name' => 'view_books',
                'description' => 'Can view the book catalog (usually public, but good to define).',
            ],
            [
                'name' => 'add_book',
                'description' => 'Can add new books to the library catalog.',
            ],
            [
                'name' => 'edit_book',
                'description' => 'Can update book details and MARC records.',
            ],
            [
                'name' => 'delete_book',
                'description' => 'Can remove books from the catalog or mark them as lost/damaged.',
            ],

            // ==========================================
            // CIRCULATION / TRANSACTIONS
            // ==========================================
            [
                'name' => 'issue_books',
                'description' => 'Can check out books to users.',
            ],
            [
                'name' => 'return_books',
                'description' => 'Can process book returns.',
            ],
            [
                'name' => 'manage_fines',
                'description' => 'Can apply, waive, or process payment for overdue fines.',
            ],

            // ==========================================
            // SYSTEM & ADMIN
            // ==========================================
            [
                'name' => 'manage_roles',
                'description' => 'Can create roles and assign permissions to them.',
            ],
            [
                'name' => 'view_reports',
                'description' => 'Can view and export system analytics and library reports.',
            ],
        ];

        // Map through the array to add timestamps to all entries automatically
        $permissionsWithTimestamps = array_map(function ($permission) use ($now) {
            return array_merge($permission, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $permissions);

        // Insert into the database, ignoring duplicates if seeded multiple times
        DB::table('permissions')->insertOrIgnore($permissionsWithTimestamps);
        
        $this->command->info('Granular permissions seeded successfully!');
    }
}