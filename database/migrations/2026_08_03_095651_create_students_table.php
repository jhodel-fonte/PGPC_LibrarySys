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
        Schema::create('library_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');

            $table->timestamps();
        });

        //students table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();//account id

            $table->string('school_id_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');

            $table->string('contact_num')->nullable();

            //for violation tracking
            $table->foreignId('library_status_id')->constrained('library_statuses')->restrictOnDelete();
            $table->string('note')->nullable();

            $table->string('program')->nullable();
            $table->string('year_level')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('library_statuses');
    }
};
