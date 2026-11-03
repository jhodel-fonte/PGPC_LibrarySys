<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->dateTime('lost_at')->nullable();
            $table->foreignId('lost_reported_by_id')->nullable()->constrained('librarians');
            $table->text('lost_note')->nullable();
            $table->dateTime('lost_resolved_at')->nullable();
            $table->foreignId('lost_resolved_by_id')->nullable()->constrained('librarians');
            $table->text('lost_resolution_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('borrowing_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lost_reported_by_id');
            $table->dropConstrainedForeignId('lost_resolved_by_id');
            $table->dropColumn(['lost_at', 'lost_note', 'lost_resolved_at', 'lost_resolution_note']);
        });
    }
};
