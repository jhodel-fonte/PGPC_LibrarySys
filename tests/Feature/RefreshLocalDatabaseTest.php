<?php

namespace Tests\Feature;

use Tests\TestCase;

class RefreshLocalDatabaseTest extends TestCase
{
    public function test_rejects_non_local_environment(): void
    {
        $this->artisan('db:refresh-from-vm')->expectsOutputToContain('Use APP_ENV=local')->assertFailed();
    }

    public function test_rejects_same_source_and_target_endpoint(): void
    {
        $this->app['env'] = 'local';
        config(['database.default' => 'pgsql', 'database.connections.pgsql' => [
            'host' => '127.0.0.1', 'port' => '5432', 'database' => 'local', 'username' => 'user',
        ], 'database.local_snapshot_target' => [
            'port' => '5432', 'database' => 'local', 'username' => 'user',
        ]]);

        $this->artisan('db:refresh-from-vm')->expectsOutputToContain('different endpoint')->assertFailed();
    }

    public function test_declining_confirmation_does_not_restore(): void
    {
        $this->app['env'] = 'local';
        config(['database.default' => 'pgsql', 'database.connections.pgsql' => [
            'host' => '127.0.0.1', 'port' => '5433', 'database' => 'remote', 'username' => 'user',
        ], 'database.local_snapshot_target' => [
            'port' => '5432', 'database' => 'local', 'username' => 'user',
        ]]);

        $this->artisan('db:refresh-from-vm')->expectsConfirmation('Continue?', 'no')->assertFailed();
    }
}
