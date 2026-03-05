<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesh_daily_records', function (Blueprint $table) {
            $table->id();
            $table->date('record_date')->unique();     // One row per calendar date
            $table->string('png_path')->nullable();    // storage/app/public relative path
            $table->string('npy_path')->nullable();    // absolute path on disk
            $table->float('max_size_inches')->default(0); // peak MESH value that day
            $table->unsignedSmallInteger('frame_count')->default(0); // frames processed
            $table->timestamp('last_frame_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesh_daily_records');
    }
};
