<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    protected $inventoryService;
    protected $discountService;

    public function __construct(InventoryService $inventoryService, DiscountService $discountService)
    {
        $this->inventoryService = $inventoryService;
        $this->discountService = $discountService;
    }

    public function createOrder(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $subtotal = 0;
            $itemsToCreate = [];

            // 1. Validate Items & Calculate Subtotal
            foreach ($data['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['variant_id']);
                if (!$variant) throw new Exception("Variant not found: " . $item['variant_id']);
                
                // Simplified name snapshot
                $name = $variant->product->title;
                $variantName = ($variant->size ? $variant->size . ' ' : '') . ($variant->color ?? '');
                
                $price = $variant->price;
                $lineTotal = $price * $item['quantity'];
                $subtotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_variant_id' => $variant->id,
                    'product_name' => $name,
                    'variant_name' => trim($variantName),
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'total' => $lineTotal,
                ];

                // Deduct Inventory
                $this->inventoryService->adjust($variant->id, -1 * $item['quantity'], 'Order Placement', $user ? $user->id : null);
            }

            // 2. Apply Discount
            $discountAmount = 0;
            if (!empty($data['discount_code'])) {
                $discountResult = $this->discountService->validateAndGet($data['discount_code'], $subtotal);
                if ($discountResult['valid']) {
                    $discountAmount = $discountResult['amount'];
                    // Increment usage
                    $discountResult['discount']->increment('used_count');
                }
            }

            $total = $subtotal - $discountAmount;

            // 3. Create Order
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'subtotal' => $subtotal,
                'discount_code' => $data['discount_code'] ?? null,
                'discount_amount' => $discountAmount,
                'total' => max(0, $total),
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // 4. Create Items
            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            // 5. Create Payment Record (Pending)
            if (!empty($data['payment_method'])) {
                $order->payments()->create([
                    'method' => $data['payment_method'],
                    'amount' => $order->total,
                    'status' => 'pending'
                ]);
            }

            return $order;
        });
    }
}
