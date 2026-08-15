<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->string('bubble_type')->default('speech');
            $table->string('kind')->default('chatter');
            $table->text('body');
            $table->date('date');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->index(['date', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_messages');
    }
};
