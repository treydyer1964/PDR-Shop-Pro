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
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('color', 7)->default('#3b82f6');
            $table->json('boundary')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territories');
    }
};
