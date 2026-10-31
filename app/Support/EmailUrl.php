<?php

namespace App\Support;

use DateTimeInterface;

final class EmailUrl
{
    public static function route(string $name, array $parameters = []): string
    {
        return rtrim((string) config('app.url'), '/') . '/' . ltrim(route($name, $parameters, false), '/');
    }

    public static function temporarySignedRoute(string $name, DateTimeInterface $expiration, array $parameters = []): string
    {
        $url = clone app('url');
        $url->forceRootUrl(rtrim((string) config('app.url'), '/'));

        return $url->temporarySignedRoute($name, $expiration, $parameters);
    }

    public static function asset(string $path): string
    {
        return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}
