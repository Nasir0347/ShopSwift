-- Insert sample data for ShopSwift
-- Run this with: mysql -u root shopswift < insert_sample_data.sql

-- Insert Categories
INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES
('Electronics', 'electronics', 'Electronic devices and accessories', NOW(), NOW()),
('Accessories', 'accessories', 'Various accessories', NOW(), NOW()),
('Home & Kitchen', 'home-kitchen', 'Home and kitchen appliances', NOW(), NOW()),
('Sports & Fitness', 'sports-fitness', 'Sports and fitness equipment', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=name;

-- Get category IDs (will use in products)
SET @electronics_id = (SELECT id FROM categories WHERE slug = 'electronics' LIMIT 1);
SET @accessories_id = (SELECT id FROM categories WHERE slug = 'accessories' LIMIT 1);
SET @home_kitchen_id = (SELECT id FROM categories WHERE slug = 'home-kitchen' LIMIT 1);
SET @sports_id = (SELECT id FROM categories WHERE slug = 'sports-fitness' LIMIT 1);

-- Insert Products
INSERT INTO products (title, slug, description, category_id, vendor, status, product_type, tags, seo_title, seo_description, created_at, updated_at) VALUES
('Premium Wireless Headphones', 'premium-wireless-headphones', 'Experience superior sound quality with our premium wireless headphones. Features active noise cancellation, 30-hour battery life, and premium leather cushions for all-day comfort.', @electronics_id, 'AudioTech', 'active', 'Headphones', 'wireless, audio, noise-cancelling, bluetooth', 'Premium Wireless Headphones - Active Noise Cancellation', 'Buy premium wireless headphones with 30hr battery and noise cancellation.', NOW(), NOW()),
('Fitness Smartwatch Pro', 'fitness-smartwatch-pro', 'Track your fitness goals with our advanced smartwatch. Features heart rate monitoring, GPS tracking, sleep analysis, and 7-day battery life.', @electronics_id, 'FitGear', 'active', 'Smartwatch', 'fitness, smartwatch, health, gps', 'Fitness Smartwatch Pro - Heart Rate & GPS Tracking', 'Advanced fitness smartwatch with heart rate monitor and GPS.', NOW(), NOW()),
('Professional Laptop Backpack', 'professional-laptop-backpack', 'Stylish and functional laptop backpack with padded compartment for 15.6" laptops. Features water-resistant fabric and USB charging port.', @accessories_id, 'UrbanGear', 'active', 'Backpack', 'backpack, laptop, travel, work', 'Professional Laptop Backpack - Water Resistant & USB Charging', 'Premium laptop backpack with USB charging port.', NOW(), NOW()),
('Programmable Coffee Maker', 'programmable-coffee-maker', 'Wake up to freshly brewed coffee with our programmable coffee maker. Features 24-hour timer, auto shut-off, and 12-cup glass carafe.', @home_kitchen_id, 'BrewMaster', 'active', 'Coffee Maker', 'coffee, kitchen, appliance, programmable', 'Programmable Coffee Maker - 12 Cup with Timer', 'Automatic coffee maker with 24-hour timer.', NOW(), NOW()),
('Eco-Friendly Yoga Mat', 'eco-friendly-yoga-mat', 'Practice in comfort with our eco-friendly yoga mat. Made from sustainable materials with superior grip and cushioning. 6mm thickness.', @sports_id, 'ZenFit', 'active', 'Yoga Mat', 'yoga, fitness, eco-friendly, exercise', 'Eco-Friendly Yoga Mat - Non-Slip & Sustainable', 'Premium eco-friendly yoga mat with superior grip.', NOW(), NOW());

-- Get product IDs
SET @product1_id = (SELECT id FROM products WHERE slug = 'premium-wireless-headphones' LIMIT 1);
SET @product2_id = (SELECT id FROM products WHERE slug = 'fitness-smartwatch-pro' LIMIT 1);
SET @product3_id = (SELECT id FROM products WHERE slug = 'professional-laptop-backpack' LIMIT 1);
SET @product4_id = (SELECT id FROM products WHERE slug = 'programmable-coffee-maker' LIMIT 1);
SET @product5_id = (SELECT id FROM products WHERE slug = 'eco-friendly-yoga-mat' LIMIT 1);

-- Insert Product Variants
INSERT INTO product_variants (product_id, sku, price, compare_at_price, cost_per_item, weight, weight_unit, color, position, created_at, updated_at) VALUES
(@product1_id, 'HP-BLK-001', 299.99, 399.99, 150.00, 0.25, 'kg', 'Black', 1, NOW(), NOW()),
(@product2_id, 'SW-SLV-001', 249.99, 329.99, 120.00, 0.05, 'kg', 'Silver', 1, NOW(), NOW()),
(@product3_id, 'BP-GRY-001', 79.99, 129.99, 35.00, 0.8, 'kg', 'Gray', 1, NOW(), NOW()),
(@product4_id, 'CM-SS-001', 89.99, 139.99, 45.00, 2.5, 'kg', 'Stainless Steel', 1, NOW(), NOW()),
(@product5_id, 'YM-PUR-001', 39.99, 59.99, 18.00, 1.2, 'kg', 'Purple', 1, NOW(), NOW()),
(@product5_id, 'YM-BLU-001', 39.99, 59.99, 18.00, 1.2, 'kg', 'Blue', 2, NOW(), NOW());

-- Get variant IDs
SET @variant1_id = (SELECT id FROM product_variants WHERE sku = 'HP-BLK-001' LIMIT 1);
SET @variant2_id = (SELECT id FROM product_variants WHERE sku = 'SW-SLV-001' LIMIT 1);
SET @variant3_id = (SELECT id FROM product_variants WHERE sku = 'BP-GRY-001' LIMIT 1);
SET @variant4_id = (SELECT id FROM product_variants WHERE sku = 'CM-SS-001' LIMIT 1);
SET @variant5_id = (SELECT id FROM product_variants WHERE sku = 'YM-PUR-001' LIMIT 1);
SET @variant6_id = (SELECT id FROM product_variants WHERE sku = 'YM-BLU-001' LIMIT 1);

-- Insert Inventory
INSERT INTO inventories (product_variant_id, quantity, location, created_at, updated_at) VALUES
(@variant1_id, 50, 'Warehouse A', NOW(), NOW()),
(@variant2_id, 75, 'Warehouse A', NOW(), NOW()),
(@variant3_id, 120, 'Warehouse B', NOW(), NOW()),
(@variant4_id, 35, 'Warehouse C', NOW(), NOW()),
(@variant5_id, 85, 'Warehouse B', NOW(), NOW()),
(@variant6_id, 92, 'Warehouse B', NOW(), NOW());

-- Insert Product Images
INSERT INTO product_images (product_id, image_path, position, created_at, updated_at) VALUES
(@product1_id, '/storage/products/wireless_headphones_1769688403176.png', 1, NOW(), NOW()),
(@product2_id, '/storage/products/smartwatch_silver_1769688424868.png', 1, NOW(), NOW()),
(@product3_id, '/storage/products/laptop_backpack_1769688438719.png', 1, NOW(), NOW()),
(@product4_id, '/storage/products/coffee_maker_1769688455500.png', 1, NOW(), NOW()),
(@product5_id, '/storage/products/yoga_mat_1769688470821.png', 1, NOW(), NOW());

SELECT 'Sample data inserted successfully!' as status;
SELECT COUNT(*) as product_count FROM products;
