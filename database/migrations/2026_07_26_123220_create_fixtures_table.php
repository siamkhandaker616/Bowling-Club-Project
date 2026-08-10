<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->foreignId('lane_id')->nullable()->constrained('lanes')->nullOnDelete();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->enum('status', ['upcoming', 'live', 'completed'])->default('upcoming');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
