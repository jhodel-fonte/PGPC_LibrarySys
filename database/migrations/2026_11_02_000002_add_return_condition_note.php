<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('borrowing_transactions', fn (Blueprint $table) => $table->string('return_condition_note', 500)->nullable());
    }

    public function down(): void
    {
        Schema::table('borrowing_transactions', fn (Blueprint $table) => $table->dropColumn('return_condition_note'));
    }
};
