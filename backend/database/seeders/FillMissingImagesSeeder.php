<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class FillMissingImagesSeeder extends Seeder
{
    public function run()
    {
        // Get products that don't have images
        $products = Product::doesntHave('images')->get();

        $imageUrl = '/generic_product_box.png'; // Path relative to public or fully qualified if needed

        foreach ($products as $product) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $imageUrl,
                'is_primary' => true,
                'sort_order' => 0
            ]);
            echo "Assigned image to Product ID: {$product->id}\n";
        }
        
        echo "Backfill complete. Assigned to " . $products->count() . " products.\n";
    }
}
