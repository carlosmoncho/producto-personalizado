<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Http\Resources\V1\CategoryResource;
use App\Http\Resources\V1\ProductResource;
use Illuminate\Http\Request;

/**
 * Category API Controller
 *
 * Endpoints públicos para categorías y sus productos
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     *
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
            'active' => 'boolean',
            'with_products' => 'boolean',
        ]);

        $query = Category::query()
            ->with('subcategories')
            ->orderBy('sort_order')
            ->orderBy('name');

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

        $categories = $query->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Display the specified category
     *
     * @urlParam category integer required ID de la categoría. Example: 1
     *
     * @response 200 {
     *   "data": {...}
     * }
     */
    public function show($id)
    {
        $category = Category::with(['subcategories'])
            ->withCount(['products' => function($q) {
                $q->where('active', true);
            }])
            ->findOrFail($id);

        if (!$category->active) {
            return response()->json([
                'message' => 'Categoría no disponible'
            ], 404);
        }

        return new CategoryResource($category);
    }

    /**
     * Get products for a category
     *
     * @urlParam category integer required ID de la categoría. Example: 1
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
    public function products(Request $request, $categoryId)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:50',
            'sort' => 'string|in:name,created_at,configurator_base_price',
            'order' => 'string|in:asc,desc',
        ]);

        // Verificar que la categoría existe y está activa
        $category = Category::where('id', $categoryId)
            ->where('active', true)
            ->firstOrFail();

        $query = Product::where('category_id', $categoryId)
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
