<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the superadmin account already exists
        $existingAccount = DB::table('accounts')->where('username', 'superadmin')->first();

        if (!$existingAccount) {
            // 1. Create the account and get the newly inserted ID
            $accountId = DB::table('accounts')->insertGetId([
                'role_id'           => 1, // Assuming '1' is your Superadmin role ID in the 'roles' table
                'status_id'         => 1, // Assuming '1' is an Active status ID in 'account_statuses'
                'username'          => 'superadmin',
                'password_hash'     => Hash::make('Admin12345'), // Change this password
                'email'             => 'admin@admin.com',
                'is_email_verified' => true,
                'email_verified_at' => Carbon::now(),
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);

            // 2. Create the librarian profile using the $accountId
            DB::table('librarians')->insert([
                'account_id'       => $accountId,
                'school_id_number' => 'SA-0001',
                'first_name'       => 'Super',
                'middle_name'      => null,
                'last_name'        => 'Admin',
                'contact_num'      => '09123456789',
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]);
        }
    }
}
