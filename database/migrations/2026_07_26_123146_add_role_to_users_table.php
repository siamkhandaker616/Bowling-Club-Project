<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('email');
            $table->string('avatar')->nullable()->after('role');
            $table->string('phone')->nullable()->after('avatar');
            $table->boolean('is_npc')->default(false)->after('phone');
            $table->boolean('is_active')->default(true)->after('is_npc');
            $table->string('google_id')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'phone', 'is_npc', 'is_active', 'google_id']);
        });
    }
};
