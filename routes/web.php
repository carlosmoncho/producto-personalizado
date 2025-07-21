<?php
// routes/web.php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\AvailableColorController; 
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() 
        ? redirect()->route('admin.dashboard') 
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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
    
    // Categorías
    Route::resource('categories', CategoryController::class);
    
    // Subcategorías
    Route::resource('subcategories', SubcategoryController::class);
    
    // Productos
    Route::resource('products', ProductController::class);
    // Ruta AJAX para obtener subcategorías por categoría
    Route::get('products/subcategories/{category}', [ProductController::class, 'getSubcategories'])->name('products.subcategories');
    
    // Campos personalizados
    Route::resource('custom-fields', CustomFieldController::class);
    
    // Pedidos
    Route::resource('orders', OrderController::class);
    // Rutas adicionales para pedidos
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');

    Route::post('available-colors', [AvailableColorController::class, 'store'])->name('available-colors.store');
    Route::delete('available-colors/{id}', [AvailableColorController::class, 'destroy'])->name('available-colors.destroy');
    Route::put('available-colors/order', [AvailableColorController::class, 'updateOrder'])->name('available-colors.update-order');

    
});

require __DIR__.'/auth.php';