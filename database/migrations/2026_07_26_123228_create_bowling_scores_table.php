<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bowling_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('score');
            $table->json('frames_data')->nullable();
            $table->timestamp('played_at');
            $table->boolean('is_high_score')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bowling_scores');
    }
};
