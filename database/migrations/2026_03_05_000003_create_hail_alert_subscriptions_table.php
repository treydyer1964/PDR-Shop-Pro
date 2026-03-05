<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hail_alert_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique(); // one per tenant
            $table->decimal('home_lat', 8, 5)->nullable();
            $table->decimal('home_lng', 9, 5)->nullable();
            $table->string('home_address')->nullable();
            $table->unsignedSmallInteger('radius_miles')->default(150);
            $table->decimal('min_size_inches', 3, 2)->default(1.00);
            $table->boolean('email_alerts')->default(true);
            $table->boolean('sms_alerts')->default(false);
            $table->unsignedSmallInteger('alert_cooldown_hours')->default(4);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hail_alert_subscriptions');
    }
};
