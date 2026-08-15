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
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('terms_acknowledged_version')->default(0)->after('password_hash');
            $table->timestamp('terms_acknowledged_at')->nullable()->after('terms_acknowledged_version');
            
            $table->integer('privacy_acknowledged_version')->default(0)->after('terms_acknowledged_at');
            $table->timestamp('privacy_acknowledged_at')->nullable()->after('privacy_acknowledged_version');
            
            $table->integer('cookie_acknowledged_version')->default(0)->after('privacy_acknowledged_at');
            $table->timestamp('cookie_acknowledged_at')->nullable()->after('cookie_acknowledged_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'terms_acknowledged_version',
                'terms_acknowledged_at',
                'privacy_acknowledged_version',
                'privacy_acknowledged_at',
                'cookie_acknowledged_version',
                'cookie_acknowledged_at'
            ]);
        });
    }
};
