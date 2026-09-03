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
        $this->down();

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement($this->createPgsqlViewSql());
        } else {
            DB::statement($this->createMysqlViewSql());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("DROP VIEW IF EXISTS opac_catalog_view CASCADE;");
        } else {
            DB::statement("DROP VIEW IF EXISTS `opac_catalog_view`;");
        }
    }

    /**
     * PostgreSQL syntax for opac_catalog_view.
     * Note:
     * - `languages` table column is `lang` (no softDeletes).
     * - `book_types` table column is `type` (no softDeletes).
     * - `borrowing_transactions` has no softDeletes.
     */
    private function createPgsqlViewSql(): string
    {
        return <<<SQL
CREATE OR REPLACE VIEW opac_catalog_view AS
SELECT 
    -- 1. Primary Keys & Foreign Keys
    bd.id AS book_detail_id,
    bdata.id AS book_data_id,
    bt.id AS book_type_id,
    p.id AS publisher_id,
    lang.id AS language_id,

    -- 2. Bibliographic Data
    bdata.book_title,
    bdata.subtitle,
    bdata.description,
    bdata.series_title,
    bdata.note,

    -- 3. Identifiers & Classification
    bd.isbn,
    bd.issn,
    bd.call_number,
    bd.classification,

    -- 4. Resource Type & Physical Format
    COALESCE(bt.type, 'Book') AS resource_type,
    COALESCE(bd.format, 'Book') AS format,
    bd.publication_year,
    bd.copyright_year,
    bd.edition,
    bd.pages,

    -- 5. Publisher & Language
    p.name AS publisher_name,
    lang.lang AS language_name,

    -- 6. Media & External Links
    bd.cover_image,
    bd.url,

    -- 7. Aggregated Authors
    COALESCE(
        (
            SELECT STRING_AGG(DISTINCT TRIM(CONCAT(a.first_name, ' ', a.last_name)), ', ')
            FROM book_data_author bda
            JOIN authors a ON a.id = bda.author_id AND a.deleted_at IS NULL
            WHERE bda.book_data_id = bdata.id
        ),
        'Unknown Author'
    ) AS authors,

    -- 8. Aggregated Categories & Subject IDs
    COALESCE(
        (
            SELECT STRING_AGG(DISTINCT c.name, ', ')
            FROM book_data_category bdc
            JOIN categories c ON c.id = bdc.category_id AND c.deleted_at IS NULL
            WHERE bdc.book_data_id = bdata.id
        ),
        'General'
    ) AS categories,

    (
        SELECT STRING_AGG(DISTINCT CAST(c.id AS VARCHAR), ',')
        FROM book_data_category bdc
        JOIN categories c ON c.id = bdc.category_id AND c.deleted_at IS NULL
        WHERE bdc.book_data_id = bdata.id
    ) AS category_ids,

    -- 9. Physical Copies Aggregation
    COUNT(b.id) AS total_copies,
    COUNT(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN 1 END) AS available_copies,
    COUNT(CASE WHEN b.status = 'borrowed' AND b.deleted_at IS NULL THEN 1 END) AS borrowed_copies,
    COUNT(CASE WHEN b.status = 'reserved' AND b.deleted_at IS NULL THEN 1 END) AS reserved_copies,

    -- 10. Representative Accession & Location
    MIN(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN b.accession_number ELSE b.accession_number END) AS primary_accession_no,
    MIN(CASE WHEN b.location IS NOT NULL AND b.location != '' THEN b.location ELSE 'Main Library' END) AS primary_location,
    MIN(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN b.id END) AS available_book_id,

    -- 11. Calculated Catalog Status
    CASE 
        WHEN COUNT(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN 1 END) > 0 THEN 'available'
        WHEN COUNT(b.id) = 0 THEN 'reference_only'
        WHEN COUNT(CASE WHEN b.status = 'reserved' AND b.deleted_at IS NULL THEN 1 END) > 0 THEN 'reserved'
        ELSE 'checked_out'
    END AS catalog_status,

    -- 12. Earliest Return Due Date among active checkouts
    (
        SELECT MIN(btx.due_date)
        FROM books bk
        JOIN borrowing_transactions btx ON btx.book_id = bk.id
        WHERE bk.book_detail_id = bd.id 
          AND bk.status = 'borrowed' 
          AND btx.return_date IS NULL
    ) AS earliest_due_date,

    -- 13. Timestamps
    bd.created_at,
    bd.updated_at

FROM book_details bd
JOIN book_datas bdata ON bdata.id = bd.book_data_id AND bdata.deleted_at IS NULL
LEFT JOIN book_types bt ON bt.id = bd.book_type_id
LEFT JOIN publishers p ON p.id = bd.publisher_id AND p.deleted_at IS NULL
LEFT JOIN languages lang ON lang.id = bdata.language_id
LEFT JOIN books b ON b.book_detail_id = bd.id AND b.deleted_at IS NULL

WHERE bd.deleted_at IS NULL

GROUP BY 
    bd.id,
    bdata.id,
    bt.id,
    p.id,
    lang.id,
    bdata.book_title,
    bdata.subtitle,
    bdata.description,
    bdata.series_title,
    bdata.note,
    bd.isbn,
    bd.issn,
    bd.call_number,
    bd.classification,
    bt.type,
    bd.format,
    bd.publication_year,
    bd.copyright_year,
    bd.edition,
    bd.pages,
    p.name,
    lang.lang,
    bd.cover_image,
    bd.url,
    bd.created_at,
    bd.updated_at;
SQL;
    }

    /**
     * MySQL syntax for opac_catalog_view.
     */
    private function createMysqlViewSql(): string
    {
        return <<<SQL
CREATE OR REPLACE VIEW `opac_catalog_view` AS
SELECT 
    bd.id AS book_detail_id,
    bdata.id AS book_data_id,
    bt.id AS book_type_id,
    p.id AS publisher_id,
    lang.id AS language_id,
    bdata.book_title,
    bdata.subtitle,
    bdata.description,
    bdata.series_title,
    bdata.note,
    bd.isbn,
    bd.issn,
    bd.call_number,
    bd.classification,
    COALESCE(bt.type, 'Book') AS resource_type,
    COALESCE(bd.format, 'Book') AS format,
    bd.publication_year,
    bd.copyright_year,
    bd.edition,
    bd.pages,
    p.name AS publisher_name,
    lang.lang AS language_name,
    bd.cover_image,
    bd.url,
    COALESCE(
        (
            SELECT GROUP_CONCAT(
                DISTINCT TRIM(CONCAT(a.first_name, ' ', a.last_name))
                ORDER BY a.last_name ASC 
                SEPARATOR ', '
            )
            FROM book_data_author bda
            JOIN authors a ON a.id = bda.author_id AND a.deleted_at IS NULL
            WHERE bda.book_data_id = bdata.id
        ),
        'Unknown Author'
    ) AS authors,
    COALESCE(
        (
            SELECT GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ')
            FROM book_data_category bdc
            JOIN categories c ON c.id = bdc.category_id AND c.deleted_at IS NULL
            WHERE bdc.book_data_id = bdata.id
        ),
        'General'
    ) AS categories,
    (
        SELECT GROUP_CONCAT(DISTINCT c.id SEPARATOR ',')
        FROM book_data_category bdc
        JOIN categories c ON c.id = bdc.category_id AND c.deleted_at IS NULL
        WHERE bdc.book_data_id = bdata.id
    ) AS category_ids,
    COUNT(b.id) AS total_copies,
    COUNT(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN 1 END) AS available_copies,
    COUNT(CASE WHEN b.status = 'borrowed' AND b.deleted_at IS NULL THEN 1 END) AS borrowed_copies,
    COUNT(CASE WHEN b.status = 'reserved' AND b.deleted_at IS NULL THEN 1 END) AS reserved_copies,
    MIN(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN b.accession_number ELSE b.accession_number END) AS primary_accession_no,
    MIN(CASE WHEN b.location IS NOT NULL AND b.location != '' THEN b.location ELSE 'Main Library' END) AS primary_location,
    MIN(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN b.id END) AS available_book_id,
    CASE 
        WHEN COUNT(CASE WHEN b.status = 'available' AND b.deleted_at IS NULL THEN 1 END) > 0 THEN 'available'
        WHEN COUNT(b.id) = 0 THEN 'reference_only'
        WHEN COUNT(CASE WHEN b.status = 'reserved' AND b.deleted_at IS NULL THEN 1 END) > 0 THEN 'reserved'
        ELSE 'checked_out'
    END AS catalog_status,
    (
        SELECT MIN(btx.due_date)
        FROM books bk
        JOIN borrowing_transactions btx ON btx.book_id = bk.id
        WHERE bk.book_detail_id = bd.id 
          AND bk.status = 'borrowed' 
          AND btx.return_date IS NULL
    ) AS earliest_due_date,
    bd.created_at,
    bd.updated_at
FROM book_details bd
JOIN book_datas bdata ON bdata.id = bd.book_data_id AND bdata.deleted_at IS NULL
LEFT JOIN book_types bt ON bt.id = bd.book_type_id
LEFT JOIN publishers p ON p.id = bd.publisher_id AND p.deleted_at IS NULL
LEFT JOIN languages lang ON lang.id = bdata.language_id
LEFT JOIN books b ON b.book_detail_id = bd.id AND b.deleted_at IS NULL
WHERE bd.deleted_at IS NULL
GROUP BY 
    bd.id, bdata.id, bt.id, p.id, lang.id, bdata.book_title, bdata.subtitle, bdata.description,
    bdata.series_title, bdata.note, bd.isbn, bd.issn, bd.call_number, bd.classification,
    bt.type, bd.format, bd.publication_year, bd.copyright_year, bd.edition, bd.pages,
    p.name, lang.lang, bd.cover_image, bd.url, bd.created_at, bd.updated_at;
SQL;
    }
};
