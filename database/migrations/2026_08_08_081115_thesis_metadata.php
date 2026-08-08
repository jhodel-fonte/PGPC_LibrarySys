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
        Schema::create('thesis_metadatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_data_id')->constrained('book_datas')->cascadeOnDelete();//
            $table->string('defense_month')->nullable();//
            $table->string('adviser_name')->nullable();//
            $table->string('dean')->nullable();//
            $table->string('program')->nullable();//
            $table->string('year_level')->nullable();//
            $table->decimal('project_cost', 15, 2)->nullable();//
            $table->text('remarks')->nullable();//

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesis_metadatas');
    }
};
