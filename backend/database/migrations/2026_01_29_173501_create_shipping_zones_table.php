<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "North America", "Europe"
            $table->json('countries'); // Array of country codes ['US', 'CA']
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Standard", "Express"
            $table->string('type'); // 'price_based', 'weight_based'
            $table->decimal('price', 10, 2); // Shipping cost
            $table->decimal('min_limit', 10, 2)->nullable(); // Min price or weight
            $table->decimal('max_limit', 10, 2)->nullable(); // Max price or weight
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
