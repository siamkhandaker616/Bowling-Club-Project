<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ban_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_staff_id')->constrained('staff')->cascadeOnDelete();
            $table->text('reason');
            $table->text('evidence')->nullable();
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->unsignedBigInteger('reviewed_by_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ban_requests');
    }
};
