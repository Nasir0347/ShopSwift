<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            
            'status' => 'sometimes|in:pending,processing,completed,cancelled',
            'notes' => 'nullable|string',
            
            // Shipping address
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string|max:100',
            'shipping_address.last_name' => 'required|string|max:100',
            'shipping_address.address1' => 'required|string|max:255',
            'shipping_address.address2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.province' => 'nullable|string|max:100',
            'shipping_address.zip' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:100',
            'shipping_address.phone' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Order must contain at least one item',
            'items.*.product_variant_id.exists' => 'One or more products are invalid',
            'shipping_address.first_name.required' => 'First name is required for shipping',
            'shipping_address.address1.required' => 'Address is required for shipping',
        ];
    }
}
