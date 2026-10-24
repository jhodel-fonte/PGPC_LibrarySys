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
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE book_datas ALTER COLUMN marc_record TYPE TEXT');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE book_datas MODIFY marc_record LONGTEXT NULL');
        } else {
            Schema::table('book_datas', function (Blueprint $table) {
                $table->text('marc_record')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE book_datas ALTER COLUMN marc_record TYPE VARCHAR(255)');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE book_datas MODIFY marc_record VARCHAR(255) NULL');
        } else {
            Schema::table('book_datas', function (Blueprint $table) {
                $table->string('marc_record', 255)->nullable()->change();
            });
        }
    }
};
