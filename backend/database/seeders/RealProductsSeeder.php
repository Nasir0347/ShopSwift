<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Str;

class RealProductsSeeder extends Seeder
{
    public function run()
    {
        // Define Real Products
        $products = [
            [
                'title' => 'Modern Charcoal Backpack',
                'description' => 'A stylish, modern charcoal grey laptop backpack. Perfect for daily commutes and travel, featuring water-resistant fabric and a dedicated laptop compartment.',
                'status' => 'active',
                'product_type' => 'Accessories',
                'vendor' => 'UrbanGear',
                'image' => '/modern_backpack.png',
                'variants' => [
                    [
                        'option1' => 'Standard', 
                        'price' => 79.99, 
                        'compare_at_price' => 99.99,
                        'sku' => 'BP-CHAR-001', 
                        'inventory' => 50
                    ]
                ]
            ],
            [
                'title' => 'Premium Wireless Headphones',
                'description' => 'Experience studio-quality sound with our premium noise-cancelling headphones. Matte black finish, 30-hour battery life, and ultra-comfortable ear cushions.',
                'status' => 'active',
                'product_type' => 'Electronics',
                'vendor' => 'SoundWave',
                'image' => '/wireless_headphones.png',
                'variants' => [
                    [
                        'option1' => 'Black', 
                        'price' => 249.00, 
                        'compare_at_price' => 299.00,
                        'sku' => 'WH-BLK-001-BLK', 
                        'inventory' => 25
                    ],
                    [
                        'option1' => 'Silver', 
                        'price' => 249.00, 
                        'compare_at_price' => 299.00,
                        'sku' => 'WH-BLK-001-SLV', 
                        'inventory' => 15
                    ]
                ]
            ],
            [
                'title' => 'Minimalist Leather Watch',
                'description' => 'A timeless classic. This minimalist watch features a genuine black leather strap and a clean white face. Elegant, understated, and perfect for any occasion.',
                'status' => 'active',
                'product_type' => 'Jewelry',
                'vendor' => 'Timeless',
                'image' => '/minimalist_watch.png',
                'variants' => [
                    [
                        'option1' => 'One Size', 
                        'price' => 120.00, 
                        'compare_at_price' => null,
                        'sku' => 'WT-MIN-001-OS', 
                        'inventory' => 100
                    ]
                ]
            ],
             [
                'title' => 'Organic Cotton T-Shirt',
                'description' => 'Sustainably sourced and incredibly soft. Our olive green organic cotton t-shirt is a wardrobe staple that looks good and feels great.',
                'status' => 'active',
                'product_type' => 'Clothing',
                'vendor' => 'EcoStyle',
                'image' => '/organic_cotton_tshirt.png',
                'variants' => [
                    ['option1' => 'Small', 'price' => 35.00, 'compare_at_price' => 45.00, 'sku' => 'TS-ORG-001-S', 'inventory' => 20],
                    ['option1' => 'Medium', 'price' => 35.00, 'compare_at_price' => 45.00, 'sku' => 'TS-ORG-001-M', 'inventory' => 40],
                    ['option1' => 'Large', 'price' => 35.00, 'compare_at_price' => 45.00, 'sku' => 'TS-ORG-001-L', 'inventory' => 30],
                    ['option1' => 'X-Large', 'price' => 35.00, 'compare_at_price' => 45.00, 'sku' => 'TS-ORG-001-XL', 'inventory' => 10],
                ]
            ],
        ];

        foreach ($products as $data) {
            $product = Product::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'],
                'status' => $data['status'],
                // 'product_type' => $data['product_type'], // Check if column exists, usually managed via categories or tags. 
                // Based on schema, strict 'product_type' column wasn't in CreateProductsTable, so leaving it out or mapping to category if needed.
                // Migration had 'category_id', 'vendor'.
                'vendor' => $data['vendor'],
            ]);

            // Create Image
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $data['image'],
                'is_primary' => true,
                'sort_order' => 0
            ]);

            // Create Variants
            foreach ($data['variants'] as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,    
                    // Title removed as it's not in schema
                    'price' => $variantData['price'],
                    'compare_at_price' => $variantData['compare_at_price'] ?? null,
                    'sku' => $variantData['sku'],
                    'color' => $data['title'] == 'Organic Cotton T-Shirt' ? 'Olive' : null, 
                    'size' => $variantData['option1'] == 'Standard' ? null : $variantData['option1']
                ]);

                // Create Inventory
                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $variantData['inventory'],
                    'safety_stock' => 5
                ]);
            }
        }
    }
}
