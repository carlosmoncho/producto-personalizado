<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductAttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductAttribute::with('attributeGroup');

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('value', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active === 'active');
        }

        $attributes = $query->orderBy('type')->orderBy('sort_order')->paginate(20);

        // Si es una petición AJAX, devolver JSON para refrescar
        if ($request->has('ajax')) {
            $attributesData = $query->orderBy('sort_order')->get();
            return response()->json([
                'success' => true,
                'attributes' => $attributesData->toArray()
            ]);
        }

        // Obtener conteos totales de todos los tipos (sin filtros para las estadísticas)
        $allAttributesQuery = ProductAttribute::query();
        $totalCounts = $allAttributesQuery->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $types = [
            ProductAttribute::TYPE_COLOR => 'Colores',
            ProductAttribute::TYPE_MATERIAL => 'Materiales',
            ProductAttribute::TYPE_SIZE => 'Tamaños',
            ProductAttribute::TYPE_INK => 'Tintas',
            ProductAttribute::TYPE_INK_COLOR => 'Colores Tintas',
            ProductAttribute::TYPE_CLICHE => 'Cliché',
            ProductAttribute::TYPE_QUANTITY => 'Cantidades',
            ProductAttribute::TYPE_SYSTEM => 'Sistemas',
            'finish' => 'Acabados'
        ];

        return view('admin.product-attributes.index', compact('attributes', 'types', 'totalCounts'));
    }

    public function create()
    {
        $types = [
            ProductAttribute::TYPE_COLOR => 'Color',
            ProductAttribute::TYPE_MATERIAL => 'Material',
            ProductAttribute::TYPE_SIZE => 'Tamaño',
            ProductAttribute::TYPE_INK => 'Tinta de Impresión',
            ProductAttribute::TYPE_INK_COLOR => 'Color de Tinta',
            ProductAttribute::TYPE_CLICHE => 'Cliché',
            ProductAttribute::TYPE_QUANTITY => 'Cantidad',
            ProductAttribute::TYPE_SYSTEM => 'Sistema de Impresión'
        ];

        // Obtener grupos de atributos activos
        $attributeGroups = \App\Models\AttributeGroup::where('active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.product-attributes.create', compact('types', 'attributeGroups'));
    }

    public function store(Request $request)
    {
        $rules = [
            'attribute_group_id' => 'required|exists:attribute_groups,id',
            'type' => 'required|in:' . implode(',', [
                ProductAttribute::TYPE_COLOR,
                ProductAttribute::TYPE_MATERIAL,
                ProductAttribute::TYPE_SIZE,
                ProductAttribute::TYPE_INK,
                ProductAttribute::TYPE_INK_COLOR,
                ProductAttribute::TYPE_CLICHE,
                ProductAttribute::TYPE_QUANTITY,
                ProductAttribute::TYPE_SYSTEM
            ]),
            'name' => 'required|string|max:255',
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attributes', 'value')->where('type', $request->type)
            ],
            'hex_code' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'price_percentage' => 'nullable|numeric|min:-100|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean',
            'is_recommended' => 'boolean'
        ];

        // Validaciones específicas por tipo
        if ($request->type === ProductAttribute::TYPE_COLOR || $request->type === ProductAttribute::TYPE_INK || $request->type === ProductAttribute::TYPE_INK_COLOR) {
            $rules['hex_code'] = ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'];
        }

        try {
            $validated = $request->validate($rules);

            $attributeService = new \App\Services\Attribute\AttributeService();

            // Procesar metadata usando AttributeService
            $validated['metadata'] = $attributeService->prepareAttributeMetadata($request, $request->type);
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['price_modifier'] = $validated['price_modifier'] ?? 0;
            $validated['price_percentage'] = $validated['price_percentage'] ?? 0;
            $validated['active'] = $request->boolean('active', true);
            $validated['is_recommended'] = $request->boolean('is_recommended', false);

            $attribute = ProductAttribute::create($validated);

            // Si es una petición AJAX, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Atributo creado exitosamente.',
                    'attribute' => $attribute
                ]);
            }

            return redirect()->route('admin.product-attributes.index')
                ->with('success', 'Atributo creado exitosamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor corrige los errores en el formulario.',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors($e->validator->errors())
                ->with('error', 'Por favor corrige los errores en el formulario.');
                
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el atributo: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el atributo: ' . $e->getMessage());
        }
    }

    public function show(ProductAttribute $productAttribute)
    {
        return view('admin.product-attributes.show', compact('productAttribute'));
    }

    public function edit(ProductAttribute $productAttribute)
    {
        $types = [
            ProductAttribute::TYPE_COLOR => 'Color',
            ProductAttribute::TYPE_MATERIAL => 'Material',
            ProductAttribute::TYPE_SIZE => 'Tamaño',
            ProductAttribute::TYPE_INK => 'Tinta de Impresión',
            ProductAttribute::TYPE_INK_COLOR => 'Color de Tinta',
            ProductAttribute::TYPE_CLICHE => 'Cliché',
            ProductAttribute::TYPE_QUANTITY => 'Cantidad',
            ProductAttribute::TYPE_SYSTEM => 'Sistema de Impresión'
        ];

        return view('admin.product-attributes.edit', compact('productAttribute', 'types'));
    }

    public function update(Request $request, ProductAttribute $productAttribute)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attributes', 'value')
                    ->where('type', $productAttribute->type)
                    ->ignore($productAttribute->id)
            ],
            'hex_code' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'price_percentage' => 'nullable|numeric|min:-100|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean',
            'is_recommended' => 'boolean'
        ];

        // Validaciones específicas por tipo
        if (in_array($productAttribute->type, [ProductAttribute::TYPE_COLOR, ProductAttribute::TYPE_INK, ProductAttribute::TYPE_INK_COLOR]) && $request->filled('hex_code')) {
            $rules['hex_code'] = ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'];
        }

        try {
            $validated = $request->validate($rules);

            $attributeService = new \App\Services\Attribute\AttributeService();

            // Procesar metadata usando AttributeService
            $validated['metadata'] = $attributeService->prepareAttributeMetadata(
                $request,
                $productAttribute->type,
                $productAttribute->metadata ?? []
            );
            $validated['sort_order'] = $validated['sort_order'] ?? $productAttribute->sort_order ?? 0;
            $validated['price_modifier'] = $validated['price_modifier'] ?? 0;
            $validated['price_percentage'] = $validated['price_percentage'] ?? 0;
            $validated['active'] = $request->boolean('active', $productAttribute->active);
            $validated['is_recommended'] = $request->boolean('is_recommended', $productAttribute->is_recommended);

            $productAttribute->update($validated);

            return redirect()->route('admin.product-attributes.index')
                ->with('success', 'Atributo actualizado exitosamente.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->validator->errors())
                ->with('error', 'Por favor corrige los errores en el formulario.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el atributo: ' . $e->getMessage());
        }
    }

    public function destroy(ProductAttribute $productAttribute)
    {
        $attributeService = new \App\Services\Attribute\AttributeService();

        // Validar si el atributo puede eliminarse usando AttributeService
        $validation = $attributeService->canDeleteAttribute($productAttribute);

        if (!$validation['can_delete']) {
            return redirect()->route('admin.product-attributes.index')
                ->with('error', 'No se puede eliminar el atributo: ' . $validation['reason']);
        }

        $productAttribute->delete();

        // Si es una petición AJAX, devolver JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Atributo eliminado exitosamente.'
            ]);
        }

        return redirect()->route('admin.product-attributes.index')
            ->with('success', 'Atributo eliminado exitosamente.');
    }

    /**
     * API para obtener atributos por tipo (usado en formularios dinámicos)
     */
    public function getByType(Request $request)
    {
        $type = $request->input('type');
        $attributes = ProductAttribute::byType($type)->active()->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'attributes' => $attributes
        ]);
    }

    /**
     * Actualizar orden de los atributos
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:product_attributes,id',
            'orders.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($validated['orders'] as $order) {
            ProductAttribute::where('id', $order['id'])
                ->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado exitosamente.'
        ]);
    }

    /**
     * Duplicar un atributo
     */
    public function duplicate(ProductAttribute $productAttribute)
    {
        $duplicate = $productAttribute->replicate();
        $duplicate->name = $productAttribute->name . ' (Copia)';
        $duplicate->value = $productAttribute->value . '_COPY_' . time();
        $duplicate->active = false;
        $duplicate->save();

        return redirect()->route('admin.product-attributes.edit', $duplicate)
            ->with('success', 'Atributo duplicado exitosamente. Recuerda actualizar el nombre y valor.');
    }

}