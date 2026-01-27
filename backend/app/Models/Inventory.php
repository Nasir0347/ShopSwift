<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['product_variant_id', 'quantity', 'safety_stock'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
