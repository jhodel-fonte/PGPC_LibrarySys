<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LibrarySettings
{
    public static function get(string $key): mixed
    {
        $default = config('settings.'.$key);

        try {
            if (Schema::hasTable('system_settings')) {
                $value = DB::table('system_settings')->where('setting_key', $key)->value('setting_value');
                if ($value !== null) {
                    return is_bool($default) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : (is_int($default) ? (int) $value : $value);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);
        }

        return $default;
    }
}
