<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add counter columns to book_details
        Schema::table('book_details', function (Blueprint $table) {
            $table->unsignedInteger('total_copies')->default(0)->index();
            $table->unsignedInteger('available_copies')->default(0)->index();
            $table->unsignedInteger('borrowed_copies')->default(0);
            $table->unsignedInteger('damaged_lost_copies')->default(0);
        });

        if (DB::getDriverName() === 'pgsql') {
            // 2. Create PostgreSQL Trigger Function for sync
            DB::unprepared("
                CREATE OR REPLACE FUNCTION fn_sync_book_detail_copy_counts()
                RETURNS TRIGGER AS $$
                DECLARE
                    target_detail_id BIGINT;
                BEGIN
                    IF (TG_OP = 'DELETE') THEN
                        target_detail_id := OLD.book_detail_id;
                    ELSE
                        target_detail_id := NEW.book_detail_id;
                    END IF;

                    IF target_detail_id IS NOT NULL THEN
                        UPDATE book_details
                        SET 
                            total_copies = (
                                SELECT COUNT(*) FROM books WHERE book_detail_id = target_detail_id AND deleted_at IS NULL
                            ),
                            available_copies = (
                                SELECT COUNT(*) FROM books 
                                WHERE book_detail_id = target_detail_id 
                                  AND deleted_at IS NULL
                                  AND LOWER(status) = 'available' 
                                  AND (book_condition_id IS NULL OR book_condition_id IN (1, 2, 3))
                            ),
                            borrowed_copies = (
                                SELECT COUNT(*) FROM books 
                                WHERE book_detail_id = target_detail_id 
                                  AND deleted_at IS NULL
                                  AND LOWER(status) = 'borrowed'
                            ),
                            damaged_lost_copies = (
                                SELECT COUNT(*) FROM books 
                                WHERE book_detail_id = target_detail_id 
                                  AND deleted_at IS NULL
                                  AND (LOWER(status) IN ('damaged', 'lost') OR book_condition_id IN (4, 5))
                            )
                        WHERE id = target_detail_id;
                    END IF;

                    -- If book_detail_id changed on UPDATE, also sync old detail
                    IF (TG_OP = 'UPDATE' AND OLD.book_detail_id IS DISTINCT FROM NEW.book_detail_id AND OLD.book_detail_id IS NOT NULL) THEN
                        UPDATE book_details
                        SET 
                            total_copies = (
                                SELECT COUNT(*) FROM books WHERE book_detail_id = OLD.book_detail_id AND deleted_at IS NULL
                            ),
                            available_copies = (
                                SELECT COUNT(*) FROM books 
                                WHERE book_detail_id = OLD.book_detail_id 
                                  AND deleted_at IS NULL
                                  AND LOWER(status) = 'available' 
                                  AND (book_condition_id IS NULL OR book_condition_id IN (1, 2, 3))
                            ),
                            borrowed_copies = (
                                SELECT COUNT(*) FROM books 
                                WHERE book_detail_id = OLD.book_detail_id 
                                  AND deleted_at IS NULL
                                  AND LOWER(status) = 'borrowed'
                            ),
                            damaged_lost_copies = (
                                SELECT COUNT(*) FROM books 
                                WHERE book_detail_id = OLD.book_detail_id 
                                  AND deleted_at IS NULL
                                  AND (LOWER(status) IN ('damaged', 'lost') OR book_condition_id IN (4, 5))
                            )
                        WHERE id = OLD.book_detail_id;
                    END IF;

                    RETURN NULL;
                END;
                $$ LANGUAGE plpgsql;
            ");

            // 3. Create Trigger on books table
            DB::unprepared("
                DROP TRIGGER IF EXISTS trg_books_sync_counts ON books;
                CREATE TRIGGER trg_books_sync_counts
                AFTER INSERT OR UPDATE OF book_detail_id, status, book_condition_id, deleted_at OR DELETE
                ON books
                FOR EACH ROW
                EXECUTE FUNCTION fn_sync_book_detail_copy_counts();
            ");

            // 4. Initial Backfill of counter data
            DB::unprepared("
                UPDATE book_details bd
                SET 
                    total_copies = COALESCE(sub.tot, 0),
                    available_copies = COALESCE(sub.avail, 0),
                    borrowed_copies = COALESCE(sub.borrowed, 0),
                    damaged_lost_copies = COALESCE(sub.dmg_lost, 0)
                FROM (
                    SELECT 
                        book_detail_id,
                        COUNT(*) as tot,
                        COUNT(*) FILTER (WHERE LOWER(status) = 'available' AND (book_condition_id IS NULL OR book_condition_id IN (1, 2, 3))) as avail,
                        COUNT(*) FILTER (WHERE LOWER(status) = 'borrowed') as borrowed,
                        COUNT(*) FILTER (WHERE LOWER(status) IN ('damaged', 'lost') OR book_condition_id IN (4, 5)) as dmg_lost
                    FROM books
                    WHERE deleted_at IS NULL
                    GROUP BY book_detail_id
                ) sub
                WHERE bd.id = sub.book_detail_id;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared("
                DROP TRIGGER IF EXISTS trg_books_sync_counts ON books;
                DROP FUNCTION IF EXISTS fn_sync_book_detail_copy_counts();
            ");
        }

        Schema::table('book_details', function (Blueprint $table) {
            $table->dropColumn(['total_copies', 'available_copies', 'borrowed_copies', 'damaged_lost_copies']);
        });
    }
};
