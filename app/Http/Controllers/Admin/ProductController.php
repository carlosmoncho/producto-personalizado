<?php
// app/Http/Controllers/Admin/ProductController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'pricing']);

        // Filtros
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('subcategory_id') && $request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('active', $request->status == 'active');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        // Para los filtros
        $categories = Category::where('active', true)->get();
        $subcategories = Subcategory::where('active', true)->get();

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')]
        ];

        return view('admin.products.index', compact(
            'products', 
            'categories', 
            'subcategories',
            'breadcrumbs'
        ));
    }

    public function create()
    {
        $categories = Category::where('active', true)->orderBy('sort_order')->get();
        $subcategories = Subcategory::where('active', true)->orderBy('sort_order')->get();
        $customFields = CustomField::where('active', true)->orderBy('sort_order')->get();

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => 'Crear Producto', 'url' => '#']
        ];

        return view('admin.products.create', compact(
            'categories',
            'subcategories',
            'customFields',
            'breadcrumbs'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku',
            'colors' => 'required|array|min:1',
            'colors.*' => 'required|string|exists:available_colors,name',
            'material' => 'required|string|max:255',
            'sizes' => 'required|array',
            'sizes.*' => 'required|string',
            'printing_system' => 'required|string|max:255',
            'face_count' => 'required|integer|min:1',
            'print_colors_count' => 'required|integer|min:1',
            'print_colors' => 'required|array',
            'print_colors.*' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'model_3d' => 'nullable|file|mimes:glb,gltf|max:10240',
            'pricing' => 'required|array|min:1',
            'pricing.*.quantity_from' => 'required|integer|min:1',
            'pricing.*.quantity_to' => 'required|integer|min:1',
            'pricing.*.price' => 'required|numeric|min:0',
            'pricing.*.unit_price' => 'required|numeric|min:0',
            'active' => 'boolean'
        ]);

        $productData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sku' => $request->sku,
            'colors' => $request->colors,
            'material' => $request->material,
            'sizes' => $request->sizes,
            'printing_system' => $request->printing_system,
            'face_count' => $request->face_count,
            'print_colors_count' => $request->print_colors_count,
            'print_colors' => $request->print_colors,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'active' => $request->boolean('active', true),
            'custom_fields' => $request->custom_fields ?? []
        ];

        // Manejar imágenes
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
            $productData['images'] = $imagePaths;
        }

        // Manejar modelo 3D
        if ($request->hasFile('model_3d')) {
            $productData['model_3d_file'] = $request->file('model_3d')->store('3d-models', 'public');
        }

        $product = Product::create($productData);

        // Guardar precios
        foreach ($request->pricing as $priceData) {
            $product->pricing()->create($priceData);
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'Producto creado exitosamente.');
    }


    public function show(Product $product)
    {
        $product->load(['category', 'subcategory', 'pricing', 'customFields']);

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => $product->name, 'url' => '#']
        ];

        return view('admin.products.show', compact('product', 'breadcrumbs'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('active', true)->orderBy('sort_order')->get();
        $subcategories = Subcategory::where('active', true)->orderBy('sort_order')->get();
        $customFields = CustomField::where('active', true)->orderBy('sort_order')->get();

        $product->load(['pricing']);

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => $product->name, 'url' => route('admin.products.show', $product)],
            ['name' => 'Editar', 'url' => '#']
        ];

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'subcategories',
            'customFields',
            'breadcrumbs'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'colors' => 'required|array|min:1',
            'colors.*' => 'required|string|exists:available_colors,name',
            'material' => 'required|string|max:255',
            'sizes' => 'required|array',
            'sizes.*' => 'required|string',
            'printing_system' => 'required|string|max:255',
            'face_count' => 'required|integer|min:1',
            'print_colors_count' => 'required|integer|min:1',
            'print_colors' => 'required|array',
            'print_colors.*' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'model_3d' => 'nullable|file|mimes:glb,gltf|max:10240',
            'pricing' => 'required|array|min:1',
            'pricing.*.quantity_from' => 'required|integer|min:1',
            'pricing.*.quantity_to' => 'required|integer|min:1',
            'pricing.*.price' => 'required|numeric|min:0',
            'pricing.*.unit_price' => 'required|numeric|min:0',
            'active' => 'boolean'
        ]);
    
        $productData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sku' => $request->sku,
            'colors' => $request->colors,
            'material' => $request->material,
            'sizes' => $request->sizes,
            'printing_system' => $request->printing_system,
            'face_count' => $request->face_count,
            'print_colors_count' => $request->print_colors_count,
            'print_colors' => $request->print_colors,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'active' => $request->boolean('active', true),
            'custom_fields' => $request->custom_fields ?? $product->custom_fields
        ];
    
        // Manejar imágenes
        if ($request->hasFile('images')) {
            // Eliminar imágenes anteriores
            $product->deleteImages();
            // Guardar nuevas imágenes
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
            $productData['images'] = $imagePaths;
        }
    
        // Manejar modelo 3D
        if ($request->hasFile('model_3d')) {
            // Eliminar modelo 3D anterior
            $product->deleteModel3D();
            // Guardar nuevo modelo 3D
            $productData['model_3d_file'] = $request->file('model_3d')->store('3d-models', 'public');
        }
    
        $product->update($productData);
    
        // Actualizar precios
        $product->pricing()->delete();
        foreach ($request->pricing as $priceData) {
            $product->pricing()->create($priceData);
        }
    
        return redirect()->route('admin.products.index')
                        ->with('success', 'Producto actualizado exitosamente.');
    }
    

    public function destroy(Product $product)
    {
        try {
            $product->deleteImages();
            $product->deleteModel3D();
            $product->delete();
            return redirect()->route('admin.products.index')
                            ->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.index')
                            ->with('error', 'No se puede eliminar el producto porque tiene pedidos asociados.');
        }
    }

    // Método AJAX para obtener subcategorías por categoría
    public function getSubcategories(Request $request)
    {
        $subcategories = Subcategory::where('category_id', $request->category_id)
                                    ->where('active', true)
                                    ->orderBy('sort_order')
                                    ->get();

        return response()->json($subcategories);
    }
}