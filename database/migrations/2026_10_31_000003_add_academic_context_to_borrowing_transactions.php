<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->string('program_at_issue')->nullable();
            $table->string('year_level_at_issue')->nullable();
            $table->index(['issued_date', 'book_id'], 'borrowing_transactions_ml_snapshot_idx');
        });
    }

    public function down(): void
    {
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->dropColumn(['program_at_issue', 'year_level_at_issue']);
            $table->dropIndex('borrowing_transactions_ml_snapshot_idx');
        });
    }
};
