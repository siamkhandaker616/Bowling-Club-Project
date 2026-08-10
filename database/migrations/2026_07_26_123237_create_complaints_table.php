<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('raised_by_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('type');
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'dismissed'])->default('open');
            $table->text('resolution')->nullable();
            $table->string('compensation_type')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
