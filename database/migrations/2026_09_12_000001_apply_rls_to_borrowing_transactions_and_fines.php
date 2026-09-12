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

        // 1. Enable RLS on borrowing_transactions and fines
        DB::statement("ALTER TABLE borrowing_transactions ENABLE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE fines ENABLE ROW LEVEL SECURITY;");

        // 2. db_admin_user has full access to both tables
        DB::statement("
            DO $$
            BEGIN
                IF EXISTS (SELECT FROM pg_roles WHERE rolname = 'db_admin_user') THEN
                    CREATE POLICY db_admin_user_all_transactions_policy
                    ON borrowing_transactions
                    TO db_admin_user
                    USING (true)
                    WITH CHECK (true);

                    CREATE POLICY db_admin_user_all_fines_policy
                    ON fines
                    TO db_admin_user
                    USING (true)
                    WITH CHECK (true);
                END IF;
            END $$;
        ");

        // 3. web_app_user policies on borrowing_transactions
        // Allows staff/admin full access, and allows students to view their own records (school_id references students table)
        DB::statement("
            DO $$
            BEGIN
                IF EXISTS (SELECT FROM pg_roles WHERE rolname = 'web_app_user') THEN
                    CREATE POLICY web_app_user_transactions_select_policy
                    ON borrowing_transactions
                    FOR SELECT
                    TO web_app_user
                    USING (
                        current_setting('app.current_user_role', true) IN ('librarian', 'admin')
                        OR current_setting('app.current_user_role', true) IS NULL
                        OR school_id::text = NULLIF(current_setting('app.current_student_id', true), '')
                    );

                    CREATE POLICY web_app_user_transactions_mod_policy
                    ON borrowing_transactions
                    FOR ALL
                    TO web_app_user
                    USING (
                        current_setting('app.current_user_role', true) IN ('librarian', 'admin')
                        OR current_setting('app.current_user_role', true) IS NULL
                    )
                    WITH CHECK (
                        current_setting('app.current_user_role', true) IN ('librarian', 'admin')
                        OR current_setting('app.current_user_role', true) IS NULL
                    );

                    -- 4. web_app_user policies on fines
                    CREATE POLICY web_app_user_fines_select_policy
                    ON fines
                    FOR SELECT
                    TO web_app_user
                    USING (
                        current_setting('app.current_user_role', true) IN ('librarian', 'admin')
                        OR current_setting('app.current_user_role', true) IS NULL
                        OR student_id::text = NULLIF(current_setting('app.current_student_id', true), '')
                    );

                    CREATE POLICY web_app_user_fines_mod_policy
                    ON fines
                    FOR ALL
                    TO web_app_user
                    USING (
                        current_setting('app.current_user_role', true) IN ('librarian', 'admin')
                        OR current_setting('app.current_user_role', true) IS NULL
                    )
                    WITH CHECK (
                        current_setting('app.current_user_role', true) IN ('librarian', 'admin')
                        OR current_setting('app.current_user_role', true) IS NULL
                    );
                END IF;
            END $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("DROP POLICY IF EXISTS db_admin_user_all_transactions_policy ON borrowing_transactions;");
        DB::statement("DROP POLICY IF EXISTS db_admin_user_all_fines_policy ON fines;");
        DB::statement("DROP POLICY IF EXISTS web_app_user_transactions_select_policy ON borrowing_transactions;");
        DB::statement("DROP POLICY IF EXISTS web_app_user_transactions_mod_policy ON borrowing_transactions;");
        DB::statement("DROP POLICY IF EXISTS web_app_user_fines_select_policy ON fines;");
        DB::statement("DROP POLICY IF EXISTS web_app_user_fines_mod_policy ON fines;");

        DB::statement("ALTER TABLE borrowing_transactions DISABLE ROW LEVEL SECURITY;");
        DB::statement("ALTER TABLE fines DISABLE ROW LEVEL SECURITY;");
    }
};
