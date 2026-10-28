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
        Schema::create('ml_evaluation_runs', function (Blueprint $table) {
            $table->id();
            $table->decimal('rmse', 12, 4)->nullable();
            $table->decimal('mae', 12, 4)->nullable();
            $table->decimal('mean', 12, 4)->nullable();
            $table->decimal('median', 12, 4)->nullable();
            $table->decimal('standard_deviation', 12, 4)->nullable();
            $table->decimal('ndcg_at_10', 12, 4)->nullable();
            $table->json('payload')->nullable(); // Raw response from the recommender, kept for auditing
            $table->string('fingerprint', 64)->index(); // Hash of the metric values, used to skip duplicate snapshots
            $table->unsignedInteger('times_checked')->default(1);
            $table->timestamp('evaluated_at')->index();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_evaluation_runs');
    }
};

