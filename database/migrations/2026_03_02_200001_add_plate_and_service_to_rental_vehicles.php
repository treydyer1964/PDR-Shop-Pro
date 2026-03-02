<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->string('plate_number', 20)->nullable()->after('color');
            $table->unsignedInteger('current_odometer')->nullable()->after('plate_number');
            $table->unsignedInteger('last_service_odometer')->nullable()->after('current_odometer');
            $table->unsignedInteger('service_interval_miles')->default(3000)->after('last_service_odometer');
            $table->unsignedInteger('service_alert_threshold_miles')->default(500)->after('service_interval_miles');
        });
    }

    public function down(): void
    {
        Schema::table('rental_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'plate_number',
                'current_odometer',
                'last_service_odometer',
                'service_interval_miles',
                'service_alert_threshold_miles',
            ]);
        });
    }
};
