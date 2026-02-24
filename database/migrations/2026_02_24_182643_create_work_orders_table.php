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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // RO number — auto-generated, unique per tenant (e.g. WO-2026-0001)
            $table->string('ro_number', 20);
            $table->string('job_type', 20); // insurance | customer_pay | wholesale
            $table->string('status', 30)->default('acquired');

            // Financials (set by owner when ready to calculate commissions)
            $table->decimal('invoice_total', 10, 2)->nullable();
            $table->text('notes')->nullable();

            // ── Insurance fields (only for insurance job_type) ─────────────────
            $table->foreignId('insurance_company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('claim_number', 100)->nullable();
            $table->string('policy_number', 100)->nullable();
            $table->string('adjuster_name', 100)->nullable();
            $table->string('adjuster_phone', 20)->nullable();
            $table->string('adjuster_email', 100)->nullable();
            $table->decimal('deductible', 8, 2)->nullable();
            // Has insurance already inspected before we received the car?
            // Yes = we submit a SUPPLEMENT; No = we inspect and submit an ESTIMATE first
            $table->boolean('insurance_pre_inspected')->default(false);
            $table->boolean('has_rental_coverage')->default(false);

            // ── On Hold overlay ────────────────────────────────────────────────
            $table->boolean('on_hold')->default(false);
            $table->timestamp('held_at')->nullable();
            $table->text('hold_reason')->nullable();

            // ── Kicked (returned unrepaired) ───────────────────────────────────
            $table->boolean('kicked')->default(false);
            $table->timestamp('kicked_at')->nullable();
            $table->text('kicked_reason')->nullable();

            // ── Sub-tasks (greyed until activated; timestamps = when completed) ─
            // Teardown — floats in the flow (before OR after insurance approval)
            $table->timestamp('teardown_completed_at')->nullable();

            // Parts — Pre-Repair (discovered at estimate or during repair)
            $table->boolean('needs_parts_pre_repair')->default(false);
            $table->timestamp('parts_pre_repair_ordered_at')->nullable();
            $table->timestamp('parts_pre_repair_received_at')->nullable();

            // Parts — Reassembly (trim, clips, etc.)
            $table->boolean('needs_parts_reassembly')->default(false);
            $table->timestamp('parts_reassembly_ordered_at')->nullable();
            $table->timestamp('parts_reassembly_received_at')->nullable();

            // Body Shop
            $table->boolean('needs_body_shop')->default(false);
            $table->timestamp('body_shop_sent_at')->nullable();
            $table->timestamp('body_shop_returned_at')->nullable();

            // Glass
            $table->boolean('needs_glass')->default(false);
            $table->timestamp('glass_sent_at')->nullable();
            $table->timestamp('glass_returned_at')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'ro_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'vehicle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
