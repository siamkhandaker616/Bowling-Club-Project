<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['club_manager', 'steward', 'caretaker'])->default('caretaker');
            $table->string('portrait_happy')->nullable();
            $table->string('portrait_disappointed')->nullable();
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('current_salary', 10, 2)->default(0);
            $table->integer('happiness')->default(70);
            $table->integer('performance_score')->default(50);
            $table->integer('honesty_score')->default(50);
            $table->date('hire_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('warnings_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
