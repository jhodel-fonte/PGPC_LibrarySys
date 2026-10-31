<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $accounts = [];

        // Seed enough students for up to 1,000 unique recommender interactions.
        for ($i = 1; $i <= 100; $i++) {
            $accounts[] = [
                'role_id' => 4, // Patron / Student
                'status_id' => 1, // Active
                'username' => "student{$i}",
                'password_hash' => Hash::make('Student123!'),
                'email' => "student{$i}@school.edu.ph",
                'is_email_verified' => true,
                'email_verified_at' => $now,
                'failed_attempts' => 0,
                'last_login' => (clone $now)->subDays(rand(1, 30)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Seed 3 Librarian / Staff accounts after the student accounts.
        $staffRoles = [2, 2, 3]; // 2 Librarians (ID 2), 1 Assistant Librarian (ID 3)
        foreach ($staffRoles as $idx => $roleId) {
            $num = $idx + 1;
            $accounts[] = [
                'role_id' => $roleId,
                'status_id' => 1, // Active
                'username' => "staff{$num}",
                'password_hash' => Hash::make('Staff123!'),
                'email' => "staff{$num}@school.edu.ph",
                'is_email_verified' => true,
                'email_verified_at' => $now,
                'failed_attempts' => 0,
                'last_login' => (clone $now)->subDays(rand(1, 15)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('accounts')->insertOrIgnore($accounts);

        $this->command->info('Accounts seeded successfully!');
    }
}
