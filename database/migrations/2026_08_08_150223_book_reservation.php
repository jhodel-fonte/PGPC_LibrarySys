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
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('reservation_status_id')->constrained('reservation_statuses')->restrictOnDelete();
            $table->dateTime('reservation_date');
            $table->dateTime('due_date');

            $table->dateTime('approved_date')->nullable();
            $table->dateTime('fulfilled_date')->nullable();
            $table->dateTime('cancelled_date')->nullable();

            // Other Data
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_reservations');
    }
};
