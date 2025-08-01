<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\SubcategoryController as ApiSubcategoryController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
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
    
    // Gestión de pedidos
    Route::apiResource('orders', ApiOrderController::class);
    Route::patch('orders/{order}/status', [ApiOrderController::class, 'updateStatus']);
    
});

// Rutas adicionales para funcionalidades específicas
Route::prefix('v1')->group(function () {
    
    // Obtener productos por categoría
    Route::get('categories/{category}/products', function($category) {
        $category = \App\Models\Category::where('slug', $category)->firstOrFail();
        return $category->products()->where('active', true)->get();
    });
    
    // Obtener productos por subcategoría
    Route::get('subcategories/{subcategory}/products', function($subcategory) {
        $subcategory = \App\Models\Subcategory::where('slug', $subcategory)->firstOrFail();
        return $subcategory->products()->where('active', true)->get();
    });
    
});