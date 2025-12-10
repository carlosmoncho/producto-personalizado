<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Usuario debe estar autenticado y verificado
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku|max:100',
            'colors' => 'nullable|array',
            'colors.*' => 'nullable|string|exists:available_colors,name',
            'materials' => 'nullable|array',
            'materials.*' => 'nullable|string|exists:available_materials,name',
            'sizes' => 'nullable|array',
            'sizes.*' => 'nullable|string',
            'printing_systems' => 'nullable|array',
            'printing_systems.*' => 'nullable|exists:printing_systems,id',
            'face_count' => 'nullable|integer|min:1|max:100',
            'print_colors_count' => 'nullable|integer|min:1|max:20',
            'print_colors' => 'nullable|array',
            'print_colors.*' => 'nullable|string|exists:available_print_colors,name',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'model_3d' => ['nullable', 'file', new \App\Rules\ValidGltfFile()],
            'pricing' => 'nullable|array',
            'pricing.*.quantity_from' => 'nullable|integer|min:1',
            'pricing.*.quantity_to' => 'nullable|integer|min:1',
            'pricing.*.price' => 'nullable|numeric|min:0',
            'pricing.*.unit_price' => 'nullable|numeric|min:0',
            'active' => 'boolean',

            // Validaciones del configurador
            'has_configurator' => 'boolean',
            'selected_attributes' => 'nullable|array',
            'selected_attributes.*' => 'nullable|array',
            'selected_attributes.*.*' => 'exists:product_attributes,id',
            'max_print_colors' => 'nullable|integer|min:1|max:10',
            'allow_file_upload' => 'boolean',
            'file_upload_types' => 'nullable|array',
            'file_upload_types.*' => 'string|in:pdf,ai,eps,svg,png,jpg',
            'configurator_base_price' => 'nullable|numeric|min:0|max:999999.99',
            'configurator_description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Este SKU ya está en uso.',
            'category_id.required' => 'Debe seleccionar una categoría.',
            'subcategory_id.required' => 'Debe seleccionar una subcategoría.',
            'model_3d.max' => 'El archivo 3D no debe superar los 20MB.',
            'model_3d.mimetypes' => 'El archivo 3D debe ser formato GLB o GLTF.',
        ];
    }
}
