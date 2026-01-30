<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product');
        
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,active,archived',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'product_type' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            
            // Variants validation (for updates)
            'variants' => 'sometimes|array|min:1',
            'variants.*.id' => 'nullable|integer',
            'variants.*.title' => 'nullable|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.barcode' => 'nullable|string|max:100',
            'variants.*.compare_at_price' => 'nullable|numeric|min:0',
            'variants.*.cost_per_item' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.weight_unit' => 'nullable|string|in:kg,g,lb,oz',
            'variants.*.option1' => 'nullable|string|max:50',
            'variants.*.option2' => 'nullable|string|max:50',
            'variants.*.option3' => 'nullable|string|max:50',
            'variants.*.inventory_quantity' => 'nullable|integer|min:0',
            'variants.*.image_id' => 'nullable|integer',
            'variants.*.image_path' => 'nullable|string',
            
            // Options definition (Shopify-style)
            'options' => 'nullable|array',
            'options.*.name' => 'nullable|string|max:50',
            'options.*.values' => 'nullable|array',
            
            // Images validation
            'images' => 'nullable|array',
            'images.*.id' => 'sometimes|integer',
            'images.*.image_path' => 'required|string',
        ];
    }
}
