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
        // roles table is a simple lookup — values are managed by the Role enum
        Schema::create('roles', function (Blueprint $table) {
            $table->string('name')->primary(); // matches Role enum value
            $table->string('label');           // human-readable
        });

        // Pivot: users can have multiple roles per tenant
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_name');
            $table->foreign('role_name')->references('name')->on('roles')->cascadeOnDelete();
            $table->unique(['user_id', 'role_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
