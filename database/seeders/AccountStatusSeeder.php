<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $statuses = [
            [
                'id' => 1,
                'status_name' => 'Active',
                'description' => 'Account is active and has full access.',
            ],
            [
                'id' => 2,
                'status_name' => 'Inactive',
                'description' => 'Account is temporarily deactivated.',
            ],
            [
                'id' => 3,
                'status_name' => 'Suspended',
                'description' => 'Account is suspended due to violations.',
            ],
            [
                'id' => 4,
                'status_name' => 'Pending',
                'description' => 'Account is awaiting email verification or admin approval.',
            ],
        ];

        $data = array_map(function ($status) use ($now) {
            return array_merge($status, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $statuses);

        DB::table('account_statuses')->insertOrIgnore($data);

        $this->command->info('Account statuses seeded successfully!');
    }
}
