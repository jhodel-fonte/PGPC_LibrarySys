<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_histories', function (Blueprint $table) {
            $table->boolean('is_seeded')->default(false)->index();
        });

        $demoAccounts = [...array_map(fn ($id) => "student{$id}", range(1, 100)), 'staff1', 'staff2', 'staff3', 'superadmin'];
        $accountIds = DB::table('accounts')->whereIn('username', $demoAccounts)->pluck('id');
        if ($accountIds->isNotEmpty()) {
            DB::table('user_histories')->whereIn('user_id', $accountIds)->update(['is_seeded' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('user_histories', function (Blueprint $table) {
            $table->dropColumn('is_seeded');
        });
    }
};
