<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_configs', function (Blueprint $table) {
            $table->id();
            $table->boolean('bad_day_mode')->default(false);
            $table->integer('current_day')->default(1);
            $table->integer('reputation')->default(75);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_configs');
    }
};
