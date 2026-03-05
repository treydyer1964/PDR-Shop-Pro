<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hail_alert_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('hail_event_id');
            $table->timestamp('triggered_at');
            $table->string('delivery_method', 10); // email, sms
            $table->string('recipient');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('hail_event_id')->references('id')->on('hail_events')->cascadeOnDelete();
            $table->index(['tenant_id', 'triggered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hail_alert_log');
    }
};
