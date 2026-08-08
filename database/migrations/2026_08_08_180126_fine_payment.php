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
        Schema::create('fine_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fine_id')->constrained('fines')->cascadeOnDelete();
            $table->foreignId('received_by_id')->constrained('librarians');

            // Payment Data
            $table->dateTime('payment_date');
            $table->decimal('payment_amount', 8, 2);

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
