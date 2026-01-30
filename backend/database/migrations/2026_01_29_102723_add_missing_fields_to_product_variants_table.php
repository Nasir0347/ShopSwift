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
        Schema::table('product_variants', function (Blueprint $table) {
            // Note: title, cost_per_item, and barcode already exist from previous migrations
            // Only adding truly missing fields
            $table->decimal('weight', 10, 2)->nullable()->after('barcode');
            $table->string('weight_unit')->default('kg')->after('weight');
            $table->integer('position')->default(0)->after('weight_unit');
            $table->softDeletes();
            
            // Add indexes
            $table->index('product_id');
            $table->index('sku');
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['barcode']);
            $table->dropSoftDeletes();
            $table->dropColumn(['weight', 'weight_unit', 'position']);
        });
    }
};
