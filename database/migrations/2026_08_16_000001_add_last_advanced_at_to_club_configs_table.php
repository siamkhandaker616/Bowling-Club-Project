<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_configs', function (Blueprint $table) {
            $table->timestamp('last_advanced_at')->nullable()->after('total_expenses');
        });
    }

    public function down(): void
    {
        Schema::table('club_configs', function (Blueprint $table) {
            $table->dropColumn('last_advanced_at');
        });
    }
};
