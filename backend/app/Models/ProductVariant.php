<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'product_id', 'title', 'option1', 'option2', 'option3', 'size', 'color', 'sku', 'price', 
        'compare_at_price', 'cost_per_item', 'barcode', 'weight', 
        'weight_unit', 'position', 'image_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_per_item' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
    
    /**
     * Get the variant's associated image from product_images
     */
    public function image()
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Accessor for inventory quantity
    public function getInventoryQuantityAttribute()
    {
        return $this->inventory ? $this->inventory->quantity : 0;
    }
    
    // Scopes
    public function scopeInStock($query)
    {
        return $query->whereHas('inventory', function($q) {
            $q->where('quantity', '>', 0);
        });
    }
    
    public function scopeAvailableExcluding($query, $quantityNeeded = 1)
    {
        return $query->whereHas('inventory', function($q) use ($quantityNeeded) {
            $q->where('quantity', '>=', $quantityNeeded);
        });
    }
}
