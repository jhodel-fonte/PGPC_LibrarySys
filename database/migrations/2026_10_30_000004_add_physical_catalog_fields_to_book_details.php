<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('book_details', function (Blueprint $table) {
            $table->string('catalog_kind', 20)->default('book')->index();
            $table->decimal('daily_fine', 8, 2)->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->text('package_contents')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('book_details', function (Blueprint $table) {
            $table->dropIndex(['catalog_kind']);
            $table->dropColumn(['catalog_kind', 'daily_fine', 'manufacturer', 'model_number', 'package_contents']);
        });
    }
};
