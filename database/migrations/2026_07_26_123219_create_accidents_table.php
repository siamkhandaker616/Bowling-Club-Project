<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->enum('severity', ['minor', 'moderate', 'major'])->default('minor');
            $table->text('description')->nullable();
            $table->boolean('resolved')->default(false);
            $table->text('resolution')->nullable();
            $table->unsignedBigInteger('affected_booking_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accidents');
    }
};
