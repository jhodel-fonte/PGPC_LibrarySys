<?php

declare(strict_types=1);

use App\Mail\WelcomeEmail;
use App\Services\SmsService;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

class MaintenanceError extends RuntimeException {}

$action = $argv[1] ?? '';

try {
    if ($action === 'env-check') {
        $before = Dotenv::parse(file_get_contents($argv[2]));
        $after = Dotenv::parse(file_get_contents($argv[3] ?? dirname(__DIR__).'/.env'));
        if (empty($before['APP_KEY']) || empty($after['APP_KEY']) || ! hash_equals($before['APP_KEY'], $after['APP_KEY'])) {
            fwrite(STDERR, "APP_KEY is missing or changed. Refusing to apply this edit.\n");
            exit(1);
        }
        echo "Environment syntax and existing APP_KEY checked.\n";
        exit(0);
    }

    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    switch ($action) {
        case 'health':
            try {
                DB::connection()->getPdo();
                echo "Database connection: OK\n";
            } catch (Throwable $e) {
                echo "Database connection: FAILED (check Laravel logs)\n";
            }
            $url = parse_url((string) config('services.recommender.url'));
            if (is_array($url) && ! empty($url['host'])) {
                $port = $url['port'] ?? (($url['scheme'] ?? '') === 'https' ? 443 : 80);
                $socket = @fsockopen($url['host'], (int) $port, $errno, $error, 3);
                echo 'Recommendation service TCP: '.($socket ? 'reachable' : 'unreachable')." (connection check only)\n";
                if ($socket) fclose($socket);
            } else {
                echo "Recommendation service: no valid URL configured\n";
            }
            break;

        case 'settings':
            $mailer = (string) config('mail.default');
            echo 'Mail transport: '.($mailer ?: '(not configured)')."\n";
            echo 'Mail sender: '.(config('mail.from.address') ? 'configured' : 'missing')."\n";
            echo 'SMS: '.(config('settings.sms.enabled') && config('settings.sms.api_token') ? 'enabled with token' : 'disabled or missing token')."\n";
            break;

        case 'maintenance':
            echo $app->isDownForMaintenance() ? "down\n" : "up\n";
            break;

        case 'email-test':
            $recipient = $argv[2] ?? '';
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) throw new MaintenanceError('Invalid email address.');
            if (in_array(config('mail.default'), ['log', 'array'], true)) throw new MaintenanceError('The configured mail transport does not deliver email.');
            Mail::to($recipient)->sendNow(new WelcomeEmail());
            echo "Test email accepted by the configured transport. Check the inbox.\n";
            break;

        case 'sms-test':
            $recipient = $argv[2] ?? '';
            $sms = app(SmsService::class);
            if (! $sms->formatPhoneNumber($recipient)) throw new MaintenanceError('Invalid Philippine mobile number.');
            $result = $sms->send($recipient, 'PGPC Library System: this is a test message.');
            if (! $result['success']) throw new MaintenanceError('SMS was not accepted. Check application logs and settings.');
            echo "Test SMS accepted by the gateway. Check the phone.\n";
            break;

        case 'db-dump':
            $output = $argv[2] ?? '';
            if ($output === '' || ! is_dir(dirname($output))) throw new MaintenanceError('Backup destination is unavailable.');
            $connection = DB::connection();
            if ($connection->getDriverName() !== 'pgsql') throw new MaintenanceError('Automatic database backup supports PostgreSQL only.');
            $config = $connection->getConfig();
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 5432;
            $database = $config['database'] ?? '';
            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';
            if (! is_string($host) || ! is_string($database) || ! is_string($username) || ! is_string($password) || strpbrk($host.$database.$username.$password, "\r\n") !== false || $database === '' || $username === '') {
                throw new MaintenanceError('The PostgreSQL connection is not supported by this backup tool.');
            }
            $executable = (new ExecutableFinder)->find('pg_dump');
            if (! $executable) throw new MaintenanceError('pg_dump is not installed.');
            $passfile = tempnam(dirname($output), '.pgpass-');
            if ($passfile === false) throw new MaintenanceError('Could not create a protected PostgreSQL credential file.');
            chmod($passfile, 0600);
            try {
                $escape = fn ($value) => str_replace(['\\', ':'], ['\\\\', '\\:'], (string) $value);
                $passHost = $host === '' || str_starts_with($host, '/') ? 'localhost' : $host;
                if (file_put_contents($passfile, implode(':', array_map($escape, [$passHost, $port, $database, $username, $password]))."\n") === false) throw new MaintenanceError('Could not write the protected PostgreSQL credential file.');
                $env = ['PGPASSFILE' => $passfile, 'PGPASSWORD' => false];
                if (! empty($config['sslmode'])) $env['PGSSLMODE'] = (string) $config['sslmode'];
                $process = new Process([$executable, '--format=custom', '--no-owner', '--no-privileges', '--no-password', '--host', $host, '--port', (string) $port, '--username', $username, '--dbname', $database, '--file', $output], dirname(__DIR__), $env, null, 1800);
                $process->run();
                if (! $process->isSuccessful()) throw new MaintenanceError('PostgreSQL backup failed. Check the connection and pg_dump compatibility.');
            } finally {
                @unlink($passfile);
            }
            echo "PostgreSQL dump created.\n";
            break;

        default:
            throw new MaintenanceError('Unknown maintenance action.');
    }
} catch (Throwable $e) {
    fwrite(STDERR, ($e instanceof MaintenanceError ? $e->getMessage() : 'Maintenance action failed. Check server logs.')."\n");
    exit(1);
}
