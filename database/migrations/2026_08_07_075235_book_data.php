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
        //book metadata table
        Schema::create('book_datas', function (Blueprint $table) {
            $table->id();
            $table->string('book_title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('series_title')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();

            $table->integer('copyright_year')->nullable();
            $table->string('marc_record')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });

        //book_data_category pivot table
        Schema::create('book_data_author', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_data_id')->constrained()->onDelete('cascade');
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['book_data_id', 'author_id']);
        });

        //book_data_category pivot table
        Schema::create('book_data_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_data_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['book_data_id', 'category_id']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_data_author');
        Schema::dropIfExists('book_data_category');
        Schema::dropIfExists('book_datas');
    }
};
