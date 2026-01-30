<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;

echo "=== CHECK PRODUCT IMAGES FOR VARIANT 51/52 ===\n\n";

// Get variants 51 and 52
$variants = ProductVariant::whereIn('id', [51, 52])->get();
echo "Variants found:\n";
foreach ($variants as $v) {
    echo "  - ID: {$v->id}, Title: {$v->title}, product_id: {$v->product_id}, image_id: " . ($v->image_id ?? 'NULL') . "\n";
}

if ($variants->count() > 0) {
    $productId = $variants->first()->product_id;
    echo "\nProduct ID: {$productId}\n";
    
    echo "\nProduct images for this product:\n";
    $images = ProductImage::where('product_id', $productId)->get();
    foreach ($images as $img) {
        echo "  - ID: {$img->id}, path: {$img->image_path}\n";
    }
    
    // Check for specific paths
    $testPaths = [
        'http://localhost:8000/storage/uploads/nDHvu3hY9cuuTcj3vnDj.png',
        'http://localhost:8000/storage/uploads/tZlwGK8rhipuzFdzAZi2.jpg'
    ];
    
    echo "\nSearching for these paths in product images:\n";
    foreach ($testPaths as $path) {
        $found = ProductImage::where('product_id', $productId)->where('image_path', $path)->first();
        if ($found) {
            echo "  ✅ FOUND: {$path} -> image_id: {$found->id}\n";
        } else {
            echo "  ❌ NOT FOUND: {$path}\n";
            
            // Try filename match
            $filename = basename(parse_url($path, PHP_URL_PATH));
            $byFilename = ProductImage::where('product_id', $productId)->where('image_path', 'LIKE', '%' . $filename)->first();
            if ($byFilename) {
                echo "     But found by filename: {$byFilename->image_path} -> image_id: {$byFilename->id}\n";
            }
        }
    }
}

echo "\n=== DONE ===\n";
