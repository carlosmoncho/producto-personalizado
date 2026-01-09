<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceRule;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceRule::with(['product', 'category']);

        // Filtros
        if ($request->filled('rule_type')) {
            $query->where('rule_type', $request->rule_type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active === '1');
        }

        // Si es una petición AJAX, devolver JSON
        if ($request->has('ajax')) {
            $rulesData = $query->orderBy('priority', 'desc')
                              ->orderBy('sort_order')
                              ->get();
            return response()->json([
                'success' => true,
                'rules' => $rulesData->load(['product', 'category'])
            ]);
        }

        $rules = $query->orderBy('priority', 'desc')
                      ->orderBy('sort_order')
                      ->paginate(15);

        // Para filtros
        $products = Product::active()->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.price-rules.index', compact('rules', 'products', 'categories'));
    }

    public function create()
    {
        $products = Product::active()->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('active', true)->orderBy('name')->get(['id', 'name']);
        $attributes = ProductAttribute::active()
                                    ->orderBy('type')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->groupBy('type');

        return view('admin.price-rules.create', compact('products', 'categories', 'attributes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rule_type' => 'required|in:combination,volume,attribute_specific,conditional',
            'action_type' => 'required|in:add_fixed,add_percentage,multiply,set_fixed,set_percentage',
            'action_value' => 'required|numeric',
            'priority' => 'integer|min:0|max:100',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity_min' => 'nullable|integer|min:1',
            'quantity_max' => 'nullable|integer|min:1|gte:quantity_min',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'active' => 'boolean',
            'conditions' => 'required',
        ]);

        // Procesar conditions (puede venir como JSON string o array)
        $conditions = $validated['conditions'];
        if (is_string($conditions)) {
            $conditions = json_decode($conditions, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Formato de condiciones inválido');
            }
        }

        try {
            DB::beginTransaction();

            $rule = PriceRule::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'rule_type' => $validated['rule_type'],
                'conditions' => $conditions,
                'action_type' => $validated['action_type'],
                'action_value' => $validated['action_value'],
                'priority' => $validated['priority'] ?? 0,
                'product_id' => $validated['product_id'],
                'category_id' => $validated['category_id'],
                'quantity_min' => $validated['quantity_min'],
                'quantity_max' => $validated['quantity_max'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'active' => $request->boolean('active', true),
                'sort_order' => PriceRule::max('sort_order') + 1,
            ]);

            DB::commit();

            // Si es una petición AJAX, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Regla de precio creada exitosamente.',
                    'rule' => $rule->load(['product', 'category'])
                ]);
            }

            return redirect()->route('admin.price-rules.index')
                           ->with('success', 'Regla de precio creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear la regla de precio: ' . $e->getMessage());
        }
    }

    public function show(PriceRule $priceRule)
    {
        $priceRule->load(['product', 'category']);

        return view('admin.price-rules.show', compact('priceRule'));
    }

    public function edit(PriceRule $priceRule)
    {
        $products = Product::active()->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('active', true)->orderBy('name')->get(['id', 'name']);
        $attributes = ProductAttribute::active()
                                    ->orderBy('type')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->groupBy('type');

        return view('admin.price-rules.edit', compact('priceRule', 'products', 'categories', 'attributes'));
    }

    public function update(Request $request, PriceRule $priceRule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rule_type' => 'required|in:combination,volume,attribute_specific,conditional',
            'action_type' => 'required|in:add_fixed,add_percentage,multiply,set_fixed,set_percentage',
            'action_value' => 'required|numeric',
            'priority' => 'integer|min:0|max:100',
            'product_id' => 'nullable|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity_min' => 'nullable|integer|min:1',
            'quantity_max' => 'nullable|integer|min:1|gte:quantity_min',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'active' => 'boolean',
            'conditions' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $priceRule->update([
                'name' => $request->name,
                'description' => $request->description,
                'rule_type' => $request->rule_type,
                'conditions' => $request->conditions,
                'action_type' => $request->action_type,
                'action_value' => $request->action_value,
                'priority' => $request->priority ?? 0,
                'product_id' => $request->product_id,
                'category_id' => $request->category_id,
                'quantity_min' => $request->quantity_min,
                'quantity_max' => $request->quantity_max,
                'valid_from' => $request->valid_from,
                'valid_until' => $request->valid_until,
                'active' => $request->boolean('active', true),
            ]);

            DB::commit();

            return redirect()->route('admin.price-rules.index')
                           ->with('success', 'Regla de precio actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar la regla de precio: ' . $e->getMessage());
        }
    }

    public function destroy(PriceRule $priceRule)
    {
        try {
            $ruleName = $priceRule->name;
            $priceRule->delete();

            // Si es una petición AJAX, devolver JSON
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Regla de precio '{$ruleName}' eliminada exitosamente."
                ]);
            }

            return redirect()->route('admin.price-rules.index')
                           ->with('success', "Regla de precio '{$ruleName}' eliminada exitosamente.");
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la regla de precio: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.price-rules.index')
                           ->with('error', 'Error al eliminar la regla de precio: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar múltiples reglas de precio en bloque
     */
    public function destroyBulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:price_rules,id'
        ]);

        $ids = $validated['ids'];
        $count = count($ids);

        try {
            DB::beginTransaction();

            PriceRule::whereIn('id', $ids)->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$count} reglas de precio exitosamente.",
                    'deleted_count' => $count
                ]);
            }

            return redirect()->route('admin.price-rules.index')
                           ->with('success', "Se eliminaron {$count} reglas de precio exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar las reglas de precio: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.price-rules.index')
                           ->with('error', 'Error al eliminar las reglas de precio: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleStatus(PriceRule $priceRule)
    {
        $priceRule->update(['active' => !$priceRule->active]);

        return response()->json([
            'success' => true,
            'active' => $priceRule->active,
            'message' => 'Estado actualizado exitosamente'
        ]);
    }

    /**
     * Duplicar regla de precio
     */
    public function duplicate(PriceRule $priceRule)
    {
        try {
            DB::beginTransaction();

            $newRule = $priceRule->replicate([
                'name',
                'sort_order'
            ]);
            
            $newRule->name = $priceRule->name . ' (Copia)';
            $newRule->active = false; // Crear como inactiva por seguridad
            $newRule->sort_order = PriceRule::max('sort_order') + 1;
            $newRule->save();

            DB::commit();

            return redirect()->route('admin.price-rules.edit', $newRule)
                           ->with('success', 'Regla de precio duplicada exitosamente. Recuerda activarla cuando esté lista.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.price-rules.index')
                           ->with('error', 'Error al duplicar la regla de precio: ' . $e->getMessage());
        }
    }

    /**
     * API para obtener atributos por tipo
     */
    public function getAttributesByType(Request $request)
    {
        $type = $request->input('type');
        
        if (!$type) {
            return response()->json(['error' => 'Type parameter is required'], 400);
        }

        $attributes = ProductAttribute::byType($type)
                                    ->active()
                                    ->orderBy('sort_order')
                                    ->get(['id', 'name', 'value']);

        return response()->json(['attributes' => $attributes]);
    }
}
