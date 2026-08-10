<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lanes', function (Blueprint $table) {
            $table->id();
            $table->integer('lane_number')->unique();
            $table->enum('status', ['open', 'occupied', 'maintenance', 'reserved'])->default('open');
            $table->unsignedBigInteger('current_booking_id')->nullable();
            $table->timestamp('last_maintained_at')->nullable();
            $table->integer('oil_level')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lanes');
    }
};
