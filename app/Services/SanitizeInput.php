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
        // 1. Remove HTML tags to prevent XSS / markup injection
        $cleaned = strip_tags($value);

        // 2. Strip control characters and non-printable characters
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $cleaned);

        // 3. Trim surrounding whitespace
        return trim($cleaned);
    }

    /**
     * Escape special SQL wildcards (%, _, \) for LIKE/ILIKE queries.
     * Prevents users from entering characters that match arbitrary records or cause performance issues.
     *
     * @param string $value
     * @return string
     */
    public static function escapeLike(string $value): string
    {
        // Clean first
        $cleaned = self::clean($value);

        // Escape backend SQL wildcard characters (\ first, then % and _)
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cleaned);
    }
}
