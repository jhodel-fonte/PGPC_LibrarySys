<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // 1. Create PostgreSQL Roles (if they don't exist)
        // Note: For a real production app, passwords should be securely injected,
        // but this fulfills the database-level security structure requirement.
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'web_app_user') THEN
                    CREATE ROLE web_app_user WITH LOGIN PASSWORD 'web_password_123';
                END IF;

                IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'db_admin_user') THEN
                    CREATE ROLE db_admin_user WITH LOGIN PASSWORD 'admin_password_123';
                END IF;
            END $$;
        ");

        // Grant basic usage on public schema so they can connect and see tables
        DB::statement("GRANT USAGE ON SCHEMA public TO web_app_user, db_admin_user;");

        // Grant basic table permissions
        DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO web_app_user, db_admin_user;");
        DB::statement("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO web_app_user, db_admin_user;");

        // 2. Enable Row-Level Security on system_settings
        DB::statement("ALTER TABLE system_settings ENABLE ROW LEVEL SECURITY;");

        // 3. web_app_user Policies
        // The standard web app can SELECT all rows
        DB::statement("
            CREATE POLICY web_app_user_select_policy
            ON system_settings
            FOR SELECT
            TO web_app_user
            USING (true);
        ");

        // The standard web app can only UPDATE non-critical settings
        DB::statement("
            CREATE POLICY web_app_user_update_policy
            ON system_settings
            FOR UPDATE
            TO web_app_user
            USING (is_critical = false)
            WITH CHECK (is_critical = false);
        ");

        // 4. db_admin_user Policies
        // The admin user can do everything, including managing critical settings
        DB::statement("
            CREATE POLICY db_admin_user_all_policy
            ON system_settings
            TO db_admin_user
            USING (true)
            WITH CHECK (true);
        ");

        // 5. Force RLS to apply even to the table owner (optional but recommended for strictness if the owner acts as the app)
        // DB::statement("ALTER TABLE system_settings FORCE ROW LEVEL SECURITY;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Drop policies
        DB::statement("DROP POLICY IF EXISTS web_app_user_select_policy ON system_settings;");
        DB::statement("DROP POLICY IF EXISTS web_app_user_update_policy ON system_settings;");
        DB::statement("DROP POLICY IF EXISTS db_admin_user_all_policy ON system_settings;");

        // Disable RLS
        DB::statement("ALTER TABLE system_settings DISABLE ROW LEVEL SECURITY;");

        // Note: We deliberately do NOT drop the roles here in case other tables end up using them,
        // but if strictly needed:
        // DB::statement("DROP ROLE IF EXISTS web_app_user;");
        // DB::statement("DROP ROLE IF EXISTS db_admin_user;");
    }
};
