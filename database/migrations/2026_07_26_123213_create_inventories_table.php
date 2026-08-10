<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->integer('quantity')->default(0);
            $table->integer('max_quantity')->default(100);
            $table->enum('condition', ['good', 'worn', 'broken'])->default('good');
            $table->integer('reorder_threshold')->default(10);
            $table->decimal('cost_per_unit', 10, 2)->default(0);
            $table->timestamp('last_restocked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
