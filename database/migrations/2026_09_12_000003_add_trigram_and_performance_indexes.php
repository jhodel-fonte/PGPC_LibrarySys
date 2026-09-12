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
        if (DB::getDriverName() === 'pgsql') {
            // 1. Enable pg_trgm extension for fast substring/ILIKE search
            DB::statement("CREATE EXTENSION IF NOT EXISTS pg_trgm;");

            // 2. GIN Trigram indexes on core search fields
            DB::statement("CREATE INDEX IF NOT EXISTS idx_book_datas_title_trgm ON book_datas USING gin (book_title gin_trgm_ops);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_book_details_isbn_trgm ON book_details USING gin (isbn gin_trgm_ops);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_book_details_call_num_trgm ON book_details USING gin (call_number gin_trgm_ops);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_authors_first_name_trgm ON authors USING gin (first_name gin_trgm_ops);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_authors_last_name_trgm ON authors USING gin (last_name gin_trgm_ops);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_books_accession_no_trgm ON books USING gin (accession_number gin_trgm_ops);");

            // 3. Composite B-Tree indexes for fast joins & filtering
            DB::statement("CREATE INDEX IF NOT EXISTS idx_books_detail_status_del ON books (book_detail_id, status, deleted_at);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_borrowing_trans_student ON borrowing_transactions (school_id);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_borrowing_trans_book ON borrowing_transactions (book_id);");
            DB::statement("CREATE INDEX IF NOT EXISTS idx_fines_trans_status ON fines (borrowing_transaction_id, fine_status);");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS idx_book_datas_title_trgm;");
            DB::statement("DROP INDEX IF EXISTS idx_book_details_isbn_trgm;");
            DB::statement("DROP INDEX IF EXISTS idx_book_details_call_num_trgm;");
            DB::statement("DROP INDEX IF EXISTS idx_authors_first_name_trgm;");
            DB::statement("DROP INDEX IF EXISTS idx_authors_last_name_trgm;");
            DB::statement("DROP INDEX IF EXISTS idx_books_accession_no_trgm;");

            DB::statement("DROP INDEX IF EXISTS idx_books_detail_status_del;");
            DB::statement("DROP INDEX IF EXISTS idx_borrowing_trans_student;");
            DB::statement("DROP INDEX IF EXISTS idx_borrowing_trans_book;");
            DB::statement("DROP INDEX IF EXISTS idx_fines_trans_status;");
        }
    }
};
