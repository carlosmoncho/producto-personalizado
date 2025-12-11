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
use App\Http\Controllers\Admin\AttributeGroupController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\AttributeDependencyController;
use App\Http\Controllers\Admin\PriceRuleController;
use App\Http\Controllers\ProductConfiguratorController;
use App\Http\Controllers\Admin\DataImportController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

// ============ RUTAS DE DESARROLLO ============
// Las rutas de testing y demos están en routes/dev.php
// Solo se cargan en entorno local (ver bootstrap/app.php)

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

    // Imágenes de atributos del producto
    Route::get('products/{product}/attribute-images', [ProductController::class, 'attributeImages'])->name('products.attribute-images');
    Route::post('products/{product}/attribute-images/{attributeValue}', [ProductController::class, 'storeAttributeImages'])->name('products.attribute-images.store');
    Route::delete('products/{product}/attribute-images/{attributeValue}/{imageIndex}', [ProductController::class, 'deleteAttributeImage'])->name('products.attribute-images.delete');

    // API para búsqueda de productos
    Route::get('api/products/search', [ProductController::class, 'search'])->name('api.products.search');
    
    // Configurador de productos
    Route::get('configurator/{product}', [\App\Http\Controllers\ProductConfiguratorController::class, 'show'])->name('configurator.show');
    
    // APIs del configurador
    Route::prefix('api/configurator')->name('api.configurator.')->group(function () {
        Route::post('attributes', [\App\Http\Controllers\ProductConfiguratorController::class, 'getAvailableAttributes'])->name('attributes');
        Route::post('inks/recommended', [\App\Http\Controllers\ProductConfiguratorController::class, 'getRecommendedInks'])->name('inks.recommended');
        Route::post('price/calculate', [\App\Http\Controllers\ProductConfiguratorController::class, 'calculatePrice'])->name('price.calculate');
        Route::post('configuration/update', [\App\Http\Controllers\ProductConfiguratorController::class, 'updateConfiguration'])->name('configuration.update');
        Route::post('configuration/validate', [\App\Http\Controllers\ProductConfiguratorController::class, 'validateConfiguration'])->name('configuration.validate');
    });
    
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
    
    // Grupos de atributos
    Route::resource('attribute-groups', AttributeGroupController::class);
    Route::post('attribute-groups/reorder', [AttributeGroupController::class, 'reorder'])->name('attribute-groups.reorder');
    Route::post('attribute-groups/{attributeGroup}/add-attribute', [AttributeGroupController::class, 'addAttribute'])->name('attribute-groups.add-attribute');
    
    // Atributos de productos (para configurador)
    // Rutas específicas ANTES del resource para evitar conflictos
    Route::post('product-attributes/{productAttribute}/duplicate', [\App\Http\Controllers\Admin\ProductAttributeController::class, 'duplicate'])->name('product-attributes.duplicate');
    Route::put('product-attributes/order', [\App\Http\Controllers\Admin\ProductAttributeController::class, 'updateOrder'])->name('product-attributes.updateOrder');
    Route::get('api/product-attributes/by-type', [\App\Http\Controllers\Admin\ProductAttributeController::class, 'getByType'])->name('api.product-attributes.by-type');
    Route::resource('product-attributes', ProductAttributeController::class);
    
    // Rutas específicas ANTES del resource para evitar conflictos
    Route::get('attribute-dependencies/create-individual', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'createIndividual'])->name('attribute-dependencies.create-individual');
    Route::post('attribute-dependencies/store-individual', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'storeIndividual'])->name('attribute-dependencies.store-individual');
    Route::get('attribute-dependencies/create-combination', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'createCombination'])->name('attribute-dependencies.create-combination');
    Route::post('attribute-dependencies/store-combination', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'storeCombination'])->name('attribute-dependencies.store-combination');

    // Dependencias de atributos - Resource route DESPUÉS de rutas específicas
    Route::resource('attribute-dependencies', \App\Http\Controllers\Admin\AttributeDependencyController::class);
    Route::post('attribute-dependencies/{attributeDependency}/duplicate', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'duplicate'])->name('attribute-dependencies.duplicate');
    Route::get('api/attribute-dependencies/by-type', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'getAttributesByType'])->name('api.attribute-dependencies.by-type');
    Route::get('api/attribute-dependencies/preview', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'previewDependencies'])->name('api.attribute-dependencies.preview');
    Route::get('api/attribute-dependencies/validate', [\App\Http\Controllers\Admin\AttributeDependencyController::class, 'validateConfiguration'])->name('api.attribute-dependencies.validate');

    // Reglas de precio (compatibilidad temporal)
    Route::resource('price-rules', PriceRuleController::class);
    Route::get('api/price-rules/by-type', [PriceRuleController::class, 'getByType'])->name('api.price-rules.by-type');

    // Importar/Exportar datos
    Route::get('data-import', [DataImportController::class, 'index'])->name('data-import.index');
    Route::get('data-import/export', [DataImportController::class, 'export'])->name('data-import.export');
    Route::post('data-import/import', [DataImportController::class, 'import'])->name('data-import.import');
});

require __DIR__.'/auth.php';