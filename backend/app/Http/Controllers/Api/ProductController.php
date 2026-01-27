<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $products = Product::with(['category', 'variants', 'images'])->paginate(20);
        return $this->success(ProductResource::collection($products)->response()->getData(true));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,active,archived',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string', // Allow category name
            'vendor' => 'nullable|string',
            'tags' => 'nullable|string',
            'variants' => 'required|array|min:1',
            'variants.*.price' => 'required|numeric',
            'variants.*.sku' => 'nullable|string',
            'variants.*.barcode' => 'nullable|string',
            'variants.*.price' => 'required|numeric',
            'variants.*.compare_at_price' => 'nullable|numeric',
            'variants.*.cost_per_item' => 'nullable|numeric',
            'variants.*.size' => 'nullable|string',
            'variants.*.color' => 'nullable|string',
            'variants.*.option1' => 'nullable|string',
            'variants.*.option2' => 'nullable|string',
            'variants.*.inventory_quantity' => 'nullable|integer',
            'images' => 'nullable|array',
            'images.*.image_path' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $categoryId = $validated['category_id'] ?? null;
            if (isset($validated['category']) && !empty($validated['category'])) {
                // Find or create category by name
                $cat = \App\Models\Category::firstOrCreate(
                    ['name' => $validated['category']],
                    ['slug' => Str::slug($validated['category'])]
                );
                $categoryId = $cat->id;
            }

            $product = Product::create([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'category_id' => $categoryId,
                'vendor' => $validated['vendor'] ?? null,
                'tags' => $validated['tags'] ?? null,
            ]);

            foreach ($validated['variants'] as $variantData) {
                // Map frontend 'option1'/'option2' to backend 'size'/'color' 
                // This is a naive mapping for now, assuming Option1=Size, Option2=Color if not explicitly provided
                $size = $variantData['size'] ?? ($variantData['option1'] ?? null); // Fallback to option1
                $color = $variantData['color'] ?? ($variantData['option2'] ?? null); // Fallback to option2
                
                // If the option1 is technically 'Color', we might be swapping them. 
                // Ideally we should store generic options JSON, but for this schema:
                
                $variant = $product->variants()->create([
                    'price' => $variantData['price'],
                    'compare_at_price' => $variantData['compare_at_price'] ?? null,
                    'cost_per_item' => $variantData['cost_per_item'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'barcode' => $variantData['barcode'] ?? null,
                    'size' => $size,
                    'color' => $color
                ]);

                // Create Inventory
                if (isset($variantData['inventory_quantity'])) {
                    \App\Models\Inventory::create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $variantData['inventory_quantity'],
                        'safety_stock' => 5
                    ]);
                }
            }

            if (!empty($validated['images'])) {
                foreach ($validated['images'] as $index => $imageData) {
                    $product->images()->create([
                        'image_path' => $imageData['image_path'],
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return $this->success(new ProductResource($product->load('variants', 'images')), 'Product created', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create product: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'images'])->find($id);
        if (!$product) return $this->error('Product not found', 404);
        return $this->success(new ProductResource($product));
    }

    public function update(Request $request, $id)
    {
        // Simplified update: only product fields for now
        $product = Product::find($id);
        if (!$product) return $this->error('Product not found', 404);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,active,archived',
            'vendor' => 'nullable|string',
            'tags' => 'nullable|string',
            'category' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if (isset($validated['title'])) {
            $product->title = $validated['title'];
            $product->slug = Str::slug($validated['title']);
        }
        if (array_key_exists('description', $validated)) $product->description = $validated['description'];
        if (isset($validated['status'])) $product->status = $validated['status'];
        if (array_key_exists('vendor', $validated)) $product->vendor = $validated['vendor'];
        if (array_key_exists('tags', $validated)) $product->tags = $validated['tags'];

        // Handle Category Update
        if (isset($validated['category']) && !empty($validated['category'])) {
             $cat = \App\Models\Category::firstOrCreate(
                ['name' => $validated['category']],
                ['slug' => Str::slug($validated['category'])]
            );
            $product->category_id = $cat->id;
        } elseif (array_key_exists('category_id', $validated)) {
            $product->category_id = $validated['category_id'];
        }

        $product->save();

        return $this->success(new ProductResource($product), 'Product updated');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return $this->error('Product not found', 404);
        $product->delete();
        return $this->success([], 'Product deleted');
    }

    public function export()
    {
        $products = Product::with(['variants', 'images'])->get();
        $csvHeader = ['Handle', 'Title', 'Body (HTML)', 'Vendor', 'Type', 'Tags', 'Published', 'Option1 Name', 'Option1 Value', 'Option2 Name', 'Option2 Value', 'Variant SKU', 'Variant Grams', 'Variant Inventory Qty', 'Variant Price', 'Variant Compare At Price', 'Image Src'];
        
        $callback = function() use ($products, $csvHeader) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader);

            foreach ($products as $product) {
                $handle = $product->slug;
                $title = $product->title;
                $body = $product->description;
                $vendor = $product->vendor;
                $type = $product->product_type ?? '';
                $tags = ''; // Implement tags later
                $published = $product->status === 'active' ? 'true' : 'false';
                
                // If no variants, output product info with defaults
                if ($product->variants->isEmpty()) {
                     fputcsv($file, [
                        $handle, $title, $body, $vendor, $type, $tags, $published,
                        'Title', 'Default Title', '', '', '', 0, 0, 0, 0,
                        $product->images->first()->image_path ?? ''
                    ]);
                    continue;
                }

                foreach ($product->variants as $index => $variant) {
                    $row = [
                        $handle,
                        $title, 
                        $body,
                        $vendor, 
                        $type,
                        $tags,
                        $published,
                        'Title', // Simple option name
                        $variant->title ?? 'Default Title', // Option value
                        '', '', // Option 2
                        $variant->sku,
                        0, // Grams
                        $variant->inventory ? $variant->inventory->quantity : 0,
                        $variant->price,
                        $variant->compare_at_price,
                        $index === 0 ? ($product->images->first()->image_path ?? '') : '' // Only show image on first row of product
                    ];
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=products_export_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Skip header

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                 // Map row to vars based on Shopify simple structure (indices based on header above)
                 // ['Handle', 'Title', 'Body (HTML)', 'Vendor', 'Type', 'Tags', 'Published', 'Option1 Name', 'Option1 Value', 'Option2 Name', 'Option2 Value', 'Variant SKU', 'Variant Grams', 'Variant Inventory Qty', 'Variant Price', 'Variant Compare At Price', 'Image Src']
                 
                 $slug = $row[0];
                 if (empty($slug)) continue;

                 $product = Product::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $row[1],
                        'description' => $row[2],
                        'vendor' => $row[3],
                        // 'product_type' => $row[4], // Schema missing
                        'status' => $row[6] === 'true' ? 'active' : 'draft',
                    ]
                 );

                 // Update basic info if exists (optional, but good for sync)
                 $product->update([
                     'description' => $row[2],
                     'vendor' => $row[3],
                     'status' => $row[6] === 'true' ? 'active' : 'draft',
                 ]);
                 
                 // Handle Images (Simple: if URL provided on row 1)
                 if (!empty($row[16])) {
                     // Check if image already exists to avoid dupes
                     $exists = $product->images()->where('image_path', $row[16])->exists();
                     if (!$exists) {
                         $product->images()->create([
                             'image_path' => $row[16],
                             'is_primary' => $product->images()->count() === 0,
                             'sort_order' => $product->images()->count()
                         ]);
                     }
                 }

                 // Handle Variants
                 $sku = $row[11];
                 $optionValue = $row[8]; // Option1 Value
                 
                 if ($optionValue) {
                     $variant = $product->variants()->updateOrCreate(
                         ['sku' => $sku, 'title' => $optionValue], // Match by SKU and Option Value
                         [
                            'product_id' => $product->id, // Ensure link
                            'price' => floatval($row[14]),
                            'compare_at_price' => !empty($row[15]) ? floatval($row[15]) : null,
                            'title' => $optionValue,
                            // 'size' => ... logic to parse size/color from options if strict columns existed
                         ]
                     );
                     
                     // Inventory
                     if ($variant) {
                         $variant->inventory()->updateOrCreate(
                             ['product_variant_id' => $variant->id],
                             [
                                 'quantity' => intval($row[13]),
                                 'safety_stock' => 5
                             ]
                         );
                     }
                 }
            }
            DB::commit();
            fclose($handle);
            return $this->success([], 'Products imported successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return $this->error('Import failed: ' . $e->getMessage(), 500);
        }
    }
}
