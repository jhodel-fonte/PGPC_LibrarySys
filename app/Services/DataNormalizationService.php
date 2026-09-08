<?php

namespace App\Services;

class DataNormalizationService
{
    /**
     * Normalize items into a plain array.
     */
    public static function normalizeItems(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if (is_object($items) && method_exists($items, 'values')) {
            return $items->values()->toArray();
        }

        return (array) $items;
    }
}