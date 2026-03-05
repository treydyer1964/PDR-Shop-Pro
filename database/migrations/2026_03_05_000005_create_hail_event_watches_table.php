<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hail_event_watches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('hail_event_id');
            $table->string('status', 20)->default('watching'); // watching | passed | activated
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('storm_event_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->unique(['tenant_id', 'hail_event_id']); // one watch record per tenant per event

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('hail_event_id')->references('id')->on('hail_events')->cascadeOnDelete();
            $table->foreign('storm_event_id')->references('id')->on('storm_events')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hail_event_watches');
    }
};
