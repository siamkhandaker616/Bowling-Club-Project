<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_personalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personality_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['staff_id', 'personality_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_personalities');
    }
};
