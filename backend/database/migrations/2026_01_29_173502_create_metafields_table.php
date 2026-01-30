<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metafields', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // owner_type, owner_id (Product, Variant, Order, etc.)
            $table->string('namespace'); // e.g., "custom", "global"
            $table->string('key');
            $table->text('value');
            $table->string('type'); // string, integer, json, boolean
            $table->timestamps();
            
            $table->unique(['owner_type', 'owner_id', 'namespace', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metafields');
    }
};
