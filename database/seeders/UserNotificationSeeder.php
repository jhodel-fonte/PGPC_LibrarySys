<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserNotification;
use App\Models\Account;
use App\Models\NotifTemplate;

class UserNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();
        $templates = NotifTemplate::all();

        if ($accounts->isEmpty() || $templates->isEmpty()) {
            $this->command->info('Accounts or NotifTemplates are empty. Cannot seed notifications.');
            return;
        }

        // Seed 2 notifications for each account
        foreach ($accounts as $account) {
            for ($i = 0; $i < 2; $i++) {
                UserNotification::create([
                    'account_id' => $account->id,
                    'template_id' => $templates->random()->id,
                    'reference_type' => null, // Optional, can be updated later when linked to specific entities like BookReservation
                    'reference_id' => null,
                    'is_read' => (bool)rand(0, 1),
                ]);
            }
        }
    }
}
