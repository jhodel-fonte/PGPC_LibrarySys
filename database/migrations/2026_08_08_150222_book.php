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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_detail_id')->constrained('book_details')->restrictOnDelete();//
            $table->foreignId('book_condition_id')->constrained('book_conditions')->restrictOnDelete();//

            // Inventory Tracking Columns
            $table->string('accession_number')->unique();
            $table->string('barcode')->unique()->nullable();

            // Physical Location and Status
            $table->string('location')->nullable();
            $table->string('status')->default('available');

            // Acquisition Data
            $table->date('date_acquired')->nullable();
            // timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
