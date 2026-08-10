<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('lane_bookings')->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lane_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('time_slot');
            $table->integer('position');
            $table->enum('status', ['waiting', 'notified', 'expired'])->default('waiting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_queues');
    }
};
