<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->string('review_type');
            $table->unsignedBigInteger('voter_id');
            $table->enum('vote', ['helpful', 'not_helpful']);
            $table->timestamps();

            $table->index(['review_id', 'review_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_votes');
    }
};
