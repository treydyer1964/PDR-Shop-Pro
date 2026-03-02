<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_segments', function (Blueprint $table) {
            $table->unsignedInteger('odometer_out')->nullable()->after('notes');
            $table->unsignedInteger('odometer_in')->nullable()->after('odometer_out');
            $table->unsignedInteger('miles_driven')->nullable()->after('odometer_in');
            $table->string('fuel_level_out', 4)->nullable()->after('miles_driven');
            $table->string('fuel_level_in', 4)->nullable()->after('fuel_level_out');
        });
    }

    public function down(): void
    {
        Schema::table('rental_segments', function (Blueprint $table) {
            $table->dropColumn([
                'odometer_out',
                'odometer_in',
                'miles_driven',
                'fuel_level_out',
                'fuel_level_in',
            ]);
        });
    }
};
