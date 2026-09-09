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
}
