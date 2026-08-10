<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confrontations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('accused_staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('incident_type');
            $table->text('incident_description')->nullable();
            $table->boolean('db_verified')->default(false);
            $table->enum('staff_response', ['confessed', 'bs', 'innocent'])->nullable();
            $table->text('investigation_result')->nullable();
            $table->enum('manager_verdict', ['upheld', 'dismissed', 'penalized'])->nullable();
            $table->date('date');
            $table->json('happiness_impacts')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confrontations');
    }
};
