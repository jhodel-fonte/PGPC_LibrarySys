<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('ml_user_token', 64)->nullable()->unique();
        });

        DB::table('accounts')->select('id')->orderBy('id')->chunk(500, function ($accounts) {
            foreach ($accounts as $account) {
                DB::table('accounts')->where('id', $account->id)->update([
                    'ml_user_token' => bin2hex(random_bytes(32)),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropUnique(['ml_user_token']);
            $table->dropColumn('ml_user_token');
        });
    }
};
