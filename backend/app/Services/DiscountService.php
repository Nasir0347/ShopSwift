<?php

namespace App\Services;

use App\Models\Discount;
use Carbon\Carbon;

class DiscountService
{
    public function validateAndGet(string $code, float $cartTotal)
    {
        $discount = Discount::where('code', $code)->first();

        if (!$discount) {
            return ['valid' => false, 'message' => 'Invalid coupon code'];
        }

        if ($discount->expires_at && Carbon::now()->gt($discount->expires_at)) {
            return ['valid' => false, 'message' => 'Coupon expired'];
        }

        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached'];
        }

        // Calculate discount amount
        $amount = 0;
        if ($discount->type === 'percentage') {
            $amount = ($cartTotal * $discount->value) / 100;
        } else {
            $amount = $discount->value;
        }

        return [
            'valid' => true,
            'amount' => min($amount, $cartTotal), // Cannot exceed total
            'discount' => $discount
        ];
    }
}
