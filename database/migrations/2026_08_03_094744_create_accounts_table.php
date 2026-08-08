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
        Schema::create('account_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status_name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('status_id')->nullable()->constrained('account_statuses')->nullOnDelete();

            //basic login details
            $table->string('username')->unique();
            $table->string('password_hash');

            //email details
            $table->string('email')->unique();
            $table->boolean('is_email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();

            //account attempt
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->timestamp('last_login')->nullable();
            $table->timestamp('password_changed_at')->nullable();

            $table->string('provider')->nullable();//googleauth
            $table->string('provider_id')->nullable();

            $table->rememberToken();//remember me token
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('accounts');


        Schema::dropIfExists('roles');
        Schema::dropIfExists('account_statuses');
        Schema::dropIfExists('password_reset_tokens');
    }
};
