<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('accounts');
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->string('status')->default('reviewing');
            $table->json('headers')->nullable();
            $table->json('mapping')->nullable();
            $table->unsignedInteger('header_row')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::create('student_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('data');
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('email_status')->nullable();
            $table->text('email_error')->nullable();
            $table->timestamps();
            $table->unique(['student_import_id', 'row_number']);
            $table->index(['student_import_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_import_rows');
        Schema::dropIfExists('student_imports');
    }
};
