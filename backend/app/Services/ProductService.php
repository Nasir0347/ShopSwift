<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Inventory;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Create a new product with variants, images, and inventory
     */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Handle category
            $categoryId = $this->resolveCategoryId($data);
            
            // Create product
            $product = Product::create([
                'title' => $data['title'],
                'slug' => $this->generateUniqueSlug($data['title']),
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'category_id' => $categoryId,
                'vendor' => $data['vendor'] ?? null,
                'options' => $data['options'] ?? null,
                'tags' => $data['tags'] ?? null,
                'product_type' => $data['product_type'] ?? null,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
            ]);

            // Create images FIRST (so we can link them to variants)
            if (!empty($data['images'])) {
                foreach ($data['images'] as $index => $imageData) {
                    $product->images()->create([
                        'image_path' => $imageData['image_path'],
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Create variants AFTER images (so we can resolve image_path to image_id)
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $index => $variantData) {
                    // Resolve image_path to image_id if needed
                    if (empty($variantData['image_id']) && !empty($variantData['image_path'])) {
                        $imagePath = $variantData['image_path'];
                        
                        // Try exact match first, then try matching just the filename
                        $image = $product->images()->where('image_path', $imagePath)->first();
                        
                        if (!$image) {
                            // Try matching by filename only (in case of URL vs path mismatch)
                            $filename = basename(parse_url($imagePath, PHP_URL_PATH));
                            $image = $product->images()->where('image_path', 'LIKE', '%' . $filename)->first();
                        }
                        
                        if ($image) {
                            $variantData['image_id'] = $image->id;
                            \Log::info("Resolved variant image: {$imagePath} -> image_id: {$image->id}");
                        } else {
                            \Log::warning("Could not find image for path: {$imagePath}");
                            \Log::warning("Available images: " . json_encode($product->images()->pluck('image_path')));
                        }
                    }
                    $this->createVariant($product, $variantData, $index);
                }
            }

            return $product->load('variants.inventory', 'variants.image', 'images', 'category');
        });
    }

    /**
     * Update an existing product
     */
    public function updateProduct(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {
            // Update basic product info
            $product->update([
                'title' => $data['title'] ?? $product->title,
                'description' => $data['description'] ?? $product->description,
                'status' => $data['status'] ?? $product->status,
                'vendor' => $data['vendor'] ?? $product->vendor,
                'options' => $data['options'] ?? $product->options,
                'tags' => $data['tags'] ?? $product->tags,
                'product_type' => $data['product_type'] ?? $product->product_type,
                'category_id' => isset($data['category']) || isset($data['category_id']) 
                    ? $this->resolveCategoryId($data) 
                    : $product->category_id,
                'seo_title' => $data['seo_title'] ?? $product->seo_title,
                'seo_description' => $data['seo_description'] ?? $product->seo_description,
            ]);

            // Sync Images FIRST (so we can link them to variants)
            if (isset($data['images'])) {
                $incomingIds = array_filter(array_column($data['images'], 'id'));
                
                // Delete removed images individually to trigger model events (file deletion)
                $imagesToDelete = $product->images()->whereNotIn('id', $incomingIds)->get();
                foreach ($imagesToDelete as $img) {
                    $img->delete();
                }

                foreach ($data['images'] as $index => $imageData) {
                     if (isset($imageData['id'])) {
                         $product->images()->where('id', $imageData['id'])->update([
                             'sort_order' => $index,
                             'is_primary' => $index === 0
                         ]);
                     } else {
                         $product->images()->create([
                             'image_path' => $imageData['image_path'],
                             'is_primary' => $index === 0,
                             'sort_order' => $index
                         ]);
                     }
                }
            }
            
            // IMPORTANT: Refresh images relationship so new image IDs are available for variant matching
            $product->load('images');

            // Sync Variants AFTER images (so image_path can be resolved)
            if (isset($data['variants'])) {
                $incomingIds = array_filter(array_column($data['variants'], 'id'));
                $product->variants()->whereNotIn('id', $incomingIds)->delete();

                foreach ($data['variants'] as $index => $variantData) {
                    if (isset($variantData['id'])) {
                        // Update existing variant
                        $variant = $product->variants()->find($variantData['id']);
                        if ($variant) {
                            // Get image_id: prefer incoming non-null value, fallback to existing
                            $imageId = !empty($variantData['image_id']) ? $variantData['image_id'] : $variant->image_id;
                            
                            // Validate that the image_id actually exists in the current product images
                            // This prevents FK errors if the image was deleted in the sync step above
                            if ($imageId && !$product->images->contains('id', $imageId)) {
                                $imageId = null;
                            }
                            
                            \Log::info("Variant {$variant->id}: incoming image_id=" . ($variantData['image_id'] ?? 'null') . ", existing={$variant->image_id}, resolved_id={$imageId}, image_path=" . ($variantData['image_path'] ?? 'null'));
                            
                            // If still no image_id but we have image_path, try to resolve it
                            if (empty($imageId) && !empty($variantData['image_path'])) {
                                $imagePath = $variantData['image_path'];
                                
                                \Log::info("Searching for image_path: {$imagePath}");
                                \Log::info("Available images: " . json_encode($product->images->pluck('image_path', 'id')));
                                
                                $image = $product->images()->where('image_path', $imagePath)->first();
                                
                                if (!$image) {
                                    // Try matching by filename only
                                    $filename = basename(parse_url($imagePath, PHP_URL_PATH));
                                    \Log::info("Trying filename match: {$filename}");
                                    $image = $product->images()->where('image_path', 'LIKE', '%' . $filename)->first();
                                }
                                
                                if ($image) {
                                    $imageId = $image->id;
                                    \Log::info("MATCHED: image_id={$imageId}");
                                } else {
                                    \Log::warning("NO MATCH found for image_path: {$imagePath}");
                                }
                            }
                            
                            $variant->update([
                                'title' => $variantData['title'] ?? $variant->title,
                                'price' => $variantData['price'],
                                'compare_at_price' => $variantData['compare_at_price'] ?? null,
                                'sku' => $variantData['sku'] ?? $variant->sku,
                                'barcode' => $variantData['barcode'] ?? $variant->barcode,
                                'option1' => $variantData['option1'] ?? $variant->option1,
                                'option2' => $variantData['option2'] ?? $variant->option2,
                                'option3' => $variantData['option3'] ?? $variant->option3,
                                'size' => $variantData['size'] ?? ($variantData['option1'] ?? $variant->size),
                                'color' => $variantData['color'] ?? ($variantData['option2'] ?? $variant->color),
                                'position' => $index,
                                'image_id' => $imageId,
                            ]);
                            
                            // Update Inventory
                            if (isset($variantData['inventory_quantity'])) {
                                $variant->inventory()->updateOrCreate(
                                    ['product_variant_id' => $variant->id],
                                    ['quantity' => $variantData['inventory_quantity']]
                                );
                            }
                        }
                    } else {
                        // Create new - resolve image_path first
                        if (empty($variantData['image_id']) && !empty($variantData['image_path'])) {
                            $imagePath = $variantData['image_path'];
                            $image = $product->images()->where('image_path', $imagePath)->first();
                            
                            if (!$image) {
                                // Try matching by filename only
                                $filename = basename(parse_url($imagePath, PHP_URL_PATH));
                                $image = $product->images()->where('image_path', 'LIKE', '%' . $filename)->first();
                            }
                            
                            $variantData['image_id'] = $image?->id;
                        }
                        $this->createVariant($product, $variantData, $index);
                    }
                }
            }

            return $product->load('variants.inventory', 'variants.image', 'images', 'category');
        });
    }

    /**
     * Create a variant for a product
     */
    private function createVariant(Product $product, array $variantData, int $position = 0)
    {
        // Map frontend option1/option2 to backend size/color if needed
        $size = $variantData['size'] ?? ($variantData['option1'] ?? null);
        $color = $variantData['color'] ?? ($variantData['option2'] ?? null);
        $option1 = $variantData['option1'] ?? $size;
        $option2 = $variantData['option2'] ?? $color;
        $option3 = $variantData['option3'] ?? null;
        
        $title = $variantData['title'] ?? $this->generateVariantTitle($option1, $option2, $option3);

        $variant = $product->variants()->create([
            'title' => $title,
            'price' => $variantData['price'],
            'compare_at_price' => $variantData['compare_at_price'] ?? null,
            'cost_per_item' => $variantData['cost_per_item'] ?? null,
            'sku' => $variantData['sku'] ?? null,
            'barcode' => $variantData['barcode'] ?? null,
            'size' => $size,
            'color' => $color,
            'option1' => $option1,
            'option2' => $option2,
            'option3' => $option3,
            'weight' => $variantData['weight'] ?? null,
            'weight_unit' => $variantData['weight_unit'] ?? 'kg',
            'position' => $position,
            'image_id' => $variantData['image_id'] ?? null,
        ]);

        // Create inventory if quantity provided
        if (isset($variantData['inventory_quantity'])) {
            Inventory::create([
                'product_variant_id' => $variant->id,
                'quantity' => $variantData['inventory_quantity'],
                'safety_stock' => 5
            ]);
        }

        return $variant;
    }

    /**
     * Generate a unique slug for a product
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Resolve category ID from data
     */
    private function resolveCategoryId(array $data): ?int
    {
        if (!empty($data['category_id'])) {
            return $data['category_id'];
        }

        if (!empty($data['category'])) {
            $category = Category::firstOrCreate(
                ['name' => $data['category']],
                ['slug' => Str::slug($data['category'])]
            );
            return $category->id;
        }

        return null;
    }

    /**
     * Delete a product
     */
    public function deleteProduct(int $id)
    {
        $product = Product::findOrFail($id);
        
        return DB::transaction(function () use ($product) {
            // Delete images from storage if needed (not implemented here)
            return $product->delete();
        });
    }

    /**
     * Generate variant title from options
     */
    private function generateVariantTitle(?string $opt1, ?string $opt2 = null, ?string $opt3 = null): string
    {
        $parts = array_filter([$opt1, $opt2, $opt3], function($v) { return !is_null($v) && $v !== ''; });
        return !empty($parts) ? implode(' / ', $parts) : 'Default Title';
    }
}
