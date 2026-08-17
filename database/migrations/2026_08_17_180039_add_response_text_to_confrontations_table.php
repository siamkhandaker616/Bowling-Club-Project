<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('confrontations', function (Blueprint $table) {
            $table->text('response_text')->nullable()->after('staff_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('confrontations', function (Blueprint $table) {
            $table->dropColumn('response_text');
        });
    }
};
