<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->dateTime('due_reminder_for')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->dropColumn('due_reminder_for');
        });
    }
};
