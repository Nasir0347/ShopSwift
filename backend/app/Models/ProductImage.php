<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'is_primary', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::deleting(function ($image) {
            if ($image->image_path) {
                // Convert URL to relative path
                // URL: http://localhost:8000/storage/uploads/filename.jpg
                // Path: uploads/filename.jpg
                $path = parse_url($image->image_path, PHP_URL_PATH);
                $path = str_replace('/storage/', '', $path); // Remove /storage prefix to get relative path for public disk
                
                if (\Storage::disk('public')->exists($path)) {
                    \Storage::disk('public')->delete($path);
                }
            }
        });
    }
}
