<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportService
{
    /**
     * Import products from CSV
     */
    public function importProducts(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        fgetcsv($handle); // Skip header

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Mapping based on simple Shopify structure
                // 0: Handle, 1: Title, 2: Body, 3: Vendor, 4: Type, ..., 6: Published
                // 8: Option1 Value, 11: SKU, 13: Qty, 14: Price, 16: Image Src

                $slug = $row[0];
                if (empty($slug)) continue;

                $product = Product::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $row[1],
                        'description' => $row[2],
                        'vendor' => $row[3],
                        'product_type' => $row[4] ?? null,
                        'status' => ($row[6] ?? 'false') === 'true' ? 'active' : 'draft',
                    ]
                );

                // Update basic info
                $product->update([
                    'description' => $row[2],
                    'vendor' => $row[3],
                    'status' => ($row[6] ?? 'false') === 'true' ? 'active' : 'draft',
                ]);

                // Handle Image
                if (!empty($row[16])) {
                    $exists = $product->images()->where('image_path', $row[16])->exists();
                    if (!$exists) {
                        $product->images()->create([
                            'image_path' => $row[16],
                            'is_primary' => $product->images()->count() === 0,
                            'sort_order' => $product->images()->count()
                        ]);
                    }
                }

                // Handle Variant
                $optionValue = $row[8];
                $sku = $row[11];
                
                if ($optionValue) {
                    $variant = $product->variants()->updateOrCreate(
                        ['sku' => $sku ?: $slug . '-' . $optionValue, 'title' => $optionValue],
                        [
                            'product_id' => $product->id,
                            'price' => floatval($row[14]),
                            'compare_at_price' => !empty($row[15]) ? floatval($row[15]) : null,
                            'title' => $optionValue,
                        ]
                    );

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
                
                $importedCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return ['count' => $importedCount, 'errors' => $errors];
    }

    /**
     * Export products to CSV
     */
    public function exportProducts(): StreamedResponse
    {
        $products = Product::with(['variants.inventory', 'images'])->get();
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
                $tags = $product->tags ?? '';
                $published = $product->status === 'active' ? 'true' : 'false';

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
                        'Title',
                        $variant->title ?? 'Default Title',
                        '', '',
                        $variant->sku,
                        $variant->weight ?? 0,
                        $variant->inventory ? $variant->inventory->quantity : 0,
                        $variant->price,
                        $variant->compare_at_price,
                        $index === 0 ? ($product->images->first()->image_path ?? '') : ''
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
}
