<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('historical_borrowing_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_file');
            $table->unsignedInteger('source_row');
            $table->string('borrowed_date')->nullable();
            $table->string('accession_number')->nullable()->index();
            $table->string('source_image')->nullable();
            $table->string('page')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->unique(['source_file', 'source_row']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_borrowing_logs');
    }
};
