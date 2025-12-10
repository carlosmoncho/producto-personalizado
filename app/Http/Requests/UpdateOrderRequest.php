<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',

            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999999',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.configuration' => 'nullable|array',

            'status' => 'nullable|in:pending,processing,completed,cancelled,on_hold',
            'subtotal' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string|max:2000',
            'delivery_date' => 'nullable|date',
        ];
    }
}
