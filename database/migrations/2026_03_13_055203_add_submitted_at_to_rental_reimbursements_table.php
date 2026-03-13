<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_reimbursements', function (Blueprint $table) {
            // Track when claim was submitted to insurance (before payment arrives)
            $table->timestamp('submitted_at')->nullable()->after('work_order_rental_id');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete()->after('submitted_at');

            // Make amount nullable — record is created on submission, amount filled when paid
            $table->decimal('insurance_amount_received', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rental_reimbursements', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropColumn(['submitted_at', 'submitted_by']);
            $table->decimal('insurance_amount_received', 10, 2)->nullable(false)->change();
        });
    }
};
