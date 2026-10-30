<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->boolean('setup_email_required')->default(false);
            $table->boolean('setup_password_prompt')->default(false);
        });
    }

    public function down(): void
    {
        if (DB::table('accounts')->whereNull('email')->exists()) {
            throw new \RuntimeException('Cannot roll back while accounts without an email exist.');
        }
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['setup_email_required', 'setup_password_prompt']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
