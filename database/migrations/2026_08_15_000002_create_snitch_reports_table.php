<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snitch_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_staff_id');
            $table->unsignedBigInteger('accused_staff_id');
            $table->text('quote')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('confrontation_id')->nullable();
            $table->string('steward_note')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('reporter_staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('accused_staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('confrontation_id')->references('id')->on('confrontations')->onDelete('set null');
            $table->index(['status', 'reporter_staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snitch_reports');
    }
};
