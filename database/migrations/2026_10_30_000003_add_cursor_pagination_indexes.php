<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->index(['reservation_date', 'id'], 'reservations_date_cursor_idx');
            $table->index(['due_date', 'id'], 'reservations_due_cursor_idx');
        });
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->index(['school_id', 'return_date', 'due_date', 'id'], 'loans_active_cursor_idx');
            $table->index(['school_id', 'return_date', 'id'], 'loans_history_cursor_idx');
        });
        Schema::table('ml_evaluation_runs', function (Blueprint $table) {
            $table->index(['evaluated_at', 'id'], 'ml_evaluation_cursor_idx');
        });
    }

    public function down(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_date_cursor_idx');
            $table->dropIndex('reservations_due_cursor_idx');
        });
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->dropIndex('loans_active_cursor_idx');
            $table->dropIndex('loans_history_cursor_idx');
        });
        Schema::table('ml_evaluation_runs', function (Blueprint $table) {
            $table->dropIndex('ml_evaluation_cursor_idx');
        });
    }
};
