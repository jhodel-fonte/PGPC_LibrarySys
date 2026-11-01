<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';

use Symfony\Component\Process\Process;

$before = tempnam(sys_get_temp_dir(), 'pgpc-env-before-');
$after = tempnam(sys_get_temp_dir(), 'pgpc-env-after-');
$script = dirname(__DIR__, 2).'/deploy/ops.php';

try {
    file_put_contents($before, "APP_KEY=base64:old-secret\nAPP_ENV=production\n");
    foreach ([
        ["APP_KEY=base64:old-secret\nAPP_ENV=local\n", true],
        ["APP_KEY=base64:new-secret\n", false],
        ["APP_KEY=\"unterminated\n", false],
        ["APP_ENV=production\n", false],
    ] as [$content, $valid]) {
        file_put_contents($after, $content);
        $process = new Process([PHP_BINARY, $script, 'env-check', $before, $after]);
        $process->run();
        if ($process->isSuccessful() !== $valid || str_contains($process->getOutput().$process->getErrorOutput(), 'secret')) {
            throw new RuntimeException('Environment validation failed or exposed a secret.');
        }
    }
    echo "Environment validation checks passed.\n";
} finally {
    @unlink($before);
    @unlink($after);
}
