<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'quantity'     => $this->quantity,
            'price'        => $this->price,
            'total'        => $this->total,
            'image'        => $this->variant?->product?->primary_image?->image_path,
            'product_id'   => $this->variant?->product_id,
            'variant_id'   => $this->product_variant_id,
        ];
    }
}
