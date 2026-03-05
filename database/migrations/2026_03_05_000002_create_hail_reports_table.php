<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hail_reports', function (Blueprint $table) {
            $table->id();
            // No tenant_id — global NOAA data shared across all tenants
            $table->unsignedBigInteger('hail_event_id')->nullable()->index();
            $table->date('report_date')->index();
            $table->time('report_time')->nullable();
            $table->decimal('lat', 8, 5);
            $table->decimal('lng', 9, 5);
            $table->decimal('size_inches', 4, 2); // e.g. 1.75"
            $table->string('location_name', 150)->nullable();
            $table->string('county', 100)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('source', 20)->default('spc');
            $table->timestamps();

            $table->foreign('hail_event_id')->references('id')->on('hail_events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hail_reports');
    }
};
