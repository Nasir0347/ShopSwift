<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Inserting sample products...\n";

try {
    // Get or create categories
    $electronics = DB::table('categories')->where('slug', 'electronics')->value('id');
    if (!$electronics) {
        $electronics = DB::table('categories')->insertGetId([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices and accessories',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    $accessories = DB::table('categories')->where('slug', 'accessories')->value('id');
    if (!$accessories) {
        $accessories = DB::table('categories')->insertGetId([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'description' => 'Various accessories',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    $homeKitchen = DB::table('categories')->where('slug', 'home-kitchen')->value('id');
    if (!$homeKitchen) {
        $homeKitchen = DB::table('categories')->insertGetId([
            'name' => 'Home & Kitchen',
            'slug' => 'home-kitchen',
            'description' => 'Home and kitchen appliances',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    $sports = DB::table('categories')->where('slug', 'sports-fitness')->value('id');
    if (!$sports) {
        $sports = DB::table('categories')->insertGetId([
            'name' => 'Sports & Fitness',
            'slug' => 'sports-fitness',
            'description' => 'Sports and fitness equipment',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    echo "✓ Categories ready\n";
    
    // Products
    $product1 = DB::table('products')->insertGetId([
        'title' => 'Premium Wireless Headphones',
        'slug' => 'premium-wireless-headphones',
        'description' => 'Experience superior sound quality with our premium wireless headphones. Features active noise cancellation, 30-hour battery life, and premium leather cushions.',
        'category_id' => $electronics,
        'vendor' => 'AudioTech',
        'status' => 'active',
        'product_type' => 'Headphones',
        'tags' => 'wireless, audio, noise-cancelling, bluetooth',
        'seo_title' => 'Premium Wireless Headphones - Active Noise Cancellation',
        'seo_description' => 'Buy premium wireless headphones with 30hr battery.',
        'options' => json_encode([
            ['name' => 'Color', 'values' => ['Black']]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $product2 = DB::table('products')->insertGetId([
        'title' => 'Fitness Smartwatch Pro',
        'slug' => 'fitness-smartwatch-pro',
        'description' => 'Track your fitness goals with our advanced smartwatch. Features heart rate monitoring, GPS tracking, and 7-day battery life.',
        'category_id' => $electronics,
        'vendor' => 'FitGear',
        'status' => 'active',
        'product_type' => 'Smartwatch',
        'tags' => 'fitness, smartwatch, health, gps',
        'seo_title' => 'Fitness Smartwatch Pro - Heart Rate & GPS',
        'seo_description' => 'Advanced fitness smartwatch with heart rate monitor.',
        'options' => json_encode([
            ['name' => 'Color', 'values' => ['Silver']]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $product3 = DB::table('products')->insertGetId([
        'title' => 'Professional Laptop Backpack',
        'slug' => 'professional-laptop-backpack',
        'description' => 'Stylish and functional laptop backpack with padded compartment for 15.6" laptops. Water-resistant fabric.',
        'category_id' => $accessories,
        'vendor' => 'UrbanGear',
        'status' => 'active',
        'product_type' => 'Backpack',
        'tags' => 'backpack, laptop, travel, work',
        'seo_title' => 'Professional Laptop Backpack - Water Resistant',
        'seo_description' => 'Premium laptop backpack with USB charging port.',
        'options' => json_encode([
            ['name' => 'Color', 'values' => ['Gray']]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $product4 = DB::table('products')->insertGetId([
        'title' => 'Programmable Coffee Maker',
        'slug' => 'programmable-coffee-maker',
        'description' => 'Wake up to freshly brewed coffee. Features 24-hour timer, auto shut-off, and 12-cup glass carafe.',
        'category_id' => $homeKitchen,
        'vendor' => 'BrewMaster',
        'status' => 'active',
        'product_type' => 'Coffee Maker',
        'tags' => 'coffee, kitchen, appliance, programmable',
        'seo_title' => 'Programmable Coffee Maker - 12 Cup',
        'seo_description' => 'Automatic coffee maker with 24-hour timer.',
        'options' => json_encode([
            ['name' => 'Color', 'values' => ['Stainless Steel']]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $product5 = DB::table('products')->insertGetId([
        'title' => 'Eco-Friendly Yoga Mat',
        'slug' => 'eco-friendly-yoga-mat',
        'description' => 'Practice in comfort with our eco-friendly yoga mat. Made from sustainable materials with superior grip. 6mm thickness.',
        'category_id' => $sports,
        'vendor' => 'ZenFit',
        'status' => 'active',
        'product_type' => 'Yoga Mat',
        'tags' => 'yoga, fitness, eco-friendly, exercise',
        'seo_title' => 'Eco-Friendly Yoga Mat - Non-Slip',
        'seo_description' => 'Premium eco-friendly yoga mat.',
        'options' => json_encode([
            ['name' => 'Color', 'values' => ['Purple']]
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✓ 5 Products created\n";
    
    // Variants
    $variant1 = DB::table('product_variants')->insertGetId([
        'product_id' => $product1,
        'sku' => 'HP-BLK-001',
        'price' => 299.99,
        'compare_at_price' => 399.99,
        'cost_per_item' => 150.00,
        'title' => 'Black',
        'option1' => 'Black',
        'weight' => 0.25,
        'weight_unit' => 'kg',
        'color' => 'Black',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $variant2 = DB::table('product_variants')->insertGetId([
        'product_id' => $product2,
        'sku' => 'SW-SLV-001',
        'price' => 249.99,
        'compare_at_price' => 329.99,
        'cost_per_item' => 120.00,
        'title' => 'Silver',
        'option1' => 'Silver',
        'weight' => 0.05,
        'weight_unit' => 'kg',
        'color' => 'Silver',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $variant3 = DB::table('product_variants')->insertGetId([
        'product_id' => $product3,
        'sku' => 'BP-GRY-001',
        'price' => 79.99,
        'compare_at_price' => 129.99,
        'cost_per_item' => 35.00,
        'title' => 'Gray',
        'option1' => 'Gray',
        'weight' => 0.8,
        'weight_unit' => 'kg',
        'color' => 'Gray',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $variant4 = DB::table('product_variants')->insertGetId([
        'product_id' => $product4,
        'sku' => 'CM-SS-001',
        'price' => 89.99,
        'compare_at_price' => 139.99,
        'cost_per_item' => 45.00,
        'title' => 'Stainless Steel',
        'option1' => 'Stainless Steel',
        'weight' => 2.5,
        'weight_unit' => 'kg',
        'color' => 'Stainless Steel',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $variant5 = DB::table('product_variants')->insertGetId([
        'product_id' => $product5,
        'sku' => 'YM-PUR-001',
        'price' => 39.99,
        'compare_at_price' => 59.99,
        'cost_per_item' => 18.00,
        'title' => 'Purple',
        'option1' => 'Purple',
        'weight' => 1.2,
        'weight_unit' => 'kg',
        'color' => 'Purple',
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✓ Variants created\n";
    
    // Inventory
    DB::table('inventories')->insert([
        ['product_variant_id' => $variant1, 'quantity' => 50, 'created_at' => now(), 'updated_at' => now()],
        ['product_variant_id' => $variant2, 'quantity' => 75, 'created_at' => now(), 'updated_at' => now()],
        ['product_variant_id' => $variant3, 'quantity' => 120, 'created_at' => now(), 'updated_at' => now()],
        ['product_variant_id' => $variant4, 'quantity' => 35, 'created_at' => now(), 'updated_at' => now()],
        ['product_variant_id' => $variant5, 'quantity' => 85, 'created_at' => now(), 'updated_at' => now()],
    ]);
    
    echo "✓ Inventory added\n";
    
    // Images
    DB::table('product_images')->insert([
        ['product_id' => $product1, 'image_path' => '/storage/products/wireless_headphones_1769688403176.png', 'sort_order' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $product2, 'image_path' => '/storage/products/smartwatch_silver_1769688424868.png', 'sort_order' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $product3, 'image_path' => '/storage/products/laptop_backpack_1769688438719.png', 'sort_order' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $product4, 'image_path' => '/storage/products/coffee_maker_1769688455500.png', 'sort_order' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $product5, 'image_path' => '/storage/products/yoga_mat_1769688470821.png', 'sort_order' => 1, 'is_primary' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);
    
    echo "✓ Images added\n";
    
    echo "\n✅ SUCCESS! All sample data inserted!\n";
    echo "📦 Products: 5\n";
    echo "🔢 Variants: 5\n";
    echo "📊 Inventory: 5 locations\n";
    echo "🖼️  Images: 5\n\n";
    echo "Now refresh http://localhost:5173/admin/products\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
