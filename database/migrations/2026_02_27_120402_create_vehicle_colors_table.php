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
        Schema::create('vehicle_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Seed common vehicle colors
        $colors = [
            'White', 'Black', 'Silver', 'Gray', 'Charcoal', 'Graphite',
            'Red', 'Dark Red', 'Maroon', 'Burgundy',
            'Blue', 'Dark Blue', 'Navy Blue', 'Light Blue',
            'Green', 'Dark Green', 'Teal',
            'Brown', 'Beige', 'Champagne', 'Tan',
            'Gold', 'Bronze', 'Orange', 'Yellow',
            'Purple', 'Pink',
            'Pearl White', 'Gunmetal',
        ];
        foreach ($colors as $i => $color) {
            \DB::table('vehicle_colors')->insert([
                'name' => $color,
                'sort_order' => $i,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_colors');
    }
};
