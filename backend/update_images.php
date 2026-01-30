<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

$mapping = [
    'Headphone' => '/images/products/headphones.png',
    'Smartwatch' => '/images/products/smartwatch.png',
    'Backpack' => '/images/products/backpack.png',
    'Coffee' => '/images/products/coffeemaker.png',
    'Yoga' => '/images/products/yogamat.png',
];

foreach ($mapping as $keyword => $mapPath) {
    // Escape keyword properly or just use variable
    $product = Product::where('title', 'like', "%$keyword%")->first();
    
    if ($product) {
        echo "Updating images for: {$product->title}\n";
        
        // Remove old images
        $product->images()->delete();
        
        // Add new image (Is Primary, Sort Order 0)
        $product->images()->create([
            'image_path' => $mapPath,
            'is_primary' => true,
            'sort_order' => 0
        ]);
        
        echo "✅ Set image to $mapPath\n";
    } else {
        echo "❌ Product not found for keyword: $keyword\n";
    }
}
