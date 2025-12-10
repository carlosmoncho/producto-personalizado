<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['subcategories', 'products'])
            ->orderBy('sort_order')
            ->paginate(10);

        $breadcrumbs = [
            ['name' => 'Categorías', 'url' => route('admin.categories.index')]
        ];

        return view('admin.categories.index', compact('categories', 'breadcrumbs'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['name' => 'Categorías', 'url' => route('admin.categories.index')],
            ['name' => 'Crear Categoría', 'url' => '#']
        ];

        return view('admin.categories.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $categoryData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'active' => $request->boolean('active', true),
            'sort_order' => $request->sort_order ?? 0
        ];

        // Manejar imagen
        if ($request->hasFile('image')) {
            $categoryData['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($categoryData);

        // Invalidar caché de categorías
        app(\App\Services\Cache\CatalogCacheService::class)->invalidateCategoriesCache();

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Categoría creada exitosamente.');
    }

    public function show(Category $category)
    {
        $category->load(['subcategories', 'products']);

        $breadcrumbs = [
            ['name' => 'Categorías', 'url' => route('admin.categories.index')],
            ['name' => $category->name, 'url' => '#']
        ];

        return view('admin.categories.show', compact('category', 'breadcrumbs'));
    }

    public function edit(Category $category)
    {
        $breadcrumbs = [
            ['name' => 'Categorías', 'url' => route('admin.categories.index')],
            ['name' => $category->name, 'url' => route('admin.categories.show', $category)],
            ['name' => 'Editar', 'url' => '#']
        ];

        return view('admin.categories.edit', compact('category', 'breadcrumbs'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $categoryData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'active' => $request->boolean('active', true),
            'sort_order' => $request->sort_order ?? $category->sort_order
        ];

        // Manejar imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior
            $category->deleteImage();
            // Guardar nueva imagen
            $categoryData['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($categoryData);

        // Invalidar caché de categorías
        app(\App\Services\Cache\CatalogCacheService::class)->invalidateCategoriesCache();

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Category $category)
    {
        // Verificar dependencias
        $subcategoriesCount = $category->subcategories()->count();
        $productsCount = $category->products()->count();
        
        if ($subcategoriesCount > 0 || $productsCount > 0) {
            $message = "No se puede eliminar la categoría '{$category->name}' porque tiene dependencias:";
            
            if ($subcategoriesCount > 0) {
                $message .= "\n• {$subcategoriesCount} subcategoría(s)";
            }
            
            if ($productsCount > 0) {
                $message .= "\n• {$productsCount} producto(s)";
            }
            
            $message .= "\n\nPrimero debe eliminar o reasignar estos elementos.";
            
            return redirect()->route('admin.categories.index')
                            ->with('error', $message);
        }

        try {
            $category->deleteImage();
            $category->delete();

            // Invalidar caché de categorías
            app(\App\Services\Cache\CatalogCacheService::class)->invalidateCategoriesCache();

            return redirect()->route('admin.categories.index')
                            ->with('success', "Categoría '{$category->name}' eliminada exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('admin.categories.index')
                            ->with('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener información de dependencias para AJAX
     */
    public function dependencies(Category $category)
    {
        $subcategories = $category->subcategories()->with('products')->get();
        $directProducts = $category->products()->count();
        
        $subcategoryInfo = $subcategories->map(function($subcategory) {
            return [
                'name' => $subcategory->name,
                'products_count' => $subcategory->products()->count()
            ];
        });
        
        return response()->json([
            'can_delete' => $subcategories->count() === 0 && $directProducts === 0,
            'subcategories_count' => $subcategories->count(),
            'products_count' => $directProducts,
            'subcategories' => $subcategoryInfo,
            'total_products' => $subcategories->sum(function($sub) { return $sub->products()->count(); }) + $directProducts
        ]);
    }
}
