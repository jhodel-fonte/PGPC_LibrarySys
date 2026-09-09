<?php

namespace App\Services;

class SanitizeInput
{
    /**
     * Clean general input values to prevent HTML/XSS injection and strip control characters.
     *
     * @param string $value
     * @return string
     */
    public static function clean(string $value): string
    {
        $cleaned = strip_tags($value);
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $cleaned);
        return trim($cleaned);
    }

    public static function escapeLike(string $value): string
    {
        $cleaned = self::clean($value);
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cleaned);
    }
}
