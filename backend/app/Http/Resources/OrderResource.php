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
            'subtotal'        => $this->subtotal,
            'discount_code'   => $this->discount_code,
            'discount_amount' => $this->discount_amount,
            'total_amount'    => $this->total_amount,
            'status'          => $this->status,
            'payment_status'  => $this->payment_status,
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'payments'        => $this->whenLoaded('payments'),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
