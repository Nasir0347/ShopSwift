<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ProductService;
use App\Models\Product;

echo "=== TEST VARIANT IMAGE SAVE ===\n\n";

$productService = app(ProductService::class);

// Find an existing product with images to test with
$existingProduct = Product::with('images')->whereHas('images')->first();

if (!$existingProduct) {
    echo "No product with images found. Creating test data...\n";
    exit;
}

echo "Testing with product ID: {$existingProduct->id} - {$existingProduct->title}\n";
echo "Product images:\n";
foreach ($existingProduct->images as $img) {
    echo "   - ID: {$img->id}, path: {$img->image_path}\n";
}

// Get one image path to test with
$testImagePath = $existingProduct->images->first()->image_path;
echo "\nTest image_path: {$testImagePath}\n";

// Prepare update data with variant that has image_path
$updateData = [
    'title' => $existingProduct->title,
    'variants' => [
        [
            'title' => 'Test Variant With Image',
            'price' => 99.99,
            'option1' => 'Test',
            'image_path' => $testImagePath,  // This should get resolved to image_id
            'inventory_quantity' => 10
        ]
    ],
    'images' => $existingProduct->images->map(fn($i) => [
        'id' => $i->id,
        'image_path' => $i->image_path
    ])->toArray()
];

echo "\nCalling updateProduct with image_path: {$testImagePath}\n";
echo "Update variants data:\n";
print_r($updateData['variants']);

try {
    $result = $productService->updateProduct($existingProduct, $updateData);
    
    echo "\n=== RESULT ===\n";
    echo "Variants after update:\n";
    foreach ($result->variants as $v) {
        echo "   - ID: {$v->id}, Title: {$v->title}, image_id: " . ($v->image_id ?? 'NULL') . "\n";
        if ($v->image) {
            echo "     Image: {$v->image->image_path}\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n=== TEST COMPLETE ===\n";
