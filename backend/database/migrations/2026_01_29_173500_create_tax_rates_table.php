<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "VAT", "Sales Tax"
            $table->decimal('rate', 8, 4); // Percentage, e.g., 20.0000
            $table->string('country_code', 2); // ISO 3166-1 alpha-2
            $table->string('region_code')->nullable(); // State/Province code
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_shipping_tax')->default(false);
            $table->integer('priority')->default(0); // Application order
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
