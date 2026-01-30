<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'customer'        => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email, // Helpful for search
                ];
            }),
            'user_id'         => $this->user_id,
            'order_number'    => $this->order_number,
            'subtotal'        => $this->subtotal,
            'tax_amount'      => $this->tax_amount,
            'shipping_amount' => $this->shipping_amount,
            'discount_code'   => $this->discount_code,
            'discount_amount' => $this->discount_amount,
            'total'           => $this->total,
            'status'          => $this->status,
            'payment_status'  => $this->payment_status,
            'fulfillment_status' => $this->fulfillment_status,
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'payments'        => $this->whenLoaded('payments'),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
