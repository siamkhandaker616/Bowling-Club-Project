<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_messages', function (Blueprint $table) {
            $table->timestamp('respond_at')->nullable()->after('body');
            $table->timestamp('seen_at')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('staff_messages', function (Blueprint $table) {
            $table->dropColumn(['respond_at', 'seen_at']);
        });
    }
};
