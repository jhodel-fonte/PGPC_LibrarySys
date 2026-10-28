<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('source_file_name');
            $table->string('source_file_path')->nullable();
            $table->string('source_type', 20);
            $table->string('status', 20)->default('reviewing');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('catalog_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_data');
            $table->string('status', 20)->default('pending');
            $table->json('errors')->nullable();
            $table->foreignId('book_data_id')->nullable()->constrained('book_datas')->nullOnDelete();
            $table->foreignId('book_detail_id')->nullable()->constrained('book_details')->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
            $table->timestamps();
            $table->unique(['catalog_import_id', 'row_number']);
            $table->index(['catalog_import_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_import_rows');
        Schema::dropIfExists('catalog_imports');
    }
};
