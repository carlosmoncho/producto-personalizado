<?php

/**
 * RUTAS DE DESARROLLO Y TESTING
 *
 * Este archivo solo se carga en entorno local.
 * NO se registra en producción por seguridad.
 *
 * Ver: bootstrap/app.php para configuración de carga
 */

use Illuminate\Support\Facades\Route;

// ============ DEMOS Y TESTING ============

// Demo de atributos con vista completa del admin
Route::get('/demo/attributes', function() {
    $attributes = \App\Models\ProductAttribute::with('attributeGroup')
        ->orderBy('type')
        ->orderBy('sort_order')
        ->paginate(50);

    // Obtener conteos totales
    $totalCounts = \App\Models\ProductAttribute::selectRaw('type, COUNT(*) as count')
        ->groupBy('type')
        ->pluck('count', 'type')
        ->toArray();

    $types = [
        \App\Models\ProductAttribute::TYPE_COLOR => 'Colores',
        \App\Models\ProductAttribute::TYPE_MATERIAL => 'Materiales',
        \App\Models\ProductAttribute::TYPE_SIZE => 'Tamaños',
        \App\Models\ProductAttribute::TYPE_INK => 'Tintas',
        \App\Models\ProductAttribute::TYPE_QUANTITY => 'Cantidades',
        \App\Models\ProductAttribute::TYPE_SYSTEM => 'Sistemas',
        'finish' => 'Acabados'
    ];

    return view('admin.product-attributes.index', compact('attributes', 'types', 'totalCounts'));
})->name('demo.attributes');

// Test crear atributo con problema
Route::get('/test/create-size', function() {
    try {
        $sizeGroup = \App\Models\AttributeGroup::where('type', 'size')->first();

        $data = [
            'attribute_group_id' => $sizeGroup->id,
            'type' => 'size',
            'name' => 'Tamaño 11,2x22,5 cm',
            'value' => '11,2x22,5 cm',
            'price_modifier' => 0,
            'sort_order' => 10,
            'active' => true
        ];

        // Simular validación del controlador
        $rules = [
            'attribute_group_id' => 'required|exists:attribute_groups,id',
            'type' => 'required|in:color,material,size,ink,quantity,system',
            'name' => 'required|string|max:255',
            'value' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('product_attributes', 'value')->where('type', 'size')
            ],
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

        if ($validator->fails()) {
            return 'ERRORES DE VALIDACIÓN:<br>' . implode('<br>', $validator->errors()->all());
        }

        $attribute = \App\Models\ProductAttribute::create($data);
        return 'ÉXITO: Atributo creado con ID ' . $attribute->id;

    } catch (Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});
