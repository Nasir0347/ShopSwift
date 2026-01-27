<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'handle'      => $this->handle,
            'description' => $this->description,
            'type'        => $this->type,
            'created_at'  => $this->created_at->toIso8601String(),
        ];
    }
}
