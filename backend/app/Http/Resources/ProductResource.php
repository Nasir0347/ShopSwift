<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description,
            'status'      => $this->status,
            'vendor'      => $this->vendor,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'variants'    => $this->whenLoaded('variants'),
            'images'      => $this->whenLoaded('images'),
            'created_at'  => $this->created_at->toIso8601String(),
        ];
    }
}
