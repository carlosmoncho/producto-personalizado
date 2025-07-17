<?php 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\SubcategoryController as ApiSubcategoryController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\CustomFieldController as ApiCustomFieldController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas públicas (para el frontend)
Route::prefix('v1')->group(function () {
    
    // Categorías y subcategorías
    Route::get('categories', [ApiCategoryController::class, 'index']);
    Route::get('categories/{category}', [ApiCategoryController::class, 'show']);
    Route::get('subcategories', [ApiSubcategoryController::class, 'index']);
    Route::get('subcategories/{subcategory}', [ApiSubcategoryController::class, 'show']);
    
    // Productos
    Route::get('products', [ApiProductController::class, 'index']);
    Route::get('products/{product}', [ApiProductController::class, 'show']);
    
    // Campos personalizados
    Route::get('custom-fields', [ApiCustomFieldController::class, 'index']);
    
    // Pedidos - Solo creación para el frontend
    Route::post('orders', [ApiOrderController::class, 'store']);
    Route::get('orders/{order}', [ApiOrderController::class, 'show']);
    
});

// Rutas protegidas (para el panel de administración API)
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    
    // Gestión de categorías
    Route::apiResource('categories', ApiCategoryController::class);
    
    // Gestión de subcategorías
    Route::apiResource('subcategories', ApiSubcategoryController::class);
    
    // Gestión de productos
    Route::apiResource('products', ApiProductController::class);
    
    // Gestión de campos personalizados
    Route::apiResource('custom-fields', ApiCustomFieldController::class);
    
    // Gestión de pedidos
    Route::apiResource('orders', ApiOrderController::class);
    Route::patch('orders/{order}/status', [ApiOrderController::class, 'updateStatus']);
    
});

// Rutas adicionales para funcionalidades específicas
Route::prefix('v1')->group(function () {
    
    // Obtener productos por categoría
    Route::get('categories/{category}/products', function($category) {
        $category = \App\Models\Category::where('slug', $category)->firstOrFail();
        return response()->json($category->products()->with(['pricing', 'customFields'])->get());
    });
    
    // Obtener productos por subcategoría
    Route::get('subcategories/{subcategory}/products', function($subcategory) {
        $subcategory = \App\Models\Subcategory::where('slug', $subcategory)->firstOrFail();
        return response()->json($subcategory->products()->with(['pricing', 'customFields'])->get());
    });
    
    // Obtener precio de producto para cantidad específica
    Route::get('products/{product}/price/{quantity}', function($product, $quantity) {
        $product = \App\Models\Product::where('slug', $product)->firstOrFail();
        $pricing = $product->getPriceForQuantity($quantity);
        
        if (!$pricing) {
            return response()->json(['error' => 'No se encontró precio para esta cantidad'], 404);
        }
        
        return response()->json($pricing);
    });
    
    // Búsqueda de productos
    Route::get('search/products', function(Request $request) {
        $query = \App\Models\Product::with(['category', 'subcategory', 'pricing']);
        
        if ($request->has('q')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%')
                  ->orWhere('sku', 'like', '%' . $request->q . '%');
            });
        }
        
        if ($request->has('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        if ($request->has('subcategory')) {
            $query->whereHas('subcategory', function($q) use ($request) {
                $q->where('slug', $request->subcategory);
            });
        }
        
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }
        
        if ($request->has('material')) {
            $query->where('material', $request->material);
        }
        
        $products = $query->where('active', true)
                         ->paginate($request->per_page ?? 12);
        
        return response()->json($products);
    });
    
    // Obtener opciones de filtros
    Route::get('filters', function() {
        $colors = \App\Models\Product::distinct()->pluck('color');
        $materials = \App\Models\Product::distinct()->pluck('material');
        $printingSystems = \App\Models\Product::distinct()->pluck('printing_system');
        
        return response()->json([
            'colors' => $colors,
            'materials' => $materials,
            'printing_systems' => $printingSystems
        ]);
    });
    
    // Validar disponibilidad de producto
    Route::post('products/{product}/validate', function(Request $request, $product) {
        $product = \App\Models\Product::where('slug', $product)->firstOrFail();
        
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'size' => 'required|string',
            'custom_fields' => 'nullable|array'
        ]);
        
        // Validar que el tamaño existe
        if (!in_array($validated['size'], $product->sizes)) {
            return response()->json(['error' => 'Tamaño no disponible'], 400);
        }
        
        // Obtener precio para la cantidad
        $pricing = $product->getPriceForQuantity($validated['quantity']);
        if (!$pricing) {
            return response()->json(['error' => 'No hay precio disponible para esta cantidad'], 400);
        }
        
        return response()->json([
            'valid' => true,
            'pricing' => $pricing,
            'total_price' => $pricing->unit_price * $validated['quantity']
        ]);
    });
    
    // Calcular precio total de carrito
    Route::post('cart/calculate', function(Request $request) {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_slug' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'required|string'
        ]);
        
        $total = 0;
        $items = [];
        
        foreach ($validated['items'] as $itemData) {
            $product = \App\Models\Product::where('slug', $itemData['product_slug'])->first();
            
            if (!$product) {
                return response()->json(['error' => 'Producto no encontrado: ' . $itemData['product_slug']], 404);
            }
            
            $pricing = $product->getPriceForQuantity($itemData['quantity']);
            if (!$pricing) {
                return response()->json(['error' => 'No hay precio disponible para la cantidad: ' . $itemData['quantity']], 400);
            }
            
            $itemTotal = $pricing->unit_price * $itemData['quantity'];
            $total += $itemTotal;
            
            $items[] = [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'slug' => $product->slug
                ],
                'quantity' => $itemData['quantity'],
                'size' => $itemData['size'],
                'unit_price' => $pricing->unit_price,
                'total_price' => $itemTotal
            ];
        }
        
        return response()->json([
            'items' => $items,
            'total' => $total,
            'currency' => 'EUR'
        ]);
    });
    
});
