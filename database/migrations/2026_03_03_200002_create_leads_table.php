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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('status', 30)->default('new');
            $table->string('source', 30)->default('door_to_door');
            $table->string('job_type_interest', 30)->nullable(); // insurance/customer_pay/wholesale
            $table->string('vehicle_year', 4)->nullable();
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('territory_id')->nullable();
            $table->unsignedBigInteger('converted_work_order_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('territory_id')->references('id')->on('territories')->nullOnDelete();
            $table->foreign('converted_work_order_id')->references('id')->on('work_orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
