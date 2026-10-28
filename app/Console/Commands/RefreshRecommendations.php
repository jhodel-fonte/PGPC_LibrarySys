<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\RecommendationService;
use Illuminate\Console\Command;
use Throwable;

class RefreshRecommendations extends Command
{
    protected $signature = 'recommendations:refresh {--user= : Refresh one account ID only}';
    protected $description = 'Fetch and store ML recommendations using encrypted account identifiers.';

    public function handle(RecommendationService $recommendations): int
    {
        $accounts = Account::query()->orderBy('id');
        if ($user = $this->option('user')) {
            $accounts->whereKey($user);
        }

        $processed = 0;
        foreach ($accounts->cursor() as $account) {
            try {
                $recommendations->refreshFor($account);
                $processed++;
            } catch (Throwable $exception) {
                report($exception);
                $this->error("Could not refresh recommendations for account {$account->id}.");
            }
        }

        $this->info("Refreshed recommendations for {$processed} account(s).");
        return self::SUCCESS;
    }
}
