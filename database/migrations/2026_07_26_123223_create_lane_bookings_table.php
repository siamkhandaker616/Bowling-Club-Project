<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lane_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lane_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('time_slot');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->integer('queue_position')->nullable();
            $table->boolean('compensation_claimed')->default(false);
            $table->string('compensation_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lane_bookings');
    }
};
