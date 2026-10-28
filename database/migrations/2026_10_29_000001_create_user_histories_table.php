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
        Schema::create('user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('encrypter_id')->nullable()->index();
            $table->string('isbn', 32)->index(); // Intentionally no foreign key constraint
            $table->unsignedTinyInteger('rating')->default(0); // 0 = no interaction, 1 = view, 2 = borrowed
            $table->timestamps();

            // Enforce single row per user and ISBN to support upserting highest rating
            $table->unique(['user_id', 'isbn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_histories');
    }
};
