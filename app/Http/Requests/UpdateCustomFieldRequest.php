<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomFieldRequest extends FormRequest
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
        // Placeholder - Implementar cuando se use este FormRequest
        return [
            'name' => 'nullable|string|max:255',
            'value' => 'nullable|string',
            'type' => 'nullable|string|in:text,number,date,boolean',
        ];
    }
}
