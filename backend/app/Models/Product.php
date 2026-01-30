<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title', 'slug', 'description', 'status', 'category_id', 
        'vendor', 'options', 'tags', 'product_type', 'seo_title', 'seo_description'
    ];

    protected $casts = [
        'options' => 'array',
        'tags' => 'array',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product');
    }
    
    // Accessors
    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first() 
            ?? $this->images()->first();
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopePublished($query)
    {
        return $this->scopeActive($query);
    }
    
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    public function scopeWithInventory($query)
    {
        return $query->with(['variants.inventory']);
    }
}
