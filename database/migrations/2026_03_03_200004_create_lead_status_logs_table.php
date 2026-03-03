<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('lead_id');
            $table->string('status', 30);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->timestamps();

            $table->index('lead_id');
            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_status_logs');
    }
};
