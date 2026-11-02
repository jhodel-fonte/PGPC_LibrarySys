<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') return;

        $keys = "'library_config.max_loan_limit', 'library_config.loan_period', 'reservation.max_pickup_days', 'reservation.auto_approve', 'reservation.auto_approve_delay_minutes', 'notifications.due_reminder_days'";
        DB::statement("CREATE POLICY web_library_settings_insert ON system_settings FOR INSERT TO web_app_user WITH CHECK (is_critical = false AND setting_key IN ($keys))");
        DB::statement("CREATE POLICY web_library_settings_delete ON system_settings FOR DELETE TO web_app_user USING (is_critical = false AND setting_key IN ($keys))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') return;
        DB::statement('DROP POLICY IF EXISTS web_library_settings_insert ON system_settings');
        DB::statement('DROP POLICY IF EXISTS web_library_settings_delete ON system_settings');
    }
};
