<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cash', 'time_off', 'recognition']);
            $table->text('reason')->nullable();
            $table->decimal('amount_or_hours', 8, 2)->default(0);
            $table->date('date');
            $table->foreignId('issued_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
