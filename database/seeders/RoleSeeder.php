<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            ['name' => 'Super Admin', 'description' => 'Has full access to all system features.'],
            ['name' => 'Librarian', 'description' => 'Manages catalog, users, and circulation.'],
            ['name' => 'Assistant Librarian', 'description' => 'Handles basic circulation and catalog viewing.'],
            ['name' => 'Patron', 'description' => 'Standard library user/student.'],
        ];

        $rolesWithTimestamps = array_map(function ($role) use ($now) {
            return array_merge($role, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $roles);

        DB::table('roles')->insertOrIgnore($rolesWithTimestamps);

        $this->command->info('Roles seeded successfully!');
    }
}