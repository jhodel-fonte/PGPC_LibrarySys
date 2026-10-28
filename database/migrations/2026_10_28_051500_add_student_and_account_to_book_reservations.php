<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('book_id')->constrained('students')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->after('student_id')->constrained('accounts')->nullOnDelete();
            $table->index(['student_id', 'reservation_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['account_id']);
            $table->dropIndex(['student_id', 'reservation_status_id']);
            $table->dropColumn(['student_id', 'account_id']);
        });
    }
};
