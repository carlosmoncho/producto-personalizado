<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeDependency;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeDependencyController extends Controller
{
    public function index(Request $request)
    {
        // Si se hace clic en "Limpiar", borrar filtros de sesión
        if ($request->has('clear_filters')) {
            session()->forget(['ad_filter_type', 'ad_filter_product', 'ad_filter_attribute_type']);
            return redirect()->route('admin.attribute-dependencies.index');
        }

        // Guardar filtros en sesión si vienen en la request
        if ($request->has('filter_type') || $request->has('filter_product') || $request->has('filter_attribute_type')) {
            session(['ad_filter_type' => $request->input('filter_type', '')]);
            session(['ad_filter_product' => $request->input('filter_product', '')]);
            session(['ad_filter_attribute_type' => $request->input('filter_attribute_type', '')]);
        }

        // Obtener filtros de sesión o de request
        $filterType = $request->input('filter_type', session('ad_filter_type', ''));
        $filterProduct = $request->input('filter_product', session('ad_filter_product', ''));
        $filterAttributeType = $request->input('filter_attribute_type', session('ad_filter_attribute_type', ''));

        $query = AttributeDependency::with(['parentAttribute', 'dependentAttribute', 'thirdAttribute', 'product']);

        // Filtro por tipo (individual vs combinación)
        if ($filterType) {
            if ($filterType === 'individual') {
                $query->whereNull('dependent_attribute_id');
            } elseif ($filterType === 'combination') {
                $query->whereNotNull('dependent_attribute_id');
            }
        }

        // Filtro por producto
        if ($filterProduct) {
            if ($filterProduct === 'global') {
                $query->whereNull('product_id');
            } else {
                $query->where('product_id', $filterProduct);
            }
        }

        // Filtro por tipo de atributo (padre)
        if ($filterAttributeType) {
            $query->whereHas('parentAttribute', function($q) use ($filterAttributeType) {
                $q->where('type', $filterAttributeType);
            });
        }

        // Filtros legacy
        if ($request->filled('parent_type')) {
            $query->whereHas('parentAttribute', function($q) use ($request) {
                $q->where('type', $request->parent_type);
            });
        }

        if ($request->filled('dependent_type')) {
            $query->whereHas('dependentAttribute', function($q) use ($request) {
                $q->where('type', $request->dependent_type);
            });
        }

        if ($request->filled('condition_type')) {
            $query->where('condition_type', $request->condition_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('parentAttribute', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('value', 'like', "%{$search}%");
                })->orWhereHas('dependentAttribute', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('value', 'like', "%{$search}%");
                });
            });
        }

        // Si es una petición AJAX, devolver JSON
        if ($request->has('ajax')) {
            $dependenciesData = $query->orderBy('priority')->orderBy('created_at')->get();
            return response()->json([
                'success' => true,
                'dependencies' => $dependenciesData->load(['parentAttribute', 'dependentAttribute', 'product'])
            ]);
        }

        $dependencies = $query->orderBy('priority')->orderBy('created_at')->paginate(20)->withQueryString();

        // Obtener productos con configurador para el filtro
        $products = \App\Models\Product::where('has_configurator', true)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Obtener tipos únicos para filtros
        $parentTypes = ProductAttribute::select('type')->distinct()->pluck('type');
        $dependentTypes = ProductAttribute::select('type')->distinct()->pluck('type');

        $conditionTypes = [
            'allows' => 'Permite',
            'blocks' => 'Bloquea',
            'requires' => 'Requiere',
            'sets_price' => 'Modifica Precio'
        ];

        $typeLabels = [
            ProductAttribute::TYPE_COLOR => 'Colores',
            ProductAttribute::TYPE_MATERIAL => 'Materiales',
            ProductAttribute::TYPE_SIZE => 'Tamaños',
            ProductAttribute::TYPE_INK => 'Tintas',
            ProductAttribute::TYPE_QUANTITY => 'Cantidades',
            ProductAttribute::TYPE_SYSTEM => 'Sistemas'
        ];

        // Filtros actuales para la vista
        $filters = [
            'type' => $filterType,
            'product' => $filterProduct,
            'attribute_type' => $filterAttributeType,
        ];

        return view('admin.attribute-dependencies.index', compact(
            'dependencies', 'parentTypes', 'dependentTypes', 'conditionTypes', 'typeLabels', 'products', 'filters'
        ));
    }

    public function create()
    {
        // Obtener productos con configurador habilitado
        $products = \App\Models\Product::where('has_configurator', true)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Obtener todos los atributos agrupados por tipo
        $attributesByType = ProductAttribute::active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        $conditionTypes = [
            'allows' => 'Permite',
            'blocks' => 'Bloquea',
            'requires' => 'Requiere',
            'sets_price' => 'Modifica Precio'
        ];

        $typeLabels = [
            ProductAttribute::TYPE_COLOR => 'Color',
            ProductAttribute::TYPE_MATERIAL => 'Material',
            ProductAttribute::TYPE_SIZE => 'Tamaño',
            ProductAttribute::TYPE_INK => 'Tinta',
            ProductAttribute::TYPE_QUANTITY => 'Cantidad',
            ProductAttribute::TYPE_SYSTEM => 'Sistema'
        ];

        return view('admin.attribute-dependencies.create', compact(
            'products', 'attributesByType', 'conditionTypes', 'typeLabels'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'parent_attribute_ids' => 'required|array|min:1',
            'parent_attribute_ids.*' => 'exists:product_attributes,id',
            'dependent_attribute_ids' => 'nullable|array',
            'dependent_attribute_ids.*' => 'exists:product_attributes,id',
            'condition_type' => 'nullable|in:allows,blocks,requires,sets_price,price_modifier',
            'conditions' => 'nullable|json',
            'price_impact' => 'nullable|numeric|min:-999|max:999',
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'price_percentage' => 'nullable|numeric|min:-100|max:1000',
            'price_applies_to' => 'nullable|in:unit,total',
            'auto_select' => 'boolean',
            'reset_dependents' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:999'
        ]);

        $parentIds = $validated['parent_attribute_ids'];
        $dependentIds = $validated['dependent_attribute_ids'] ?? [];
        $hasDependent = !empty($dependentIds);

        // Validaciones específicas
        if (!$hasDependent) {
            if (!$validated['price_modifier']) {
                return back()
                    ->withInput()
                    ->withErrors(['price_modifier' => 'Para modificadores individuales es necesario especificar un modificador de precio.']);
            }
            $conditionType = 'price_modifier';
        } else {
            if (!$validated['condition_type']) {
                return back()
                    ->withInput()
                    ->withErrors(['condition_type' => 'La condición es requerida para dependencias entre atributos.']);
            }
            $conditionType = $validated['condition_type'];
        }

        // Datos comunes para todas las dependencias
        $commonData = [
            'product_id' => $validated['product_id'] ?? null,
            'condition_type' => $conditionType,
            'conditions' => $validated['conditions'] ?? null,
            'price_impact' => $validated['price_impact'] ?? null,
            'price_modifier' => $validated['price_modifier'] ?? null,
            'price_percentage' => $validated['price_percentage'] ?? null,
            'price_applies_to' => $validated['price_applies_to'] ?? 'unit',
            'auto_select' => $validated['auto_select'] ?? false,
            'reset_dependents' => $validated['reset_dependents'] ?? false,
            'priority' => $validated['priority'] ?? 0,
            'active' => true,
        ];

        $created = 0;
        $skipped = 0;

        // Generar todas las combinaciones
        foreach ($parentIds as $parentId) {
            if (!$hasDependent) {
                // Modificadores individuales
                $exists = AttributeDependency::where('parent_attribute_id', $parentId)
                    ->whereNull('dependent_attribute_id')
                    ->where('condition_type', $conditionType)
                    ->where('product_id', $commonData['product_id'])
                    ->exists();

                if (!$exists) {
                    AttributeDependency::create(array_merge($commonData, [
                        'parent_attribute_id' => $parentId,
                        'dependent_attribute_id' => null,
                    ]));
                    $created++;
                } else {
                    $skipped++;
                }
            } else {
                // Dependencias por combinación
                foreach ($dependentIds as $dependentId) {
                    if ($parentId == $dependentId) {
                        continue;
                    }

                    $exists = AttributeDependency::where('parent_attribute_id', $parentId)
                        ->where('dependent_attribute_id', $dependentId)
                        ->where('condition_type', $conditionType)
                        ->where('product_id', $commonData['product_id'])
                        ->exists();

                    if (!$exists) {
                        AttributeDependency::create(array_merge($commonData, [
                            'parent_attribute_id' => $parentId,
                            'dependent_attribute_id' => $dependentId,
                        ]));
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
            }
        }

        // Si es una petición AJAX, devolver JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Se crearon {$created} dependencias. {$skipped} ya existían.",
                'created' => $created,
                'skipped' => $skipped,
            ]);
        }

        $message = $created > 0
            ? "Se crearon {$created} dependencias exitosamente."
            : "No se crearon dependencias (todas ya existían).";

        if ($skipped > 0) {
            $message .= " {$skipped} combinaciones ya existían y fueron omitidas.";
        }

        return redirect()->route('admin.attribute-dependencies.index')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }

    public function show(AttributeDependency $attributeDependency)
    {
        $attributeDependency->load(['parentAttribute', 'dependentAttribute']);
        
        // Obtener dependencias relacionadas
        $relatedDependencies = AttributeDependency::where(function($query) use ($attributeDependency) {
            $query->where('parent_attribute_id', $attributeDependency->parent_attribute_id)
                  ->orWhere('dependent_attribute_id', $attributeDependency->parent_attribute_id)
                  ->orWhere('parent_attribute_id', $attributeDependency->dependent_attribute_id)
                  ->orWhere('dependent_attribute_id', $attributeDependency->dependent_attribute_id);
        })
        ->where('id', '!=', $attributeDependency->id)
        ->with(['parentAttribute', 'dependentAttribute'])
        ->take(10)
        ->get();

        return view('admin.attribute-dependencies.show', compact(
            'attributeDependency', 'relatedDependencies'
        ));
    }

    public function edit(AttributeDependency $attributeDependency)
    {
        $attributeDependency->load(['parentAttribute', 'dependentAttribute', 'thirdAttribute']);

        // Obtener productos con configurador habilitado
        $products = \App\Models\Product::where('has_configurator', true)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Obtener todos los atributos agrupados por tipo
        $attributesByType = ProductAttribute::active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        $conditionTypes = [
            'allows' => 'Permite',
            'blocks' => 'Bloquea',
            'requires' => 'Requiere',
            'sets_price' => 'Modifica Precio'
        ];

        $typeLabels = [
            ProductAttribute::TYPE_COLOR => 'Color',
            ProductAttribute::TYPE_MATERIAL => 'Material',
            ProductAttribute::TYPE_SIZE => 'Tamaño',
            ProductAttribute::TYPE_INK => 'Tinta',
            ProductAttribute::TYPE_QUANTITY => 'Cantidad',
            ProductAttribute::TYPE_SYSTEM => 'Sistema'
        ];

        return view('admin.attribute-dependencies.edit', compact(
            'attributeDependency', 'products', 'attributesByType', 'conditionTypes', 'typeLabels'
        ));
    }

    public function update(Request $request, AttributeDependency $attributeDependency)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'parent_attribute_id' => 'required|exists:product_attributes,id',
            'dependent_attribute_id' => 'nullable|exists:product_attributes,id|different:parent_attribute_id',
            'third_attribute_id' => 'nullable|exists:product_attributes,id|different:parent_attribute_id|different:dependent_attribute_id',
            'condition_type' => 'nullable|in:allows,blocks,requires,sets_price,price_modifier',
            'conditions' => 'nullable|json',
            'price_impact' => 'nullable|numeric|min:-999|max:999',
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'price_percentage' => 'nullable|numeric|min:-100|max:1000',
            'price_applies_to' => 'nullable|in:unit,total',
            'auto_select' => 'boolean',
            'reset_dependents' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:999'
        ]);

        // Valor por defecto para price_applies_to
        $validated['price_applies_to'] = $validated['price_applies_to'] ?? $attributeDependency->price_applies_to ?? 'unit';

        // Validaciones específicas
        // Si no hay atributo dependiente, debe ser un modificador de precio individual
        if (!$validated['dependent_attribute_id']) {
            if (!$validated['price_modifier']) {
                return back()
                    ->withInput()
                    ->withErrors(['price_modifier' => 'Para modificadores individuales es necesario especificar un modificador de precio.']);
            }
            // Asignar automáticamente el tipo de condición para modificadores individuales
            $validated['condition_type'] = 'price_modifier';
        } else {
            // Si hay atributo dependiente, la condición es requerida
            if (!$validated['condition_type']) {
                return back()
                    ->withInput()
                    ->withErrors(['condition_type' => 'La condición es requerida para dependencias entre atributos.']);
            }
        }

        // Validar que no existe ya la misma dependencia (excluyendo la actual)
        $query = AttributeDependency::where('parent_attribute_id', $validated['parent_attribute_id'])
            ->where('dependent_attribute_id', $validated['dependent_attribute_id'] ?? null)
            ->where('condition_type', $validated['condition_type'])
            ->where('product_id', $validated['product_id'] ?? null)
            ->where('id', '!=', $attributeDependency->id);

        if (isset($validated['third_attribute_id'])) {
            $query->where('third_attribute_id', $validated['third_attribute_id']);
        } else {
            $query->whereNull('third_attribute_id');
        }

        $existingDependency = $query->first();

        if ($existingDependency) {
            return back()
                ->withInput()
                ->withErrors(['dependent_attribute_id' => 'Ya existe una dependencia igual entre estos atributos.']);
        }

        $attributeDependency->update($validated);

        return redirect()->route('admin.attribute-dependencies.index')
            ->with('success', 'Dependencia actualizada exitosamente.');
    }

    public function destroy(AttributeDependency $attributeDependency)
    {
        $attributeDependency->delete();

        // Si es una petición AJAX, devolver JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dependencia eliminada exitosamente.'
            ]);
        }

        return redirect()->route('admin.attribute-dependencies.index')
            ->with('success', 'Dependencia eliminada exitosamente.');
    }

    /**
     * Mostrar formulario para crear modificador individual
     */
    public function createIndividual()
    {
        // Obtener productos con configurador habilitado
        $products = \App\Models\Product::where('has_configurator', true)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Obtener todos los atributos agrupados por tipo
        $attributesByType = ProductAttribute::active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        $typeLabels = [
            ProductAttribute::TYPE_COLOR => 'Color',
            ProductAttribute::TYPE_MATERIAL => 'Material',
            ProductAttribute::TYPE_SIZE => 'Tamaño',
            ProductAttribute::TYPE_INK => 'Tinta',
            ProductAttribute::TYPE_QUANTITY => 'Cantidad',
            ProductAttribute::TYPE_SYSTEM => 'Sistema'
        ];

        return view('admin.attribute-dependencies.create-individual', compact(
            'products', 'attributesByType', 'typeLabels'
        ));
    }

    /**
     * Guardar modificador individual
     */
    public function storeIndividual(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'parent_attribute_id' => 'required|exists:product_attributes,id',
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'price_percentage' => 'nullable|numeric|min:-100|max:1000',
            'price_applies_to' => 'nullable|in:unit,total',
            'priority' => 'nullable|integer|min:0|max:999'
        ]);

        // Debe tener al menos un modificador
        if (!$validated['price_modifier'] && !$validated['price_percentage']) {
            return back()
                ->withInput()
                ->withErrors(['price_modifier' => 'Debe especificar un modificador fijo o porcentual.']);
        }

        $validated['price_applies_to'] = $validated['price_applies_to'] ?? 'unit';

        // Verificar que no existe ya un modificador para este atributo y producto
        $existingModifier = AttributeDependency::where('product_id', $validated['product_id'])
            ->where('parent_attribute_id', $validated['parent_attribute_id'])
            ->whereNull('dependent_attribute_id')
            ->where('condition_type', 'price_modifier')
            ->first();

        if ($existingModifier) {
            return back()
                ->withInput()
                ->withErrors(['parent_attribute_id' => 'Ya existe un modificador individual para este atributo en este producto.']);
        }

        // Crear el modificador individual
        $dependency = AttributeDependency::create([
            'product_id' => $validated['product_id'],
            'parent_attribute_id' => $validated['parent_attribute_id'],
            'dependent_attribute_id' => null,
            'condition_type' => 'price_modifier',
            'price_modifier' => $validated['price_modifier'],
            'price_percentage' => $validated['price_percentage'],
            'price_applies_to' => $validated['price_applies_to'],
            'priority' => $validated['priority'] ?? 0,
            'active' => true
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Modificador individual creado exitosamente.',
                'dependency' => $dependency->load(['parentAttribute', 'product'])
            ]);
        }

        return redirect()->route('admin.attribute-dependencies.index')
            ->with('success', 'Modificador individual creado exitosamente.');
    }

    /**
     * Mostrar formulario para crear dependencia por combinación
     */
    public function createCombination()
    {
        // Obtener productos con configurador habilitado
        $products = \App\Models\Product::where('has_configurator', true)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Obtener todos los atributos agrupados por tipo
        $attributesByType = ProductAttribute::active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        $conditionTypes = [
            'allows' => 'Permite',
            'blocks' => 'Bloquea',
            'requires' => 'Requiere',
            'sets_price' => 'Modifica Precio'
        ];

        $typeLabels = [
            ProductAttribute::TYPE_COLOR => 'Color',
            ProductAttribute::TYPE_MATERIAL => 'Material',
            ProductAttribute::TYPE_SIZE => 'Tamaño',
            ProductAttribute::TYPE_INK => 'Tinta',
            ProductAttribute::TYPE_QUANTITY => 'Cantidad',
            ProductAttribute::TYPE_SYSTEM => 'Sistema'
        ];

        return view('admin.attribute-dependencies.create-combination', compact(
            'products', 'attributesByType', 'conditionTypes', 'typeLabels'
        ));
    }

    /**
     * Guardar dependencia por combinación
     */
    public function storeCombination(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'parent_attribute_ids' => 'required|array|min:1',
            'parent_attribute_ids.*' => 'exists:product_attributes,id',
            'dependent_attribute_ids' => 'required|array|min:1',
            'dependent_attribute_ids.*' => 'exists:product_attributes,id',
            'third_attribute_ids' => 'nullable|array',
            'third_attribute_ids.*' => 'exists:product_attributes,id',
            'condition_type' => 'required|in:allows,blocks,requires,sets_price',
            'conditions' => 'nullable|json',
            'price_impact' => 'nullable|numeric|min:-999|max:999',
            'price_modifier' => 'nullable|numeric|min:-999|max:999',
            'price_percentage' => 'nullable|numeric|min:-100|max:1000',
            'price_applies_to' => 'nullable|in:unit,total',
            'auto_select' => 'boolean',
            'reset_dependents' => 'boolean',
            'priority' => 'nullable|integer|min:0|max:999'
        ]);

        $validated['price_applies_to'] = $validated['price_applies_to'] ?? 'unit';

        $parentIds = $validated['parent_attribute_ids'];
        $dependentIds = $validated['dependent_attribute_ids'];
        $thirdIds = $validated['third_attribute_ids'] ?? [];

        // Datos comunes para todas las dependencias
        $commonData = [
            'product_id' => $validated['product_id'] ?? null,
            'condition_type' => $validated['condition_type'],
            'conditions' => $validated['conditions'] ?? null,
            'price_impact' => $validated['price_impact'] ?? null,
            'price_modifier' => $validated['price_modifier'] ?? null,
            'price_percentage' => $validated['price_percentage'] ?? null,
            'price_applies_to' => $validated['price_applies_to'],
            'auto_select' => $validated['auto_select'] ?? false,
            'reset_dependents' => $validated['reset_dependents'] ?? false,
            'priority' => $validated['priority'] ?? 0,
            'active' => true,
        ];

        $created = 0;
        $skipped = 0;

        // Generar todas las combinaciones
        foreach ($parentIds as $parentId) {
            foreach ($dependentIds as $dependentId) {
                // No permitir mismo atributo
                if ($parentId == $dependentId) {
                    continue;
                }

                if (empty($thirdIds)) {
                    // Combinación de 2 atributos
                    $exists = AttributeDependency::where('parent_attribute_id', $parentId)
                        ->where('dependent_attribute_id', $dependentId)
                        ->where('condition_type', $commonData['condition_type'])
                        ->where('product_id', $commonData['product_id'])
                        ->whereNull('third_attribute_id')
                        ->exists();

                    if (!$exists) {
                        AttributeDependency::create(array_merge($commonData, [
                            'parent_attribute_id' => $parentId,
                            'dependent_attribute_id' => $dependentId,
                            'third_attribute_id' => null,
                        ]));
                        $created++;
                    } else {
                        $skipped++;
                    }
                } else {
                    // Combinación de 3 atributos
                    foreach ($thirdIds as $thirdId) {
                        // No permitir atributos repetidos
                        if ($thirdId == $parentId || $thirdId == $dependentId) {
                            continue;
                        }

                        $exists = AttributeDependency::where('parent_attribute_id', $parentId)
                            ->where('dependent_attribute_id', $dependentId)
                            ->where('third_attribute_id', $thirdId)
                            ->where('condition_type', $commonData['condition_type'])
                            ->where('product_id', $commonData['product_id'])
                            ->exists();

                        if (!$exists) {
                            AttributeDependency::create(array_merge($commonData, [
                                'parent_attribute_id' => $parentId,
                                'dependent_attribute_id' => $dependentId,
                                'third_attribute_id' => $thirdId,
                            ]));
                            $created++;
                        } else {
                            $skipped++;
                        }
                    }
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Se crearon {$created} dependencias. {$skipped} ya existían.",
                'created' => $created,
                'skipped' => $skipped,
            ]);
        }

        $message = $created > 0
            ? "Se crearon {$created} dependencias exitosamente."
            : "No se crearon dependencias (todas ya existían).";

        if ($skipped > 0) {
            $message .= " {$skipped} combinaciones ya existían y fueron omitidas.";
        }

        return redirect()->route('admin.attribute-dependencies.index')
            ->with($created > 0 ? 'success' : 'warning', $message);
    }

    /**
     * API: Obtener atributos por tipo para formularios dinámicos
     */
    public function getAttributesByType(Request $request)
    {
        $type = $request->input('type');
        $excludeId = $request->input('exclude_id');

        $query = ProductAttribute::byType($type)->active()->orderBy('sort_order');
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $attributes = $query->get();

        return response()->json([
            'success' => true,
            'attributes' => $attributes
        ]);
    }

    /**
     * API: Previsualizar dependencias de un atributo
     */
    public function previewDependencies(Request $request)
    {
        $attributeId = $request->input('attribute_id');
        
        $dependencies = AttributeDependency::where('parent_attribute_id', $attributeId)
            ->orWhere('dependent_attribute_id', $attributeId)
            ->with(['parentAttribute', 'dependentAttribute'])
            ->get();

        return response()->json([
            'success' => true,
            'dependencies' => $dependencies
        ]);
    }


    /**
     * Duplicar una dependencia
     */
    public function duplicate(AttributeDependency $attributeDependency)
    {
        $duplicate = $attributeDependency->replicate();
        $duplicate->priority = ($attributeDependency->priority ?? 0) + 1;
        $duplicate->save();

        return redirect()->route('admin.attribute-dependencies.edit', $duplicate)
            ->with('success', 'Dependencia duplicada exitosamente.');
    }

    /**
     * Validar configuración completa de dependencias
     */
    public function validateConfiguration()
    {
        $attributeService = new \App\Services\Attribute\AttributeService();

        // Validar usando AttributeService
        $validation = $attributeService->validateDependencyConfiguration();

        return response()->json([
            'success' => true,
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'],
            'is_valid' => $validation['valid']
        ]);
    }

}