<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Product;
use App\Http\Resources\V1\SubcategoryResource;
use App\Http\Resources\V1\ProductResource;
use Illuminate\Http\Request;

/**
 * Subcategory API Controller
 *
 * Endpoints públicos para subcategorías y sus productos
 */
class SubcategoryController extends Controller
{
    /**
     * Display a listing of subcategories
     *
     * @queryParam category_id integer Filtrar por categoría. Example: 1
     * @queryParam active boolean Filtrar por estado activo. Example: 1
     * @queryParam with_products boolean Incluir conteo de productos. Example: 1
     *
     * @response 200 {
     *   "data": [...]
     * }
     */
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => 'integer|exists:categories,id',
            'active' => 'boolean',
            'with_products' => 'boolean',
        ]);

        $query = Subcategory::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name');

        // Filtrar por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtrar por activas (por defecto solo activas)
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        } else {
            $query->where('active', true);
        }

        // Agregar conteo de productos si se solicita
        if ($request->boolean('with_products')) {
            $query->withCount(['products' => function($q) {
                $q->where('active', true);
            }]);
        }

        $subcategories = $query->get();

        return SubcategoryResource::collection($subcategories);
    }

    /**
     * Display the specified subcategory
     *
     * @urlParam subcategory integer required ID de la subcategoría. Example: 1
     *
     * @response 200 {
     *   "data": {...}
     * }
     */
    public function show($id)
    {
        $subcategory = Subcategory::with('category')
            ->withCount(['products' => function($q) {
                $q->where('active', true);
            }])
            ->findOrFail($id);

        if (!$subcategory->active) {
            return response()->json([
                'message' => 'Subcategoría no disponible'
            ], 404);
        }

        return new SubcategoryResource($subcategory);
    }

    /**
     * Get products for a subcategory
     *
     * @urlParam subcategory integer required ID de la subcategoría. Example: 1
     * @queryParam page integer Número de página. Example: 1
     * @queryParam per_page integer Items por página (máx 50). Example: 15
     * @queryParam sort string Campo de ordenamiento. Example: name
     * @queryParam order string Dirección (asc|desc). Example: asc
     *
     * @response 200 {
     *   "data": [...],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function products(Request $request, $subcategoryId)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:50',
            'sort' => 'string|in:name,created_at,configurator_base_price',
            'order' => 'string|in:asc,desc',
        ]);

        // Verificar que la subcategoría existe y está activa
        $subcategory = Subcategory::where('id', $subcategoryId)
            ->where('active', true)
            ->firstOrFail();

        $query = Product::where('subcategory_id', $subcategoryId)
            ->where('active', true)
            ->with(['category', 'subcategory', 'printingSystems', 'pricing']);

        // Ordenamiento
        $sortField = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Paginación
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }
}
