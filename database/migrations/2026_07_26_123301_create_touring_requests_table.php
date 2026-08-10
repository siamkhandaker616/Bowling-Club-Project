<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('touring_requests', function (Blueprint $table) {
            $table->id();
            $table->string('team_name');
            $table->string('home_club')->nullable();
            $table->date('arrival_date');
            $table->integer('player_count')->default(0);
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'declined'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('touring_requests');
    }
};
