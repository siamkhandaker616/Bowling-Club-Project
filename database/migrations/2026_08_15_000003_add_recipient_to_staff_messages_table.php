<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_messages', function (Blueprint $table) {
            $table->foreignId('recipient_staff_id')->nullable()->after('staff_id')
                ->constrained('staff')
                ->nullOnDelete();

            $table->index(['recipient_staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('staff_messages', function (Blueprint $table) {
            $table->dropIndex(['recipient_staff_id', 'date']);
            $table->dropConstrainedForeignId('recipient_staff_id');
        });
    }
};
