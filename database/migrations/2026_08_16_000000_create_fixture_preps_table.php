<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixture_preps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->unique();
            $table->unsignedBigInteger('welcomed_by')->nullable();
            $table->timestamp('welcomed_at')->nullable();
            $table->boolean('kits_ready')->default(false);
            $table->unsignedBigInteger('kits_prepared_by')->nullable();
            $table->timestamp('kits_prepared_at')->nullable();
            $table->boolean('lane_ready')->default(false);
            $table->unsignedBigInteger('lane_prepared_by')->nullable();
            $table->timestamp('lane_prepared_at')->nullable();
            $table->boolean('training_ready')->default(false);
            $table->unsignedBigInteger('training_prepared_by')->nullable();
            $table->timestamp('training_prepared_at')->nullable();
            $table->timestamps();

            $table->foreign('fixture_id')->references('id')->on('fixtures')->cascadeOnDelete();
            $table->foreign('welcomed_by')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('kits_prepared_by')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('lane_prepared_by')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('training_prepared_by')->references('id')->on('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixture_preps');
    }
};
