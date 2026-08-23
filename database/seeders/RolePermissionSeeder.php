<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Fetch all available permission IDs dynamically
        $allPermissionIds = DB::table('permissions')->pluck('id')->toArray();

        if (empty($allPermissionIds)) {
            $this->command->warn('No permissions found! Please run the PermissionSeeder first.');
            return;
        }
        
        $assistantPermissionNames = [
            'manage_users',
            'manage_catalog',
            'manage_circulation',
            'manage_fines',
            'view_catalog',
        ];

        $assistantPermissionIds = DB::table('permissions')
            ->whereIn('name', $assistantPermissionNames)
            ->pluck('id')
            ->toArray();

        $studentPermissionNames = [
            'view_catalog',
            'view_own_loans',
        ];

        $studentPermissionIds = DB::table('permissions')
            ->whereIn('name', $studentPermissionNames)
            ->pluck('id')
            ->toArray();

        // Safety check to ensure the names matched the database
        if (empty($assistantPermissionIds) || empty($studentPermissionIds)) {
            $this->command->warn('Specific role permissions not found! Check your PermissionSeeder names.');
            return;
        }

        $records = [];

        // 1. SUPER ADMIN (Role ID: 1) -> All Permissions
        foreach ($allPermissionIds as $permId) {
            $records[] = [
                'role_id'       => 1,
                'permission_id' => $permId,
                'is_allowed'    => true,
                'created_at'    => $now,
            ];
        }

        // 2. HEAD LIBRARIAN (Role ID: 2) -> All Permissions
        foreach ($allPermissionIds as $permId) {
            $records[] = [
                'role_id'       => 2,
                'permission_id' => $permId,
                'is_allowed'    => true,
                'created_at'    => $now,
            ];
        }

        // 3. LIBRARIAN (Role ID: 3) -> Specific Categories Only
        foreach ($assistantPermissionIds as $permId) {
            $records[] = [
                'role_id'       => 3,
                'permission_id' => $permId,
                'is_allowed'    => true,
                'created_at'    => $now,
            ];
        }

        // 4. STUDENT (Role ID: 4) -> Read-only / Self-service Only
        foreach ($studentPermissionIds as $permId) {
            $records[] = [
                'role_id'       => 4,
                'permission_id' => $permId,
                'is_allowed'    => true,
                'created_at'    => $now,
            ];
        }

        try {
            DB::table('role_permissions')->insertOrIgnore($records);
            $this->command->info('Role permissions (including student) assigned successfully!');
        } catch (Exception $e) {
            $this->command->error('Failed to seed role permissions. Error: ' . $e->getMessage());
        }
    }
}