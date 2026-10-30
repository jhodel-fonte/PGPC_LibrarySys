<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('periodical_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_detail_id')->unique()->constrained('book_details')->cascadeOnDelete();
            $table->string('volume', 50)->nullable();
            $table->string('issue_number', 50)->nullable();
            $table->string('issue_label', 255)->nullable();
            $table->date('publication_date')->nullable();
            $table->boolean('borrowable')->default(true);
            $table->unsignedSmallInteger('loan_days')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodical_issues');
    }
};
