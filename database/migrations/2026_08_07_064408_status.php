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
        //book condition table
        Schema::create('book_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamps();
        });

        //reservation status table
        Schema::create('reservation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('book_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_types');
        Schema::dropIfExists('reservation_statuses');
        Schema::dropIfExists('book_conditions');
    }
};
