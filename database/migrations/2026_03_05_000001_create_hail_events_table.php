<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hail_events', function (Blueprint $table) {
            $table->id();
            // No tenant_id — global clusters derived from NOAA data
            $table->date('event_date')->index();
            $table->decimal('centroid_lat', 8, 5);
            $table->decimal('centroid_lng', 9, 5);
            $table->decimal('max_size_inches', 4, 2);
            $table->decimal('min_size_inches', 4, 2);
            $table->unsignedSmallInteger('report_count')->default(1);
            $table->decimal('coverage_radius_miles', 6, 2)->nullable();
            $table->string('primary_state', 50)->nullable();
            $table->string('primary_county', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hail_events');
    }
};
