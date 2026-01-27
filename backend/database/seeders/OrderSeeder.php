<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $customer = User::where('email', 'customer@shopswift.com')->first();
        if (!$customer) return;

        $variants = ProductVariant::with('product')->get();

        if ($variants->isEmpty()) return;

        // Create 20 Sample Orders
        for ($i = 0; $i < 20; $i++) {
            $isPaid = rand(0, 1);
            $total = rand(50, 300) + 0.99;
            
            $order = Order::create([
                'user_id' => $customer->id,
                'total_amount' => $total, // approximate, will verify
                'status' => $isPaid ? 'completed' : 'pending',
                'payment_status' => $isPaid ? 'paid' : 'pending',
                'payment_method' => rand(0, 1) ? 'stripe' : 'cod',
                'created_at' => now()->subDays(rand(0, 30)),
                'shipping_address' => json_encode(['address' => rand(100, 999) . ' Main St', 'city' => 'New York', 'zip' => '10001']),
            ]);

            $itemsCount = rand(1, 3);
            $orderTotal = 0;

            for ($k = 0; $k < $itemsCount; $k++) {
                $variant = $variants->random();
                $qty = rand(1, 2);
                $price = $variant->price;
                $lineTotal = $price * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->title,
                    'variant_name' => $variant->option1 ?? 'Standard',
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $lineTotal
                ]);
                $orderTotal += $lineTotal;
            }
            
            //Update order total to be accurate
            $order->update(['total_amount' => $orderTotal]);
        }
    }
}
