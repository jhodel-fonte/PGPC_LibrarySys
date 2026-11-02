<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('password_change_reminder')->default(false);
        });
        DB::table('accounts')->where('setup_password_prompt', true)->update([
            'password_change_reminder' => true,
            'setup_password_prompt' => false,
        ]);
    }

    public function down(): void
    {
        Schema::table('accounts', fn (Blueprint $table) => $table->dropColumn('password_change_reminder'));
    }
};
