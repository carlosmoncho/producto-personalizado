<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AvailableColorController; 
use App\Http\Controllers\Admin\AvailablePrintColorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AvailableMaterialController;
use App\Http\Controllers\Admin\PrintingSystemController;

Route::get('/', function () {
    return Auth::check() 
        ? redirect()->route('admin.dashboard') 
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas del panel de administración
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Ruta AJAX para datos de ventas del dashboard
    Route::get('/sales-data', [DashboardController::class, 'salesData'])->name('sales-data');
    
    // Categorías
    Route::resource('categories', CategoryController::class);
    // Ruta AJAX para verificar dependencias
    Route::get('categories/{category}/dependencies', [CategoryController::class, 'dependencies'])->name('categories.dependencies');
    
    // Subcategorías
    Route::resource('subcategories', SubcategoryController::class);
    // Ruta AJAX para verificar dependencias
    Route::get('subcategories/{subcategory}/dependencies', [SubcategoryController::class, 'dependencies'])->name('subcategories.dependencies');
    
    // Productos
    Route::resource('products', ProductController::class);
    // Ruta AJAX para obtener subcategorías por categoría
    Route::get('products/subcategories/{category}', [ProductController::class, 'getSubcategories'])->name('products.subcategories');
    // Ruta AJAX para verificar dependencias
    Route::get('products/{product}/dependencies', [ProductController::class, 'dependencies'])->name('products.dependencies');
    
    // API para búsqueda de productos
    Route::get('api/products/search', [ProductController::class, 'search'])->name('api.products.search');
    
    // Pedidos - ruta export debe ir ANTES del resource para evitar conflictos
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', OrderController::class);
    // Rutas adicionales para pedidos
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    // Ruta AJAX para verificar dependencias
    Route::get('orders/{order}/dependencies', [OrderController::class, 'dependencies'])->name('orders.dependencies');

    // Clientes - ruta export debe ir ANTES del resource para evitar conflictos
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::resource('customers', CustomerController::class);
    // Ruta AJAX para verificar dependencias
    Route::get('customers/{customer}/dependencies', [CustomerController::class, 'dependencies'])->name('customers.dependencies');

    // Colores disponibles
    Route::post('available-colors', [AvailableColorController::class, 'store'])->name('available-colors.store');
    Route::delete('available-colors/{id}', [AvailableColorController::class, 'destroy'])->name('available-colors.destroy');
    Route::put('available-colors/order', [AvailableColorController::class, 'updateOrder'])->name('available-colors.update-order');

    // Colores de impresión disponibles
    Route::post('available-print-colors', [AvailablePrintColorController::class, 'store'])->name('available-print-colors.store');
    Route::delete('available-print-colors/{id}', [AvailablePrintColorController::class, 'destroy'])->name('available-print-colors.destroy');
    Route::put('available-print-colors/order', [AvailablePrintColorController::class, 'updateOrder'])->name('available-print-colors.update-order');

    // Tamaños disponibles
    Route::post('available-sizes', [\App\Http\Controllers\Admin\AvailableSizeController::class, 'store'])->name('available-sizes.store');
    Route::delete('available-sizes/{id}', [\App\Http\Controllers\Admin\AvailableSizeController::class, 'destroy'])->name('available-sizes.destroy');
    Route::put('available-sizes/order', [\App\Http\Controllers\Admin\AvailableSizeController::class, 'updateOrder'])->name('available-sizes.update-order');

    // Materiales disponibles
    Route::post('available-materials', [AvailableMaterialController::class, 'store'])->name('available-materials.store');
    Route::delete('available-materials/{id}', [AvailableMaterialController::class, 'destroy'])->name('available-materials.destroy');
    Route::put('available-materials/order', [AvailableMaterialController::class, 'updateOrder'])->name('available-materials.update-order');

    // Sistemas de impresión
    Route::resource('printing-systems', PrintingSystemController::class);
    Route::put('printing-systems/order', [PrintingSystemController::class, 'updateOrder'])->name('printing-systems.update-order');
});

require __DIR__.'/auth.php';