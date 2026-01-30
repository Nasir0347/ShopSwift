<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Product;

echo "=== VARIANT IMAGES DEBUG ===\n\n";

// Check variants with image_id
echo "1. Variants with image_id set:\n";
$variantsWithImages = ProductVariant::whereNotNull('image_id')->get();
if ($variantsWithImages->count() > 0) {
    foreach ($variantsWithImages as $v) {
        echo "   - Variant ID: {$v->id}, Title: {$v->title}, image_id: {$v->image_id}\n";
    }
} else {
    echo "   NONE - No variants have image_id set!\n";
}

echo "\n2. All variants (first 10):\n";
$allVariants = ProductVariant::limit(10)->get(['id', 'title', 'image_id', 'product_id']);
foreach ($allVariants as $v) {
    echo "   - ID: {$v->id}, Title: {$v->title}, image_id: " . ($v->image_id ?? 'NULL') . ", product_id: {$v->product_id}\n";
}

echo "\n3. All product images (first 10):\n";
$allImages = ProductImage::limit(10)->get(['id', 'product_id', 'image_path']);
foreach ($allImages as $img) {
    echo "   - ID: {$img->id}, product_id: {$img->product_id}, path: {$img->image_path}\n";
}

echo "\n4. Check VariantResource output for a sample variant:\n";
$sampleVariant = ProductVariant::with('image')->first();
if ($sampleVariant) {
    $resource = new App\Http\Resources\VariantResource($sampleVariant);
    echo json_encode($resource->resolve(), JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
