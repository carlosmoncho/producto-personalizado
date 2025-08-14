<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\PrintingSystem;
use App\Models\AvailableColor;
use App\Models\AvailableMaterial;
use App\Models\AvailableSize;
use App\Models\AvailablePrintColor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'pricing', 'printingSystems']);

        // Filtros
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('subcategory_id') && $request->subcategory_id) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->has('search') && $request->search) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                // Búsqueda insensible a mayúsculas/minúsculas
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            });
        }

        if ($request->filled('status')) {
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
        $printingSystems = PrintingSystem::where('active', true)->orderBy('sort_order')->get();
        $availableColors = AvailableColor::where('active', true)->orderBy('sort_order')->get();
        $availableMaterials = AvailableMaterial::where('active', true)->orderBy('sort_order')->get();
        $availableSizes = AvailableSize::where('active', true)->orderBy('sort_order')->get();
        $availablePrintColors = AvailablePrintColor::where('active', true)->orderBy('sort_order')->get();

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => 'Crear Producto', 'url' => '#']
        ];

        return view('admin.products.create', compact(
            'categories',
            'subcategories',
            'printingSystems',
            'availableColors',
            'availableMaterials',
            'availableSizes',
            'availablePrintColors',
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
            'materials' => 'required|array|min:1',
            'materials.*' => 'required|string|exists:available_materials,name',
            'sizes' => 'required|array',
            'sizes.*' => 'required|string',
            'printing_systems' => 'required|array|min:1',
            'printing_systems.*' => 'required|exists:printing_systems,id',
            'face_count' => 'required|integer|min:1',
            'print_colors_count' => 'required|integer|min:1',
            'print_colors' => 'required|array',
            'print_colors.*' => 'required|string|exists:available_print_colors,name',
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

        DB::beginTransaction();

        try {
            $productData = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'sku' => $request->sku,
                'colors' => $request->colors,
                'materials' => $request->materials,
                'sizes' => $request->sizes,
                'face_count' => $request->face_count,
                'print_colors_count' => $request->print_colors_count,
                'print_colors' => $request->print_colors,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'active' => $request->boolean('active', true),
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

            // Asociar sistemas de impresión
            $product->printingSystems()->attach($request->printing_systems);

            // Guardar precios
            foreach ($request->pricing as $priceData) {
                $product->pricing()->create($priceData);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                            ->with('success', 'Producto creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar archivos subidos si hay error
            if (isset($imagePaths)) {
                foreach ($imagePaths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
            if (isset($productData['model_3d_file'])) {
                Storage::disk('public')->delete($productData['model_3d_file']);
            }

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load(['category', 'subcategory', 'pricing', 'printingSystems']);

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
        $printingSystems = PrintingSystem::where('active', true)->orderBy('sort_order')->get();
        $availableColors = AvailableColor::where('active', true)->orderBy('sort_order')->get();
        $availableMaterials = AvailableMaterial::where('active', true)->orderBy('sort_order')->get();
        $availableSizes = AvailableSize::where('active', true)->orderBy('sort_order')->get();
        $availablePrintColors = AvailablePrintColor::where('active', true)->orderBy('sort_order')->get();

        $product->load(['pricing', 'printingSystems']);

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => $product->name, 'url' => route('admin.products.show', $product)],
            ['name' => 'Editar', 'url' => '#']
        ];

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'subcategories',
            'printingSystems',
            'availableColors',
            'availableMaterials',
            'availableSizes',
            'availablePrintColors',
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
            'materials' => 'required|array|min:1',
            'materials.*' => 'required|string|exists:available_materials,name',
            'sizes' => 'required|array',
            'sizes.*' => 'required|string',
            'printing_systems' => 'required|array|min:1',
            'printing_systems.*' => 'required|exists:printing_systems,id',
            'face_count' => 'required|integer|min:1',
            'print_colors_count' => 'required|integer|min:1',
            'print_colors' => 'required|array',
            'print_colors.*' => 'required|string|exists:available_print_colors,name',
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

        DB::beginTransaction();

        try {
            $productData = [
                'name' => $request->name,
                'slug' => $request->slug ?? Str::slug($request->name),
                'description' => $request->description,
                'sku' => $request->sku,
                'colors' => $request->colors,
                'materials' => $request->materials,
                'sizes' => $request->sizes,
                'face_count' => $request->face_count,
                'print_colors_count' => $request->print_colors_count,
                'print_colors' => $request->print_colors,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'active' => $request->boolean('active', true),
            ];

            // Manejar eliminación de imágenes
            $imagePaths = $product->images ?? [];
            if ($request->has('remove_images') && $request->remove_images) {
                $imagesToRemove = explode(',', $request->remove_images);
                foreach ($imagesToRemove as $imageToRemove) {
                    $imageToRemove = trim($imageToRemove);
                    if (($key = array_search($imageToRemove, $imagePaths)) !== false) {
                        Storage::disk('public')->delete($imageToRemove);
                        unset($imagePaths[$key]);
                    }
                }
                $imagePaths = array_values($imagePaths); // Reindexar array
            }

            // Manejar nuevas imágenes
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('products', 'public');
                }
            }
            
            $productData['images'] = $imagePaths;

            // Manejar modelo 3D
            if ($request->hasFile('model_3d')) {
                // Eliminar modelo anterior si existe
                if ($product->model_3d_file) {
                    Storage::disk('public')->delete($product->model_3d_file);
                }
                $productData['model_3d_file'] = $request->file('model_3d')->store('3d-models', 'public');
            }

            $product->update($productData);

            // Actualizar sistemas de impresión (sync reemplaza las relaciones existentes)
            $product->printingSystems()->sync($request->printing_systems);

            // Actualizar precios
            $product->pricing()->delete();
            foreach ($request->pricing as $priceData) {
                $product->pricing()->create($priceData);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                            ->with('success', 'Producto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Verificar dependencias - revisar si el producto está en pedidos
            $orderItemsCount = $product->orderItems()->count();
            
            if ($orderItemsCount > 0) {
                $orderNumbers = $product->orderItems()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->pluck('orders.order_number')
                    ->unique();
                    
                $ordersCount = $orderNumbers->count();
                $ordersList = $orderNumbers->take(5)->implode(', ');
                
                if ($ordersCount > 5) {
                    $ordersList .= " y " . ($ordersCount - 5) . " más";
                }
                
                $message = "No se puede eliminar el producto '{$product->name}' porque está incluido en {$ordersCount} pedido(s):\n\n";
                $message .= "• Pedidos: {$ordersList}\n\n";
                $message .= "Los productos que forman parte del historial de pedidos no pueden eliminarse para mantener la integridad de los datos.";
                
                return redirect()->route('admin.products.index')
                                ->with('error', $message);
            }

            DB::beginTransaction();

            // Eliminar imágenes
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            // Eliminar modelo 3D
            if ($product->model_3d_file) {
                Storage::disk('public')->delete($product->model_3d_file);
            }

            // Guardar el nombre del producto para el mensaje
            $productName = $product->name;
            
            $product->delete();

            DB::commit();

            return redirect()->route('admin.products.index')
                            ->with('success', "Producto '{$productName}' eliminado exitosamente.");

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            return redirect()->route('admin.products.index')
                            ->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    public function getSubcategories(Category $category)
    {
        $subcategories = $category->subcategories()
                                  ->where('active', true)
                                  ->orderBy('sort_order')
                                  ->get();

        return response()->json($subcategories);
    }

    public function deleteImage(Product $product, Request $request)
    {
        $request->validate([
            'image' => 'required|string'
        ]);

        $images = $product->images ?? [];
        $imageToDelete = $request->image;

        // Buscar y eliminar la imagen
        $key = array_search($imageToDelete, $images);
        if ($key !== false) {
            Storage::disk('public')->delete($imageToDelete);
            unset($images[$key]);
            $product->images = array_values($images); // Reindexar el array
            $product->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function toggleStatus(Product $product)
    {
        $product->active = !$product->active;
        $product->save();

        return response()->json([
            'success' => true,
            'active' => $product->active
        ]);
    }
    
    /**
     * Obtener información de dependencias para AJAX
     */
    public function dependencies(Product $product)
    {
        try {
            $orderItemsCount = $product->orderItems()->count();
            
            if ($orderItemsCount > 0) {
                $orderNumbers = $product->orderItems()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->pluck('orders.order_number')
                    ->unique();
            } else {
                $orderNumbers = collect([]);
            }
            
            return response()->json([
                'can_delete' => $orderItemsCount === 0,
                'order_items_count' => $orderItemsCount,
                'orders' => $orderNumbers->map(function($orderNumber) {
                    return [
                        'order_number' => $orderNumber
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'can_delete' => true,
                'order_items_count' => 0,
                'orders' => [],
                'error' => 'Could not check dependencies'
            ]);
        }
    }
    
    /**
     * Buscar productos para API (usado en crear pedidos)
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('q', '');
        
        if (strlen($searchTerm) < 2) {
            return response()->json([]);
        }
        
        $products = Product::where('active', true)
            ->where(function($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                      ->orWhereRaw('LOWER(sku) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->with('pricing')
            ->limit(10)
            ->get();
        
        return response()->json($products->map(function($product) {
            $minPrice = $product->pricing->count() > 0 
                ? $product->pricing->min('unit_price') 
                : 10.00;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => Str::limit($product->description, 100),
                'price' => $minPrice,
                'image' => $product->getFirstImageUrl()
            ];
        }));
    }
}