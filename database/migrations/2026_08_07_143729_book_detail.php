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
        //publisher table
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        //book_details table
        Schema::create('book_details', function (Blueprint $table) {
            $table->id();//

            //Foreign Key
            // cascadeOnDelete - if the main book data is deleted, its editions are too.
            $table->foreignId('book_data_id')->constrained('book_datas')->cascadeOnDelete();//

            //identifiers & Publication Info
            $table->string('isbn')->nullable();//
            $table->string('issn')->nullable();          //
            $table->year('publication_year')->nullable();//
            $table->year('copyright_year')->nullable(); // Added for accurate citations
            $table->string('edition')->nullable();//

            //Physical Attributes
            $table->unsignedInteger('pages')->nullable();//
            $table->string('format')->required();//
            $table->foreignId('book_type_id')->constrained('book_types')->restrictOnDelete();

            //Library Organization
            $table->string('call_number')->required();//
            $table->string('classification')->nullable();//

            //Publisher Link
            $table->foreignId('publisher_id')->constrained('publishers')->restrictOnDelete();//

            // 6. Media
            $table->string('cover_image')->nullable();//
            $table->string('url')->nullable();//

            // timestamps
            $table->timestamps(); //
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_details');
        Schema::dropIfExists('publishers');


    }
};
