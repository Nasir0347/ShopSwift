<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DebugOrderSeeder extends Seeder
{
    public function run()
    {
        $variantIds = ProductVariant::pluck('id')->toArray();
        echo "Found " . count($variantIds) . " variants.\n";

        if (empty($variantIds)) {
            echo "No variants found! Aborting order creation.\n";
            return;
        }

        $userId = 1; // Assuming admin is ID 1 (or customer ID 2)
        
        echo "Creating orders for User ID: $userId\n";

        DB::beginTransaction();
        try {
            for ($i = 0; $i < 20; $i++) {
                $order = Order::create([
                    'user_id' => $userId,
                    'total_amount' => 100.00,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'stripe',
                    'created_at' => now()->subDays(rand(0, 30)),
                    'shipping_address' => json_encode(['address' => '123 Test St']),
                ]);

                // Item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variantIds[0],
                    'quantity' => 1,
                    'price' => 50.00,
                    'total' => 50.00
                ]);
            }
            DB::commit();
            echo "Successfully created 20 orders.\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error creating orders: " . $e->getMessage() . "\n";
        }
    }
}
