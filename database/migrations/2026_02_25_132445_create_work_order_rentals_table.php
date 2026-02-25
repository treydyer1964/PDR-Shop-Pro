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
        Schema::create('work_order_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rental_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('has_insurance_coverage')->default(false);
            $table->decimal('insurance_daily_rate', 8, 2)->nullable(); // rate insurance will reimburse per day
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'work_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_rentals');
    }
};
