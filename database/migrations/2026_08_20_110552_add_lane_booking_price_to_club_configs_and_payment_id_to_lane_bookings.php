<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_configs', function (Blueprint $table) {
            $table->decimal('lane_booking_price', 8, 2)->default(0)->after('total_expenses');
        });

        Schema::table('lane_bookings', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->nullable()->after('queue_position');
        });
    }

    public function down(): void
    {
        Schema::table('club_configs', function (Blueprint $table) {
            $table->dropColumn('lane_booking_price');
        });

        Schema::table('lane_bookings', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
