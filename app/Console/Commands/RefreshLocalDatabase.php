<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Throwable;

class RefreshLocalDatabase extends Command
{
    protected $signature = 'db:refresh-from-vm';

    protected $description = 'Replace local PostgreSQL data with a snapshot from the VM';

    public function handle(): int
    {
        $source = config('database.connections.pgsql');
        $local = config('database.local_snapshot_target');

        if (! app()->environment('local') || config('database.default') !== 'pgsql'
            || ! empty($source['url'])
            || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $local['database'] ?? '')
            || ! ctype_digit((string) ($local['port'] ?? '')) || empty($local['username'])) {
            $this->error('Use APP_ENV=local, DB_CONNECTION=pgsql without DB_URL, and configure LOCAL_DB_PORT, LOCAL_DB_DATABASE and LOCAL_DB_USERNAME.');
            return self::FAILURE;
        }

        if (empty($source['host']) || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $source['database'] ?? '') || empty($source['username'])
            || (($source['host'] === '127.0.0.1' || $source['host'] === 'localhost') && (string) $source['port'] === (string) $local['port'])) {
            $this->error('Configure the VM using DB_HOST, DB_PORT, DB_DATABASE and DB_USERNAME; it must use a different endpoint from the local database.');
            return self::FAILURE;
        }

        $this->warn("This will replace objects and data in local database {$local['database']} at 127.0.0.1:{$local['port']} with the VM snapshot.");
        if (! $this->confirm('Continue?', false)) {
            return self::FAILURE;
        }

        $dump = tempnam(sys_get_temp_dir(), 'pgpc-db-');
        if ($dump === false) {
            $this->error('Could not create a temporary snapshot file.');
            return self::FAILURE;
        }

        try {
            $backup = new Process(['pg_dump', '--format=custom', '--no-owner', '--no-privileges', '--no-password', '--host', $source['host'], '--port', (string) $source['port'], '--username', $source['username'], '--dbname', $source['database'], '--file', $dump], base_path(), ['PGPASSWORD' => $source['password'], 'PGSSLMODE' => $source['sslmode'] ?? 'prefer', 'PGHOSTADDR' => false], null, null);
            $backup->mustRun();

            $restore = new Process(['pg_restore', '--clean', '--if-exists', '--single-transaction', '--no-owner', '--no-privileges', '--no-password', '--host', '127.0.0.1', '--port', (string) $local['port'], '--username', $local['username'], '--dbname', $local['database'], $dump], base_path(), ['PGPASSWORD' => $local['password'], 'PGHOSTADDR' => '127.0.0.1'], null, null);
            $restore->mustRun();
            $this->info('Local database refreshed from the VM snapshot.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Database refresh failed: '.$e->getMessage());
            return self::FAILURE;
        } finally {
            @unlink($dump);
        }
    }
}
