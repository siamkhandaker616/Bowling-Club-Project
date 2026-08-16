<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_messages', function (Blueprint $table) {
            $table->foreignId('confrontation_id')->nullable()->after('recipient_staff_id')
                ->constrained('confrontations')->nullOnDelete();
        });

        Schema::table('confrontations', function (Blueprint $table) {
            $table->enum('manager_verdict', ['upheld', 'dismissed', 'penalized', 'reporter_penalized'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('staff_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confrontation_id');
        });

        Schema::table('confrontations', function (Blueprint $table) {
            $table->enum('manager_verdict', ['upheld', 'dismissed', 'penalized'])->nullable()->change();
        });
    }
};
