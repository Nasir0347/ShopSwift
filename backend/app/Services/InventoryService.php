<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Adjust inventory for a variant.
     * 
     * @param int $variantId
     * @param int $quantity Positive to add, negative to deduct.
     * @param string $reason
     * @param int|null $userId
     * @return Inventory
     * @throws Exception
     */
    public function adjust(int $variantId, int $quantity, string $reason, ?int $userId = null)
    {
        return DB::transaction(function () use ($variantId, $quantity, $reason, $userId) {
            $inventory = Inventory::firstOrCreate(
                ['product_variant_id' => $variantId],
                ['quantity' => 0, 'safety_stock' => 0]
            );

            if ($quantity < 0 && ($inventory->quantity + $quantity < 0)) {
                // Check if we want to enforce strict stock or allow negative (backorders).
                // Requirement says "Out-of-stock protection", so we block.
                throw new Exception("Insufficient stock for variant ID {$variantId}.");
            }

            $inventory->increment('quantity', $quantity);

            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'adjustment'   => $quantity,
                'reason'       => $reason,
                'user_id'      => $userId,
            ]);

            return $inventory;
        });
    }
}
