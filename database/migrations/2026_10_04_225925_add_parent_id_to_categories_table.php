<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sqliteViewSql = $this->temporarilyDropSqliteCatalogView();

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('categories')
                    ->nullOnDelete();
            }
        });

        $this->restoreSqliteCatalogView($sqliteViewSql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $sqliteViewSql = $this->temporarilyDropSqliteCatalogView();

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
        });

        $this->restoreSqliteCatalogView($sqliteViewSql);
    }

    private function temporarilyDropSqliteCatalogView(): ?string
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return null;
        }

        $viewSql = DB::table('sqlite_master')
            ->where('type', 'view')
            ->where('name', 'opac_catalog_view')
            ->value('sql');

        if ($viewSql) {
            DB::statement('DROP VIEW IF EXISTS opac_catalog_view');
        }

        return $viewSql;
    }

    private function restoreSqliteCatalogView(?string $viewSql): void
    {
        if ($viewSql && DB::connection()->getDriverName() === 'sqlite') {
            DB::statement($viewSql);
        }
    }
};
