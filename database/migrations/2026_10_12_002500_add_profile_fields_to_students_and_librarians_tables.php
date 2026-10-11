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
        Schema::table('students', function (Blueprint $table) {
            $table->string('date_of_birth')->nullable()->after('contact_num');
            $table->string('image_url')->nullable()->after('date_of_birth');
            $table->string('enrollment_status')->nullable()->after('year_level');
            $table->string('enrollment_year')->default(date('Y'))->after('enrollment_status');
        });

        Schema::table('librarians', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('contact_num');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'image_url',
                'enrollment_status',
                'enrollment_year',
            ]);
        });

        Schema::table('librarians', function (Blueprint $table) {
            $table->dropColumn(['image_url']);
        });
    }
};
