<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPreference;
use App\Models\Account;

class UserPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();

        if ($accounts->isEmpty()) {
            $this->command->info('No accounts found to seed user preferences.');
            return;
        }

        foreach ($accounts as $account) {
            // Check if preference already exists to avoid duplicates if seeded multiple times
            if (!UserPreference::where('account_id', $account->id)->exists()) {
                UserPreference::create([
                    'account_id' => $account->id,
                    'cookies_enable' => true,
                    'email_overdue' => true,
                    'email_reservation' => true,
                    'due_reminder' => true,
                    'in_app_announcements' => true,
                ]);
            }
        }
    }
}
