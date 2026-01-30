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
                // Use product_variant_id consistent with StoreOrderRequest
                $variantId = $item['product_variant_id'] ?? $item['variant_id']; 
                $variant = ProductVariant::with('product')->find($variantId);
                
                if (!$variant) throw new Exception("Variant not found: " . $variantId);
                
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

            // 2. Address & Calculations
            $countryCode = $data['shipping_address']['country'] ?? 'US';


            // 3. Tax & Shipping (Simple Logic)
            $taxAmount = 0;
            $shippingAmount = 0;
            
            // Fetch Tax Rate based on Country
            $taxRate = \App\Models\TaxRate::where('country_code', $countryCode)->first();
            if ($taxRate) {
                $taxAmount = $subtotal * ($taxRate->rate / 100);
            }

            // Fetch Shipping Rate (Simple flat rate for now or lookup)
            // Ideally match ShippingZone -> ShippingRate
            // For now, assume Free Shipping if > 100, else 10
            $shippingAmount = $subtotal > 100 ? 0 : 10;

            // 4. Apply Discount
            $discountAmount = 0;
            if (!empty($data['discount_code'])) {
                $discountResult = $this->discountService->validateAndGet($data['discount_code'], $subtotal);
                if ($discountResult['valid']) {
                    $discountAmount = $discountResult['amount'];
                    $discountResult['discount']->increment('used_count');
                }
            }

            $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            // 5. Create Order
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'subtotal' => $subtotal,
                'discount_code' => $data['discount_code'] ?? null,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total' => max(0, $total),
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // 5b. Create Shipping Address
            if (isset($data['shipping_address'])) {
                \App\Models\ShippingAddress::create(array_merge(
                    $data['shipping_address'], 
                    ['order_id' => $order->id]
                ));
            }

            // 6. Create Items
            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            // 7. Create Payment Record (Pending)
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
