<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\SubcategoryController as ApiSubcategoryController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\V1\ConfiguratorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Api\PerformanceController;

// ============ HEALTH CHECK ENDPOINTS ============
// Con rate limiting alto para permitir monitoreo pero proteger contra abuso
Route::prefix('health')->middleware('throttle:health-check')->name('health.')->group(function () {
    Route::get('/', [HealthCheckController::class, 'index'])->name('index');
    Route::get('/detailed', [HealthCheckController::class, 'detailed'])->name('detailed');
    Route::get('/metrics', [HealthCheckController::class, 'metrics'])->name('metrics');
    Route::get('/ready', [HealthCheckController::class, 'ready'])->name('ready');
    Route::get('/alive', [HealthCheckController::class, 'alive'])->name('alive');
});

// ============ AUTHENTICATION ENDPOINTS ============
// Para SPA Next.js usando sesiones
// IMPORTANTE: Usamos middleware 'web' para que las sesiones funcionen
Route::prefix('auth')->middleware('web')->name('auth.')->group(function () {
    // Rutas públicas
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/check', [AuthController::class, 'check'])->name('check');

    // Rutas protegidas (requieren autenticación)
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::get('/customer-data', [AuthController::class, 'getCustomerData'])->name('customer-data');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::delete('/account', [AuthController::class, 'deleteAccount'])->name('account.delete');
    });
});

// ============ ADDRESS MANAGEMENT ENDPOINTS ============
// Gestión de direcciones de envío y facturación
Route::prefix('auth/addresses')->middleware(['web', 'auth'])->name('addresses.')->group(function () {
    Route::get('/', [AddressController::class, 'index'])->name('index');
    Route::post('/', [AddressController::class, 'store'])->name('store');
    Route::get('/{id}', [AddressController::class, 'show'])->name('show');
    Route::put('/{id}', [AddressController::class, 'update'])->name('update');
    Route::delete('/{id}', [AddressController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/set-default', [AddressController::class, 'setDefault'])->name('set-default');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Storage files endpoint (for CORS-enabled access to 3D models and images)
// Sirve automáticamente WebP si existe y el navegador lo soporta
Route::get('/storage/{path}', function ($path, Request $request) {
    $filePath = storage_path('app/public/' . $path);
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    // Para imágenes PNG/JPG, intentar servir WebP si existe y es soportado
    if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
        $acceptHeader = $request->header('Accept', '');
        $supportsWebp = str_contains($acceptHeader, 'image/webp');

        if ($supportsWebp) {
            $webpPath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $path);
            $webpFilePath = storage_path('app/public/' . $webpPath);

            if (file_exists($webpFilePath)) {
                return response()->file($webpFilePath, [
                    'Content-Type' => 'image/webp',
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            }
        }
    }

    if (!file_exists($filePath)) {
        abort(404);
    }

    // Cache largo para assets estáticos
    $cacheableExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'glb', 'gltf'];
    $headers = in_array($extension, $cacheableExtensions)
        ? ['Cache-Control' => 'public, max-age=31536000, immutable']
        : [];

    return response()->file($filePath, $headers);
})->where('path', '.*');

// Rutas públicas (para el frontend) con rate limiting
Route::prefix('v1')->group(function () {

    // Categorías y subcategorías - Rate limiter de lectura pública
    Route::middleware(['throttle:public-read'])->group(function () {
        Route::get('categories', [ApiCategoryController::class, 'index']);
        Route::get('categories/{category}', [ApiCategoryController::class, 'show']);
        Route::get('subcategories', [ApiSubcategoryController::class, 'index']);
        Route::get('subcategories/{subcategory}', [ApiSubcategoryController::class, 'show']);

        // Productos
        Route::get('products', [ApiProductController::class, 'index']);
        Route::get('products/{product}', [ApiProductController::class, 'show']);

        // Pedidos - Solo consulta
        Route::get('orders/{order}', [ApiOrderController::class, 'show']);
    });

    // Pedidos - Creación con límite MUY RESTRICTIVO (2/min, 10/hora, 50/día)
    Route::post('orders', [ApiOrderController::class, 'store'])
        ->middleware('throttle:orders');

    // ============ CONFIGURADOR DE PRODUCTOS ============
    Route::prefix('configurator')->name('configurator.')->group(function () {

        // Obtener configuración inicial del producto - Lectura pública
        Route::get('products/{product}/config', [ConfiguratorController::class, 'getConfig'])
            ->middleware('throttle:public-read')
            ->name('config');

        // Obtener atributos disponibles según selección actual - API estricta
        Route::post('products/{product}/attributes', [ConfiguratorController::class, 'getAvailableAttributes'])
            ->middleware('throttle:api-strict')
            ->name('attributes');

        // Calcular precio dinámico - Rate limiter específico (20/min, 200/hora)
        Route::post('products/{product}/price', [ConfiguratorController::class, 'calculatePrice'])
            ->middleware('throttle:price-calculation')
            ->name('price');

        // Validar configuración completa - API estricta
        Route::post('products/{product}/validate', [ConfiguratorController::class, 'validateConfiguration'])
            ->middleware('throttle:api-strict')
            ->name('validate');

        // Obtener tintas recomendadas por contraste - API estricta
        Route::post('inks/recommended', [ConfiguratorController::class, 'getRecommendedInks'])
            ->middleware('throttle:api-strict')
            ->name('inks.recommended');

        // Guardar configuración (requiere sesión) - API general
        Route::post('products/{product}/save', [ConfiguratorController::class, 'saveConfiguration'])
            ->middleware(['web', 'throttle:api'])
            ->name('save');

        // Obtener configuración guardada (requiere sesión) - API general
        Route::get('products/{product}/configuration', [ConfiguratorController::class, 'getConfiguration'])
            ->middleware(['web', 'throttle:api'])
            ->name('configuration');
    });

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

// ============ PERFORMANCE MONITORING ============
Route::prefix('performance')->name('performance.')->group(function () {
    // Public endpoints for viewing metrics
    Route::get('/', [PerformanceController::class, 'index'])->name('index');
    Route::get('/summary', [PerformanceController::class, 'summary'])->name('summary');
    Route::get('/trends', [PerformanceController::class, 'trends'])->name('trends');

    // Admin endpoint to trigger new audit
    Route::post('/audit', [PerformanceController::class, 'runAudit'])
        ->middleware(['auth:sanctum'])
        ->name('audit');
});

// Rutas adicionales para funcionalidades específicas - Con rate limiting
Route::prefix('v1')->middleware(['throttle:public-read'])->group(function () {

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