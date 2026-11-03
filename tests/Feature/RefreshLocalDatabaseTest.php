<?php

namespace Tests\Feature;

use Tests\TestCase;

class RefreshLocalDatabaseTest extends TestCase
{
    public function test_rejects_non_local_environment(): void
    {
        $this->artisan('db:refresh-from-vm')->expectsOutputToContain('Use APP_ENV=local')->assertFailed();
    }

    public function test_rejects_remote_destination_before_asking_for_vm_details(): void
    {
        $this->app['env'] = 'local';
        config(['database.default' => 'pgsql', 'database.connections.pgsql' => [
            'host' => 'vm.example.org', 'port' => '5432', 'database' => 'local', 'username' => 'user',
        ]]);

        $this->artisan('db:refresh-from-vm')->expectsOutputToContain('set DB_HOST to localhost')->assertFailed();
    }

    public function test_rejects_same_source_and_target_endpoint(): void
    {
        $this->app['env'] = 'local';
        config(['database.default' => 'pgsql', 'database.connections.pgsql' => [
            'host' => 'localhost', 'port' => '5432', 'database' => 'local', 'username' => 'user',
        ]]);

        $this->artisan('db:refresh-from-vm')
            ->expectsQuestion('VM database host (or local SSH tunnel host)', '127.0.0.1')
            ->expectsQuestion('VM database port', '5432')
            ->expectsQuestion('VM database name', 'remote')
            ->expectsQuestion('VM database username', 'user')
            ->expectsQuestion('VM database password', 'secret')
            ->expectsOutputToContain('different endpoint')->assertFailed();
    }

    public function test_declining_confirmation_does_not_restore(): void
    {
        $this->app['env'] = 'local';
        config(['database.default' => 'pgsql', 'database.connections.pgsql' => [
            'host' => 'localhost', 'port' => '5432', 'database' => 'local', 'username' => 'user',
        ]]);

        $this->artisan('db:refresh-from-vm')
            ->expectsQuestion('VM database host (or local SSH tunnel host)', '127.0.0.1')
            ->expectsQuestion('VM database port', '15432')
            ->expectsQuestion('VM database name', 'remote')
            ->expectsQuestion('VM database username', 'user')
            ->expectsQuestion('VM database password', 'secret')
            ->expectsConfirmation('Continue?', 'no')->assertFailed();
    }
}
