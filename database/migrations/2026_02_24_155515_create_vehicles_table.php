<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // VIN — the primary vehicle identifier
            $table->string('vin', 17)->nullable();

            // NHTSA-decoded fields (auto-filled after scan)
            $table->smallInteger('year')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('trim')->nullable();
            $table->string('body_style')->nullable();   // Sedan, SUV, Truck, etc.
            $table->string('drive_type')->nullable();   // FWD, RWD, AWD, 4WD
            $table->string('engine')->nullable();       // e.g. "3.5L V6"
            $table->string('color')->nullable();        // Paint color (manually entered)
            $table->string('plate')->nullable();        // License plate (optional)

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'vin']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
