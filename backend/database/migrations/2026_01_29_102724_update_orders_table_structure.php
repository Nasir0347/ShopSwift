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
        Schema::table('orders', function (Blueprint $table) {
            // Rename total_amount to total for consistency
            $table->renameColumn('total_amount', 'total');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            // Add new fields
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('shipping_amount', 10, 2)->default(0)->after('tax_amount');
            $table->text('notes')->nullable()->after('shipping_address');
            $table->enum('fulfillment_status', ['unfulfilled', 'partial', 'fulfilled'])->default('unfulfilled')->after('payment_status');
            $table->string('order_number')->unique()->nullable()->after('id');
            
            // Add indexes
            $table->index('user_id');
            $table->index('status');
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at', 'status']);
            $table->dropColumn(['tax_amount', 'shipping_amount', 'notes', 'fulfillment_status', 'order_number']);
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('total', 'total_amount');
        });
    }
};
