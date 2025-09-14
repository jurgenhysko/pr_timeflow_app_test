<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer ID is required',
            'customer_id.exists' => 'Customer not found',
            'products.required' => 'Products are required',
            'products.array' => 'Products must be an array',
            'products.*.product_id.required' => 'Product ID is required',
            'products.*.product_id.exists' => 'Product not found',
            'products.*.quantity.required' => 'Quantity is required',
            'products.*.quantity.integer' => 'Quantity must be an integer',
            'products.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
