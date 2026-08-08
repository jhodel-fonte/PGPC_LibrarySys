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
        Schema::create('fines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('borrowing_transaction_id')->constrained('borrowing_transactions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('fine_type_id')->constrained('fine_types');

            // Fine due date
            $table->date('fine_due_date')->nullable();//for user notification mostly

            // (123456.12)
            $table->decimal('amount', 8, 2);

            $table->text('note')->nullable();

            // Tracks the current state of the fine
            $table->enum('fine_status', ['unpaid', 'paid', 'waived'])->default('unpaid');

            //date paid
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
