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
        $local = config('database.connections.pgsql');

        if (! app()->environment('local') || config('database.default') !== 'pgsql'
            || ! empty($local['url']) || ! in_array(strtolower((string) ($local['host'] ?? '')), ['localhost', '127.0.0.1'], true)
            || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $local['database'] ?? '')
            || ! ctype_digit((string) ($local['port'] ?? '')) || (int) $local['port'] < 1 || (int) $local['port'] > 65535 || empty($local['username'])) {
            $this->error('Use APP_ENV=local, DB_CONNECTION=pgsql without DB_URL, and set DB_HOST to localhost or 127.0.0.1 with valid local database credentials.');
            return self::FAILURE;
        }

        $this->line("Local destination: {$local['database']} at {$local['host']}:{$local['port']} (user {$local['username']}).");
        $this->warn('DB_HOST must point to a local PostgreSQL server, not an SSH tunnel to the VM.');
        $source = [
            'host' => trim((string) $this->ask('VM database host (or local SSH tunnel host)')),
            'port' => trim((string) $this->ask('VM database port', '5432')),
            'database' => trim((string) $this->ask('VM database name')),
            'username' => trim((string) $this->ask('VM database username')),
            'password' => (string) $this->secret('VM database password'),
        ];
        if (! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9.-]*\z/', $source['host'])
            || ! ctype_digit($source['port']) || (int) $source['port'] < 1 || (int) $source['port'] > 65535
            || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $source['database']) || $source['username'] === '' || str_starts_with($source['username'], '-')) {
            $this->error('Provide a valid VM host, port, database name and username.');
            return self::FAILURE;
        }
        $addresses = gethostbynamel($source['host']) ?: [];
        $localAddresses = array_merge(['127.0.0.1'], gethostbynamel(gethostname()) ?: []);
        if (array_intersect($addresses, $localAddresses) && (string) $source['port'] === (string) $local['port']) {
            $this->error('The VM source must use a different endpoint from the local database.');
            return self::FAILURE;
        }

        $this->line("VM source: {$source['database']} at {$source['host']}:{$source['port']} (user {$source['username']}).");
        $this->warn("This will delete ALL schemas and large objects in local database {$local['database']} at 127.0.0.1:{$local['port']} and restore the VM snapshot. Back up local data first.");
        if (! $this->confirm('Continue?', false)) {
            return self::FAILURE;
        }

        $dump = tempnam(sys_get_temp_dir(), 'pgpc-db-');
        if ($dump === false) {
            $this->error('Could not create a temporary snapshot file.');
            return self::FAILURE;
        }

        $sql = tempnam(sys_get_temp_dir(), 'pgpc-sql-');
        if ($sql === false) {
            @unlink($dump);
            $this->error('Could not create a temporary restore file.');
            return self::FAILURE;
        }
        try {
            $backup = new Process(['pg_dump', '--format=custom', '--no-owner', '--no-privileges', '--no-password', '--host', $source['host'], '--port', (string) $source['port'], '--username', $source['username'], '--dbname', $source['database'], '--file', $dump], base_path(), ['PGPASSWORD' => $source['password'], 'PGSSLMODE' => $source['sslmode'] ?? 'prefer', 'PGHOSTADDR' => false], null, null);
            $backup->mustRun();

            (new Process(['pg_restore', '--no-owner', '--no-privileges', '--file', $sql, $dump], base_path(), null, null, null))->mustRun();
            $prefix = <<<'SQL'
DO $$ DECLARE s record; BEGIN
  FOR s IN SELECT nspname FROM pg_namespace WHERE nspname !~ '^pg_' AND nspname <> 'information_schema' LOOP
    EXECUTE format('DROP SCHEMA %I CASCADE', s.nspname);
  END LOOP;
END $$;
SELECT lo_unlink(oid) FROM pg_largeobject_metadata;
SQL;
            $hasPublicSchema = false;
            $scan = fopen($sql, 'rb');
            if (! $scan) {
                throw new \RuntimeException('Could not inspect restore file.');
            }
            while (($line = fgets($scan)) !== false) {
                if (preg_match('/^CREATE SCHEMA (?:public|"public");\s*$/', $line)) {
                    $hasPublicSchema = true;
                    break;
                }
            }
            fclose($scan);
            if (! $hasPublicSchema) {
                $prefix .= "\nCREATE SCHEMA public;";
            }
            $restoreSql = tempnam(sys_get_temp_dir(), 'pgpc-restore-');
            if ($restoreSql === false) {
                throw new \RuntimeException('Could not create restore file.');
            }
            try {
                $output = fopen($restoreSql, 'wb');
                $input = fopen($sql, 'rb');
                if (! $output || ! $input || fwrite($output, $prefix."\n") === false || stream_copy_to_stream($input, $output) === false) {
                    throw new \RuntimeException('Could not prepare restore file.');
                }
                fclose($input);
                fclose($output);
                $restore = new Process(['psql', '--no-psqlrc', '--single-transaction', '--set', 'ON_ERROR_STOP=1', '--no-password', '--host', '127.0.0.1', '--port', (string) $local['port'], '--username', $local['username'], '--dbname', $local['database'], '--file', $restoreSql], base_path(), ['PGPASSWORD' => $local['password'], 'PGHOSTADDR' => '127.0.0.1'], null, null);
                $restore->mustRun();
            } finally {
                @unlink($restoreSql);
            }
            $this->info('Local database refreshed from the VM snapshot.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Database refresh failed: '.$e->getMessage());
            return self::FAILURE;
        } finally {
            @unlink($dump);
            @unlink($sql);
        }
    }
}
