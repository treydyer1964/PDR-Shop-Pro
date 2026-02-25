<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20);       // INSURANCE, CUSTOMER, OTHER
            $table->string('method', 20);       // CHECK, CARD, CASH, ACH, OTHER
            $table->decimal('amount', 10, 2);
            $table->date('received_on')->nullable();
            $table->string('reference', 100)->nullable();   // check #, ACH ref, etc.
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['tenant_id', 'work_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_payments');
    }
};
