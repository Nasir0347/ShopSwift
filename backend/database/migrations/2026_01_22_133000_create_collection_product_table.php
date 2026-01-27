<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            // We will add foreign key to products later or now. Ideally now, but product table doesn't exist.
            // So we will use unsignedBigInteger and index, then add constraint in products migration or here if products created first.
            // Since products aren't created, we'll just define the column and index it.
            // Actually, best practice is to create products migration first OR add constraint later.
            // Let's rely on standard practice: I'll create the pivot migration file but maybe I should have created products first?
            // "Categories & Collections" is the current module. Products is next.
            // I'll make this migration depend on products table, so I will CREATE the file but NOT run it until products exist.
            // OR I can just make it unsignedBigInteger for now.
            $table->unsignedBigInteger('product_id'); 
            $table->index('product_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product');
    }
};
