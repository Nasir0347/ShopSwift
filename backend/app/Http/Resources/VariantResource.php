<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'price'            => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'cost_per_item'    => $this->cost_per_item,
            'sku'              => $this->sku,
            'barcode'          => $this->barcode,
            'option1'          => $this->option1,
            'option2'          => $this->option2,
            'option3'          => $this->option3,
            'size'             => $this->size,
            'color'            => $this->color,
            'weight'           => $this->weight,
            'weight_unit'      => $this->weight_unit,
            'inventory'        => [
                'quantity' => $this->inventory?->quantity ?? 0,
            ],
            'image'            => $this->image ? [
                'id' => $this->image->id,
                'image_path' => $this->image->image_path,
            ] : null,
            'image_id'         => $this->image_id,
        ];
    }
}
