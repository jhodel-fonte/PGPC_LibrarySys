<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Services\SanitizeInput;

trait SanitizesRequestInput
{
    /**
     * Safely read a request input as a trimmed, sanitized string.
     * Returns '' when the value is an array (e.g. subject[]=1) to
     * prevent trim() TypeErrors on array parameters.
     */
    protected function inputString(Request $request, string $key): string
    {
        $value = $request->input($key);

        if (! is_string($value)) {
            return '';
        }

        return SanitizeInput::clean($value);
    }

    /**
     * Safely read a request input as an integer.
     * Returns null when the value is not numeric.
     */
    protected function inputInt(Request $request, string $key): ?int
    {
        $value = $request->input($key);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Safely sanitize and clean a string directly using SanitizeInput.
     * Strips HTML tags, removes control characters, and trims whitespace.
     * Returns null when the cleaned string is empty and $nullable is true.
     */
    protected function cleanString(mixed $value, bool $nullable = false): ?string
    {
        if ($value === null) {
            return $nullable ? null : '';
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return $nullable ? null : '';
        }

        $cleaned = SanitizeInput::clean((string) $value);

        if ($nullable && $cleaned === '') {
            return null;
        }

        return $cleaned;
    }

    /**
     * Alias for cleanString().
     */
    protected function sanitizeString(mixed $value, bool $nullable = false): ?string
    {
        return $this->cleanString($value, $nullable);
    }
}

