<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubcategoryRequest extends FormRequest
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
        $subcategoryId = $this->route('subcategory')->id ?? $this->route('subcategory');

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories', 'slug')->ignore($subcategoryId)
            ],
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la subcategoría es obligatorio.',
            'slug.required' => 'El slug es obligatorio.',
            'slug.unique' => 'Este slug ya está en uso.',
            'category_id.required' => 'Debe seleccionar una categoría.',
        ];
    }
}
