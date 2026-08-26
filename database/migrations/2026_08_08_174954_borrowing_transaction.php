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
        Schema::create('borrowing_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('book_id')->constrained('books');

            $table->foreignId('school_id')->constrained('students');

            //librarian
            $table->foreignId('issued_by_id')->constrained('librarians');
            $table->foreignId('book_reservation_id')->nullable()->constrained('book_reservations')->nullOnDelete();

            //borrow type (outside library or inside library, etc.)
            $table->foreignId('borrow_type_id')->constrained('borrow_types');
            $table->foreignId('issued_condition_id')->constrained('book_conditions');
            $table->foreignId('return_condition_id')->nullable()->constrained('book_conditions');

            //issued and due dates
            $table->dateTime('issued_date');
            $table->dateTime('due_date');

            // the user return the book date
            $table->dateTime('return_date')->nullable();
            $table->foreignId('received_by_id')->nullable()->constrained('librarians');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowing_transactions');
    }
};
