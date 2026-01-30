<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingAddress;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@shopswift.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
            ]
        );

        // Create customer user
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]
        );

        // Create categories
        $electronics = Category::firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories']
        );

        $accessories = Category::firstOrCreate(
            ['slug' => 'accessories'],
            ['name' => 'Accessories', 'description' => 'Various accessories']
        );

        $homeKitchen = Category::firstOrCreate(
            ['slug' => 'home-kitchen'],
            ['name' => 'Home & Kitchen', 'description' => 'Home and kitchen appliances']
        );

        $sport = Category::firstOrCreate(
            ['slug' => 'sports-fitness'],
            ['name' => 'Sports & Fitness', 'description' => 'Sports and fitness equipment']
        );

        // Product 1: Wireless Headphones
        $headphones = Product::create([
            'title' => 'Premium Wireless Headphones',
            'slug' => 'premium-wireless-headphones',
            'description' => 'Experience superior sound quality with our premium wireless headphones. Features active noise cancellation, 30-hour battery life, and premium leather cushions for all-day comfort.',
            'category_id' => $electronics->id,
            'vendor' => 'AudioTech',
            'status' => 'active',
            'product_type' => 'Headphones',
            'tags' => 'wireless, audio, noise-cancelling, bluetooth',
            'seo_title' => 'Premium Wireless Headphones - Active Noise Cancellation',
            'seo_description' => 'Buy premium wireless headphones with 30hr battery and noise cancellation. Free shipping available.',
        ]);

        $headphonesVariant = ProductVariant::create([
            'product_id' => $headphones->id,
            'sku' => 'HP-BLK-001',
            'price' => 299.99,
            'compare_at_price' => 399.99,
            'cost_per_item' => 150.00,
            'weight' => 0.25,
            'weight_unit' => 'kg',
            'color' => 'Black',
        ]);

        Inventory::create([
            'product_variant_id' => $headphonesVariant->id,
            'quantity' => 50,
        ]);

        ProductImage::create([
            'product_id' => $headphones->id,
            'image_path' => '/storage/products/wireless_headphones_1769688403176.png',
            'sort_order' => 1,
        ]);

        // Product 2: Smartwatch
        $smartwatch = Product::create([
            'title' => 'Fitness Smartwatch Pro',
            'slug' => 'fitness-smartwatch-pro',
            'description' => 'Track your fitness goals with our advanced smartwatch. Features heart rate monitoring, GPS tracking, sleep analysis, and 7-day battery life. Water-resistant up to 50 meters.',
            'category_id' => $electronics->id,
            'vendor' => 'FitGear',
            'status' => 'active',
            'product_type' => 'Smartwatch',
            'tags' => 'fitness, smartwatch, health, gps',
            'seo_title' => 'Fitness Smartwatch Pro - Heart Rate & GPS Tracking',
            'seo_description' => 'Advanced fitness smartwatch with heart rate monitor and GPS. Track your health 24/7.',
        ]);

        $smartwatchVariant1 = ProductVariant::create([
            'product_id' => $smartwatch->id,
            'sku' => 'SW-SLV-001',
            'price' => 249.99,
            'compare_at_price' => 329.99,
            'cost_per_item' => 120.00,
            'weight' => 0.05,
            'weight_unit' => 'kg',
            'color' => 'Silver',
        ]);

        Inventory::create([
            'product_variant_id' => $smartwatchVariant1->id,
            'quantity' => 75,
        ]);

        ProductImage::create([
            'product_id' => $smartwatch->id,
            'image_path' => '/storage/products/smartwatch_silver_1769688424868.png',
            'sort_order' => 1,
        ]);

        // Product 3: Laptop Backpack
        $backpack = Product::create([
            'title' => 'Professional Laptop Backpack',
            'slug' => 'professional-laptop-backpack',
            'description' => 'Stylish and functional laptop backpack with padded compartment for 15.6" laptops. Features water-resistant fabric, USB charging port, and multiple organizational pockets. Perfect for work and travel.',
            'category_id' => $accessories->id,
            'vendor' => 'UrbanGear',
            'status' => 'active',
            'product_type' => 'Backpack',
            'tags' => 'backpack, laptop, travel, work',
            'seo_title' => 'Professional Laptop Backpack - Water Resistant & USB Charging',
            'seo_description' => 'Premium laptop backpack with USB charging port. Water-resistant and spacious design.',
        ]);

        $backpackVariant = ProductVariant::create([
            'product_id' => $backpack->id,
            'sku' => 'BP-GRY-001',
            'price' => 79.99,
            'compare_at_price' => 129.99,
            'cost_per_item' => 35.00,
            'weight' => 0.8,
            'weight_unit' => 'kg',
            'color' => 'Gray',
        ]);

        Inventory::create([
            'product_variant_id' => $backpackVariant->id,
            'quantity' => 120,
        ]);

        ProductImage::create([
            'product_id' => $backpack->id,
            'image_path' => '/storage/products/laptop_backpack_1769688438719.png',
            'sort_order' => 1,
        ]);

        // Product 4: Coffee Maker
        $coffeeMaker = Product::create([
            'title' => 'Programmable Coffee Maker',
            'slug' => 'programmable-coffee-maker',
            'description' => 'Wake up to freshly brewed coffee with our programmable coffee maker. Features 24-hour timer, auto shut-off, pause and serve function, and 12-cup glass carafe. Stainless steel design.',
            'category_id' => $homeKitchen->id,
            'vendor' => 'BrewMaster',
            'status' => 'active',
            'product_type' => 'Coffee Maker',
            'tags' => 'coffee, kitchen, appliance, programmable',
            'seo_title' => 'Programmable Coffee Maker - 12 Cup with Timer',
            'seo_description' => 'Automatic coffee maker with 24-hour timer. Brew perfect coffee every morning.',
        ]);

        $coffeeMakerVariant = ProductVariant::create([
            'product_id' => $coffeeMaker->id,
            'sku' => 'CM-SS-001',
            'price' => 89.99,
            'compare_at_price' => 139.99,
            'cost_per_item' => 45.00,
            'weight' => 2.5,
            'weight_unit' => 'kg',
            'color' => 'Stainless Steel',
        ]);

        Inventory::create([
            'product_variant_id' => $coffeeMakerVariant->id,
            'quantity' => 35,
        ]);

        ProductImage::create([
            'product_id' => $coffeeMaker->id,
            'image_path' => '/storage/products/coffee_maker_1769688455500.png',
            'sort_order' => 1,
        ]);

        // Product 5: Yoga Mat
        $yogaMat = Product::create([
            'title' => 'Eco-Friendly Yoga Mat',
            'slug' => 'eco-friendly-yoga-mat',
            'description' => 'Practice in comfort with our eco-friendly yoga mat. Made from sustainable materials with superior grip and cushioning. Non-slip texture, 6mm thickness, and includes carrying strap. Perfect for all yoga styles.',
            'category_id' => $sport->id,
            'vendor' => 'ZenFit',
            'status' => 'active',
            'product_type' => 'Yoga Mat',
            'tags' => 'yoga, fitness, eco-friendly, exercise',
            'seo_title' => 'Eco-Friendly Yoga Mat - Non-Slip & Sustainable',
            'seo_description' => 'Premium eco-friendly yoga mat with superior grip. 6mm thick for maximum comfort.',
        ]);

        $yogaMatVariant1 = ProductVariant::create([
            'product_id' => $yogaMat->id,
            'sku' => 'YM-PUR-001',
            'price' => 39.99,
            'compare_at_price' => 59.99,
            'cost_per_item' => 18.00,
            'weight' => 1.2,
            'weight_unit' => 'kg',
            'color' => 'Purple',
        ]);

        $yogaMatVariant2 = ProductVariant::create([
            'product_id' => $yogaMat->id,
            'sku' => 'YM-BLU-001',
            'price' => 39.99,
            'compare_at_price' => 59.99,
            'cost_per_item' => 18.00,
            'weight' => 1.2,
            'weight_unit' => 'kg',
            'color' => 'Blue',
        ]);

        Inventory::create([
            'product_variant_id' => $yogaMatVariant1->id,
            'quantity' => 85,
        ]);

        Inventory::create([
            'product_variant_id' => $yogaMatVariant2->id,
            'quantity' => 92,
        ]);

        ProductImage::create([
            'product_id' => $yogaMat->id,
            'image_path' => '/storage/products/yoga_mat_1769688470821.png',
            'sort_order' => 1,
        ]);

        // Create Orders
        // Order 1: Completed order
        $order1 = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'completed',
            'fulfillment_status' => 'fulfilled',
            'subtotal' => 699.97,
            'tax_amount' => 69.99,
            'shipping_amount' => 15.00,
            'total' => 784.96,
            'payment_status' => 'paid',
            'notes' => 'Customer requested gift wrapping',
        ]);

        $shipping1 = ShippingAddress::create([
            'order_id' => $order1->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => '123 Main Street',
            'address2' => 'Apt 4B',
            'city' => 'New York',
            'province' => 'NY',
            'zip' => '10001',
            'country' => 'United States',
            'phone' => '+1 (555) 123-4567',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_variant_id' => $headphonesVariant->id,
            'product_name' => 'Premium Wireless Headphones',
            'variant_name' => 'Black',
            'quantity' => 1,
            'price' => 299.99,
            'total' => 299.99,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_variant_id' => $smartwatchVariant1->id,
            'product_name' => 'Fitness Smartwatch Pro',
            'variant_name' => 'Silver',
            'quantity' => 1,
            'price' => 249.99,
            'total' => 249.99,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_variant_id' => $coffeeMakerVariant->id,
            'product_name' => 'Programmable Coffee Maker',
            'variant_name' => 'Stainless Steel',
            'quantity' => 1,
            'price' => 89.99,
            'total' => 89.99,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_variant_id' => $yogaMatVariant1->id,
            'product_name' => 'Eco-Friendly Yoga Mat',
            'variant_name' => 'Purple',
            'quantity' => 2,
            'price' => 39.99,
            'total' => 79.98,
        ]);

        // Order 2: Pending order
        $order2 = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'pending',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'subtotal' => 79.99,
            'tax_amount' => 8.00,
            'shipping_amount' => 10.00,
            'total' => 97.99,
        ]);

        $shipping2 = ShippingAddress::create([
            'order_id' => $order2->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => '123 Main Street',
            'city' => 'New York',
            'province' => 'NY',
            'zip' => '10001',
            'country' => 'United States',
            'phone' => '+1 (555) 123-4567',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_variant_id' => $backpackVariant->id,
            'product_name' => 'Professional Laptop Backpack',
            'variant_name' => 'Gray',
            'quantity' => 1,
            'price' => 79.99,
            'total' => 79.99,
        ]);

        // Order 3: Paid but Unfulfilled
        $order3 = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'pending', // Processing
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'subtotal' => 379.98,
            'tax_amount' => 38.00,
            'shipping_amount' => 0.00,
            'total' => 417.98,
            'notes' => 'Leave at front door',
        ]);

        ShippingAddress::create([
            'order_id' => $order3->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => '123 Main Street',
            'city' => 'New York',
            'province' => 'NY',
            'zip' => '10001',
            'country' => 'United States',
            'phone' => '+1 (555) 123-4567',
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'product_variant_id' => $headphonesVariant->id,
            'product_name' => 'Premium Wireless Headphones',
            'variant_name' => 'Black',
            'quantity' => 1,
            'price' => 299.99,
            'total' => 299.99,
        ]);

         OrderItem::create([
            'order_id' => $order3->id,
            'product_variant_id' => $backpackVariant->id,
            'product_name' => 'Professional Laptop Backpack',
            'variant_name' => 'Gray',
            'quantity' => 1,
            'price' => 79.99,
            'total' => 79.99,
        ]);

        // Order 4: Cancelled & Refunded
        $order4 = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'cancelled',
            'payment_status' => 'refunded',
            'fulfillment_status' => 'unfulfilled',
            'subtotal' => 249.99,
            'tax_amount' => 25.00,
            'shipping_amount' => 12.00,
            'total' => 286.99,
            'notes' => 'Customer changed mind',
        ]);

        ShippingAddress::create([
            'order_id' => $order4->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => '123 Main Street',
            'city' => 'New York',
            'province' => 'NY',
            'zip' => '10001',
            'country' => 'United States',
            'phone' => '+1 (555) 123-4567',
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'product_variant_id' => $smartwatchVariant1->id,
            'product_name' => 'Fitness Smartwatch Pro',
            'variant_name' => 'Silver',
            'quantity' => 1,
            'price' => 249.99,
            'total' => 249.99,
        ]);

        // Order 5: High Value / Wholesale
        $order5 = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'completed',
            'payment_status' => 'paid',
            'fulfillment_status' => 'fulfilled',
            'subtotal' => 449.95, // 5 coffee makers
            'tax_amount' => 45.00,
            'shipping_amount' => 50.00,
            'total' => 544.95,
        ]);

        ShippingAddress::create([
            'order_id' => $order5->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address1' => '123 Main Street',
            'city' => 'New York',
            'province' => 'NY',
            'zip' => '10001',
            'country' => 'United States',
            'phone' => '+1 (555) 123-4567',
        ]);

        OrderItem::create([
            'order_id' => $order5->id,
            'product_variant_id' => $coffeeMakerVariant->id,
            'product_name' => 'Programmable Coffee Maker',
            'variant_name' => 'Stainless Steel',
            'quantity' => 5,
            'price' => 89.99,
            'total' => 449.95,
        ]);

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('📦 Created 5 products with images');
        $this->command->info('🛒 Created 5 sample orders (Completed, Pending, Paid, Refunded)');
        $this->command->info('👤 Admin: admin@shopswift.com / password');
        $this->command->info('👤 Customer: customer@example.com / password');
    }
}
