<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::with('category');

        // Filtro por categoría
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $subcategories = $query->orderBy('sort_order')->paginate(10);
        $categories = Category::where('active', true)->orderBy('sort_order')->get();

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
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $subcategoryData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
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
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $subcategoryData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
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
        try {
            $subcategory->deleteImage();
            $subcategory->delete();
            return redirect()->route('admin.subcategories.index')
                            ->with('success', 'Subcategoría eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.subcategories.index')
                            ->with('error', 'No se puede eliminar la subcategoría porque tiene productos asociados.');
        }
    }
}
