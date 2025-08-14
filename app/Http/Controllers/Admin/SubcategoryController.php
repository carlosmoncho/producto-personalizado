<?php
// app/Http/Controllers/Admin/SubcategoryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::with(['category']);

        // Filtros
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $subcategories = $query->orderBy('sort_order')->paginate(10);
        $categories = Category::where('active', true)->get();

        $breadcrumbs = [
            ['name' => 'Subcategorías', 'url' => route('admin.subcategories.index')]
        ];

        return view('admin.subcategories.index', compact('subcategories', 'categories', 'breadcrumbs'));
    }

    public function create()
    {
        $categories = Category::where('active', true)->orderBy('sort_order')->get();

        $breadcrumbs = [
            ['name' => 'Subcategorías', 'url' => route('admin.subcategories.index')],
            ['name' => 'Crear Subcategoría', 'url' => '#']
        ];

        return view('admin.subcategories.create', compact('categories', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        // Generar slug único
        $slug = $this->generateUniqueSlug($request->name, $request->category_id);

        $subcategoryData = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'active' => $request->boolean('active', true),
            'sort_order' => $request->sort_order ?? 0
        ];

        // Manejar imagen
        if ($request->hasFile('image')) {
            $subcategoryData['image'] = $request->file('image')->store('subcategories', 'public');
        }

        Subcategory::create($subcategoryData);

        return redirect()->route('admin.subcategories.index')
                        ->with('success', 'Subcategoría creada exitosamente.');
    }

    public function show(Subcategory $subcategory)
    {
        $subcategory->load(['category', 'products']);

        $breadcrumbs = [
            ['name' => 'Subcategorías', 'url' => route('admin.subcategories.index')],
            ['name' => $subcategory->name, 'url' => '#']
        ];

        return view('admin.subcategories.show', compact('subcategory', 'breadcrumbs'));
    }

    public function edit(Subcategory $subcategory)
    {
        $categories = Category::where('active', true)->orderBy('sort_order')->get();

        $breadcrumbs = [
            ['name' => 'Subcategorías', 'url' => route('admin.subcategories.index')],
            ['name' => $subcategory->name, 'url' => route('admin.subcategories.show', $subcategory)],
            ['name' => 'Editar', 'url' => '#']
        ];

        return view('admin.subcategories.edit', compact('subcategory', 'categories', 'breadcrumbs'));
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        // Generar slug único solo si el nombre cambió
        $slug = $subcategory->slug;
        if ($request->name !== $subcategory->name) {
            $slug = $this->generateUniqueSlug($request->name, $request->category_id, $subcategory->id);
        }

        $subcategoryData = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'active' => $request->boolean('active', true),
            'sort_order' => $request->sort_order ?? $subcategory->sort_order
        ];

        // Manejar imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior
            $subcategory->deleteImage();
            // Guardar nueva imagen
            $subcategoryData['image'] = $request->file('image')->store('subcategories', 'public');
        }

        $subcategory->update($subcategoryData);

        return redirect()->route('admin.subcategories.index')
                        ->with('success', 'Subcategoría actualizada exitosamente.');
    }

    public function destroy(Subcategory $subcategory)
    {
        // Verificar dependencias
        $productsCount = $subcategory->products()->count();
        
        if ($productsCount > 0) {
            $products = $subcategory->products()->pluck('name')->take(5);
            $productsList = $products->implode(', ');
            
            if ($productsCount > 5) {
                $productsList .= " y " . ($productsCount - 5) . " más";
            }
            
            $message = "No se puede eliminar la subcategoría '{$subcategory->name}' porque tiene {$productsCount} producto(s) asociado(s):\n\n";
            $message .= "• {$productsList}\n\n";
            $message .= "Primero debe eliminar o reasignar estos productos a otra subcategoría.";
            
            return redirect()->route('admin.subcategories.index')
                            ->with('error', $message);
        }

        try {
            $subcategory->deleteImage();
            $subcategory->delete();
            
            return redirect()->route('admin.subcategories.index')
                            ->with('success', "Subcategoría '{$subcategory->name}' eliminada exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('admin.subcategories.index')
                            ->with('error', 'Error al eliminar la subcategoría: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener información de dependencias para AJAX
     */
    public function dependencies(Subcategory $subcategory)
    {
        $products = $subcategory->products()->get(['id', 'name', 'sku']);
        
        return response()->json([
            'can_delete' => $products->count() === 0,
            'products_count' => $products->count(),
            'products' => $products->map(function($product) {
                return [
                    'name' => $product->name,
                    'sku' => $product->sku
                ];
            })
        ]);
    }

    /**
     * Genera un slug único para la subcategoría
     * 
     * @param string $name
     * @param int $categoryId
     * @param int|null $excludeId ID de la subcategoría a excluir (para edición)
     * @return string
     */
    private function generateUniqueSlug($name, $categoryId, $excludeId = null)
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Construir query base
        $query = Subcategory::where('slug', $slug);
        
        // Excluir la subcategoría actual si estamos editando
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Mientras exista el slug, agregar un contador
        while ($query->exists()) {
            // Obtener la categoría para agregar su nombre al slug
            $category = Category::find($categoryId);
            if ($category) {
                $slug = $baseSlug . '-' . Str::slug($category->name);
            } else {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            // Actualizar query con el nuevo slug
            $query = Subcategory::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            // Si aún existe con el nombre de categoría, agregar contador
            if ($query->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
                $query = Subcategory::where('slug', $slug);
                if ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                }
            }
        }

        return $slug;
    }
}