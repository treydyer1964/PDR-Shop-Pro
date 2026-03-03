<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storm_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->date('event_date');
            $table->string('city', 100)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('storm_type', 20)->default('hail');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storm_events');
    }
};
