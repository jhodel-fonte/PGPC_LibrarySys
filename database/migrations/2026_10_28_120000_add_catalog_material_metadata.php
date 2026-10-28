<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('is_borrowable')->default(true);
            $table->unsignedSmallInteger('default_loan_days')->nullable();
            $table->timestamps();
        });

        Schema::table('book_details', function (Blueprint $table) {
            $table->foreignId('material_type_id')->nullable()->after('book_type_id')->constrained('material_types')->nullOnDelete();
            $table->string('lccn')->nullable()->after('issn')->index();
        });

        Schema::table('publishers', function (Blueprint $table) {
            $table->string('address')->nullable()->after('name');
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('book_data_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_data_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['book_data_id', 'subject_id']);
        });

        Schema::create('material_item_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('asset_tag')->nullable()->unique();
            $table->string('serial_number')->nullable();
            $table->text('condition_notes')->nullable();
            $table->decimal('replacement_cost', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('material_item_metadata');
        Schema::dropIfExists('book_data_subject');
        Schema::dropIfExists('subjects');

        Schema::table('publishers', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        Schema::table('book_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('material_type_id');
            $table->dropColumn('lccn');
        });

        Schema::dropIfExists('material_types');
    }
};
