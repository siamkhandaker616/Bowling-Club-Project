<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_a_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('staff_b_id')->constrained('staff')->cascadeOnDelete();
            $table->enum('level', ['hostile', 'neutral', 'friendly', 'trusted'])->default('neutral');
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->unique(['staff_a_id', 'staff_b_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_relationships');
    }
};
