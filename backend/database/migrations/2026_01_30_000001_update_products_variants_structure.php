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
        Schema::table('products', function (Blueprint $table) {
            $table->json('options')->nullable()->after('vendor')->comment('JSON array of options like [{"name":"Size","values":["S","M"]}]');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('option1')->nullable()->after('product_id');
            $table->string('option2')->nullable()->after('option1');
            $table->string('option3')->nullable()->after('option2');
            
            // We'll keep size/color for now for backward compatibility but populate them from options
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('options');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['option1', 'option2', 'option3']);
        });
    }
};
