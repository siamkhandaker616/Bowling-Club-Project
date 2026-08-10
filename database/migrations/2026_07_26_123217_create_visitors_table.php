<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('tier', ['regular', 'premium'])->default('regular');
            $table->integer('reputation_score')->default(50);
            $table->boolean('is_banned')->default(false);
            $table->text('ban_reason')->nullable();
            $table->unsignedBigInteger('banned_by_admin_id')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
