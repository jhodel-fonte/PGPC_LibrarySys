<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservation_notification_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('account_id');
            $table->primary(['reservation_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_notification_deliveries');
    }
};
