<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservation_waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_detail_id')->constrained('book_details')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status')->default('waiting');
            $table->foreignId('book_reservation_id')->nullable()->constrained('book_reservations')->nullOnDelete();
            $table->timestamps();
            $table->index(['book_detail_id', 'status', 'id']);
            $table->index(['account_id', 'book_detail_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_waitlist');
    }
};
