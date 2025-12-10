<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\V1\ProductResource;
use Illuminate\Http\Request;

/**
 * Product API Controller
 *
 * Endpoints públicos para listar y ver productos
 * Optimizado con eager loading, caching y filtros
 */
class ProductController extends Controller
{
    /**
     * Display a listing of products
     *
     * @queryParam page integer Número de página. Example: 1
     * @queryParam per_page integer Items por página (máx 50). Example: 15
     * @queryParam search string Buscar por nombre, SKU o descripción. Example: servilleta
     * @queryParam category_id integer Filtrar por categoría. Example: 1
     * @queryParam subcategory_id integer Filtrar por subcategoría. Example: 2
     * @queryParam has_configurator boolean Filtrar productos con configurador. Example: 1
     * @queryParam active boolean Filtrar por estado activo. Example: 1
     * @queryParam sort string Campo de ordenamiento. Example: name
     * @queryParam order string Dirección de ordenamiento (asc|desc). Example: asc
     *
     * @response 200 {
     *   "data": [...],
     *   "links": {...},
     *   "meta": {...}
     * }
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:50',
            'search' => 'string|max:255',
            'category_id' => 'integer|exists:categories,id',
            'subcategory_id' => 'integer|exists:subcategories,id',
            'has_configurator' => 'in:0,1,true,false',
            'active' => 'in:0,1,true,false',
            'sort' => 'string|in:name,created_at,updated_at,configurator_base_price',
            'order' => 'string|in:asc,desc',
        ]);

        $query = Product::query()
            ->with(['category', 'subcategory', 'printingSystems', 'pricing'])
            ->withCount('orderItems');

        // Filtro por estado activo (por defecto solo activos)
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        } else {
            $query->where('active', true);
        }

        // Filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por subcategoría
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Filtro por productos con configurador
        if ($request->has('has_configurator')) {
            $query->where('has_configurator', $request->boolean('has_configurator'));
        }

        // Ordenamiento
        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Paginación
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Display the specified product
     *
     * @urlParam product mixed required ID o slug del producto. Example: 1 o bolsas-sin-asas
     *
     * @response 200 {
     *   "data": {...}
     * }
     * @response 404 {
     *   "message": "Producto no encontrado"
     * }
     */
    public function show($identifier)
    {
        // Intentar buscar por ID si es numérico, sino por slug
        $query = Product::with([
            'category',
            'subcategory',
            'printingSystems',
            'pricing',
            'productAttributes.attributeGroup',
            'productAttributes' // Cargar atributos para el configurador
        ]);

        if (is_numeric($identifier)) {
            $product = $query->findOrFail($identifier);
        } else {
            $product = $query->where('slug', $identifier)->firstOrFail();
        }

        // Verificar que el producto esté activo (para API pública)
        if (!$product->active) {
            return response()->json([
                'message' => 'Producto no disponible'
            ], 404);
        }

        return new ProductResource($product);
    }
}
