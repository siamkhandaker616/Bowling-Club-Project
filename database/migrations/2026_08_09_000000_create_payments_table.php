<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->string('transaction_id')->nullable();
            $table->string('session_key')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('response')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
