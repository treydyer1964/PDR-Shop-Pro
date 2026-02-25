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
        Schema::create('work_order_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');                      // Role enum value
            $table->decimal('amount', 10, 2);            // Calculated commission amount
            $table->decimal('split_pct', 5, 2)->nullable();   // Split % used (PDR Tech / Advisor)
            $table->decimal('rate_pct', 8, 4)->nullable();    // Commission rate % used
            $table->string('notes')->nullable();         // Human-readable calculation summary
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('pay_run_id')->nullable(); // Set when included in a pay run (Phase 8)
            $table->timestamps();

            $table->index(['tenant_id', 'work_order_id']);
            $table->index(['tenant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_commissions');
    }
};
