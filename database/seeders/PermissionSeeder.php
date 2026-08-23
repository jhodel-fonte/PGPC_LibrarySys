<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Consolidated Module-Based Permissions
        $permissions = [
            // ==========================================
            // STAFF / ADMIN PERMISSIONS
            // ==========================================
            [
                'name' => 'manage_users',
                'description' => 'Full CRUD access (View, Add, Edit, Delete) to students.',
            ],
            [
                'name' => 'manage_librarian',
                'description' => 'Full CRUD access (View, Add, Edit, Delete) to librarian and staff accounts.',
            ],
            [
                'name' => 'manage_catalog',
                'description' => 'Full CRUD access to the library catalog, books, and digital collections.',
            ],
            [
                'name' => 'manage_circulation',
                'description' => 'Full access to issue books, process returns, and handle reservations.',
            ],
            [
                'name' => 'manage_fines',
                'description' => 'Full access to view, apply, waive, and process overdue fines.',
            ],
            [
                'name' => 'manage_system',
                'description' => 'Full access to system settings, roles, permissions, and reports.',
            ],
            
            // ==========================================
            // STUDENT / PATRON PERMISSIONS
            // ==========================================
            [
                'name' => 'view_catalog',
                'description' => 'Can browse, search, and view book details in the library catalog.',
            ],
            [
                'name' => 'view_own_loans',
                'description' => 'Can view their own active book loans, transaction history, and fines.',
            ],
        ];

        $permissionsWithTimestamps = array_map(function ($permission) use ($now) {
            return array_merge($permission, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $permissions);

        try {
            DB::table('permissions')->insertOrIgnore($permissionsWithTimestamps);
            $this->command->info('Role-based permissions seeded successfully!');
        } catch (Exception $e) {
            $this->command->error('Failed to seed permissions. Error: ' . $e->getMessage());
        }
    }
}