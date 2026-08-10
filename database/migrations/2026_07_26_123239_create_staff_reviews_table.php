<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('lane_bookings')->cascadeOnDelete();
            $table->integer('rating');
            $table->text('body')->nullable();
            $table->boolean('was_polite')->nullable();
            $table->boolean('caused_issues')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_reviews');
    }
};
