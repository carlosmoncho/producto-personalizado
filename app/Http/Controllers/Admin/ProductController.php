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
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use App\Models\AttributeDependency;
use App\Models\PriceRule;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subcategory', 'pricing', 'printingSystems', 'productAttributes.attributeGroup']);

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

        if ($request->filled('has_attributes')) {
            if ($request->has_attributes == '1') {
                $query->whereHas('productAttributes');
            } elseif ($request->has_attributes == '0') {
                $query->whereDoesntHave('productAttributes');
            }
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

        // Grupos de atributos del configurador (NUEVO SISTEMA)
        $attributeGroups = AttributeGroup::with(['attributes' => function($query) {
            $query->active()->orderBy('sort_order');
        }])
        ->active()
        ->orderBy('sort_order')
        ->get();
        
        // Mantener compatibilidad temporal con sistema antiguo
        $configuratorAttributes = ProductAttribute::active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

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
            'configuratorAttributes',
            'attributeGroups', // NUEVO: grupos de atributos organizados
            'breadcrumbs'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku',
            'colors' => 'nullable|array',
            'colors.*' => 'nullable|string|exists:available_colors,name',
            'materials' => 'nullable|array',
            'materials.*' => 'nullable|string|exists:available_materials,name',
            'sizes' => 'nullable|array',
            'sizes.*' => 'nullable|string',
            'printing_systems' => 'nullable|array',
            'printing_systems.*' => 'nullable|exists:printing_systems,id',
            'face_count' => 'nullable|integer|min:1',
            'print_colors_count' => 'nullable|integer|min:1',
            'print_colors' => 'nullable|array',
            'print_colors.*' => 'nullable|string|exists:available_print_colors,name',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'model_3d' => 'nullable|file|mimetypes:model/gltf-binary,model/gltf+json,application/octet-stream|max:51200', // 50MB max
            'pricing' => 'nullable|array',
            'pricing.*.quantity_from' => 'nullable|integer|min:1',
            'pricing.*.quantity_to' => 'nullable|integer|min:1',
            'pricing.*.price' => 'nullable|numeric|min:0',
            'pricing.*.unit_price' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            // Validaciones del configurador
            'has_configurator' => 'boolean',
            'selected_attributes' => 'nullable|array',
            'selected_attributes.*' => 'nullable|array',
            'selected_attributes.*.*' => 'exists:product_attributes,id',
            'max_print_colors' => 'nullable|integer|min:1|max:10',
            'allow_file_upload' => 'boolean',
            'file_upload_types' => 'nullable|array',
            'configurator_base_price' => 'nullable|numeric|min:0',
            'configurator_description' => 'nullable|string|max:1000',
            'pricing_unit' => 'nullable|in:unit,thousand'
        ]);

        DB::beginTransaction();

        try {
            $productService = new \App\Services\Product\ProductService();
            $fileService = new \App\Services\File\FileUploadService();

            // Generar slug único usando ProductService
            $slug = $productService->generateUniqueSlug($request->name);

            // Preparar datos base del producto
            $productData = $productService->prepareProductData($request, $slug);

            // Manejar imágenes
            if ($request->hasFile('images')) {
                $productData['images'] = $fileService->uploadProductImages(
                    $request->file('images'),
                    'products'
                );
            }

            // Manejar modelo 3D
            if ($request->hasFile('model_3d')) {
                $productData['model_3d_file'] = $fileService->upload3DModel(
                    $request->file('model_3d'),
                    '3d-models',
                    $request->name
                );
            }

            // Crear producto
            $product = Product::create($productData);

            // Sincronizar relaciones usando ProductService
            $productService->syncPrintingSystems($product, $request->printing_systems);
            $productService->syncPricing($product, $request->pricing);

            if ($request->has_configurator && $request->selected_attributes) {
                $productService->syncProductAttributes($product, $request->selected_attributes);
            }

            DB::commit();

            // Invalidar caché de productos (afecta categorías con productos)
            app(\App\Services\Cache\CatalogCacheService::class)->invalidateProductsCache();

            return redirect()->route('admin.products.index')
                            ->with('success', 'Producto creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar archivos subidos si hay error
            if (isset($imagePaths)) {
                foreach ($imagePaths as $path) {
                    Storage::disk(config('filesystems.default', 'public'))->delete($path);
                }
            }
            if (isset($productData['model_3d_file'])) {
                Storage::disk(config('filesystems.default', 'public'))->delete($productData['model_3d_file']);
            }

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear el producto: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'subcategory',
            'pricing',
            'printingSystems',
            'productAttributes.attributeGroup',
            'productAttributes.parentDependencies.dependentAttribute.attributeGroup',
            'productAttributes.childDependencies.parentAttribute.attributeGroup',
            'attributeGroups',
            'productAttributeValues'
        ]);

        // Cargar dependencias de atributos para este producto
        $dependencies = AttributeDependency::whereHas('parentAttribute.products', function($query) use ($product) {
            $query->where('products.id', $product->id);
        })->orWhereHas('dependentAttribute.products', function($query) use ($product) {
            $query->where('products.id', $product->id);
        })->with(['parentAttribute.attributeGroup', 'dependentAttribute.attributeGroup'])
        ->active()
        ->get();

        // Cargar reglas de precio para este producto
        $priceRules = PriceRule::where('product_id', $product->id)
            ->orWhereNull('product_id')
            ->active()
            ->orderBy('priority')
            ->get();

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => $product->name, 'url' => '#']
        ];

        return view('admin.products.show', compact('product', 'breadcrumbs', 'dependencies', 'priceRules'));
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

        // Grupos de atributos para el configurador
        $attributeGroups = AttributeGroup::with(['attributes' => function($query) {
            $query->active()->orderBy('sort_order');
        }])->active()
          ->orderBy('sort_order')
          ->get();

        $product->load(['pricing', 'printingSystems', 'productAttributes', 'productAttributeValues']);

        // Crear array de atributos seleccionados para facilitar el manejo en la vista
        $selectedAttributes = [];
        foreach ($product->productAttributes as $attribute) {
            $groupId = $attribute->attribute_group_id;
            if (!isset($selectedAttributes[$groupId])) {
                $selectedAttributes[$groupId] = [];
            }
            $selectedAttributes[$groupId][] = $attribute->id;
        }

        // Crear array de imágenes por atributo (attribute_id => images array)
        $attributeImages = [];
        foreach ($product->productAttributeValues as $pav) {
            $attributeImages[$pav->product_attribute_id] = [
                'pivot_id' => $pav->id,
                'images' => $pav->images ?? []
            ];
        }

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
            'attributeGroups',
            'selectedAttributes',
            'attributeImages',
            'breadcrumbs'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'colors' => 'nullable|array',
            'colors.*' => 'nullable|string|exists:available_colors,name',
            'materials' => 'nullable|array',
            'materials.*' => 'nullable|string|exists:available_materials,name',
            'sizes' => 'nullable|array',
            'sizes.*' => 'nullable|string',
            'printing_systems' => 'nullable|array',
            'printing_systems.*' => 'nullable|exists:printing_systems,id',
            'face_count' => 'nullable|integer|min:1',
            'print_colors_count' => 'nullable|integer|min:1',
            'print_colors' => 'nullable|array',
            'print_colors.*' => 'nullable|string|exists:available_print_colors,name',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'model_3d' => 'nullable|file|mimetypes:model/gltf-binary,model/gltf+json,application/octet-stream|max:51200', // 50MB max
            'pricing' => 'nullable|array',
            'pricing.*.quantity_from' => 'nullable|integer|min:1',
            'pricing.*.quantity_to' => 'nullable|integer|min:1',
            'pricing.*.price' => 'nullable|numeric|min:0',
            'pricing.*.unit_price' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            // Validaciones del configurador
            'has_configurator' => 'boolean',
            'selected_attributes' => 'nullable|array',
            'selected_attributes.*' => 'nullable|array',
            'selected_attributes.*.*' => 'exists:product_attributes,id',
            'max_print_colors' => 'nullable|integer|min:1|max:10',
            'allow_file_upload' => 'boolean',
            'file_upload_types' => 'nullable|array',
            'configurator_base_price' => 'nullable|numeric|min:0',
            'configurator_description' => 'nullable|string|max:1000',
            'pricing_unit' => 'nullable|in:unit,thousand'
        ]);

        DB::beginTransaction();

        try {
            $productService = new \App\Services\Product\ProductService();
            $fileService = new \App\Services\File\FileUploadService();

            // Generar slug único si ha cambiado el nombre
            $slug = $request->slug;
            if (!$slug || $request->name !== $product->name) {
                $slug = $productService->generateUniqueSlug($request->name, $product->id);
            }

            // Preparar datos base del producto
            $productData = $productService->prepareProductData($request, $slug);

            // Manejar eliminación de imágenes
            $imagePaths = $product->images ?? [];
            if ($request->has('remove_images') && $request->remove_images) {
                $imagesToRemove = explode(',', $request->remove_images);
                foreach ($imagesToRemove as $imageToRemove) {
                    $imageToRemove = trim($imageToRemove);
                    if (($key = array_search($imageToRemove, $imagePaths)) !== false) {
                        $fileService->deleteFile($imageToRemove);
                        unset($imagePaths[$key]);
                    }
                }
                $imagePaths = array_values($imagePaths); // Reindexar array
            }

            // Manejar nuevas imágenes usando FileUploadService
            if ($request->hasFile('images')) {
                $newImages = $fileService->uploadProductImages(
                    $request->file('images'),
                    'products'
                );
                $imagePaths = array_merge($imagePaths, $newImages);
            }

            $productData['images'] = $imagePaths;

            // Manejar eliminación de modelo 3D
            if ($request->input('remove_model_3d') === '1' && $product->model_3d_file) {
                $fileService->deleteFile($product->model_3d_file);
                $productData['model_3d_file'] = null;
            }
            // Manejar modelo 3D usando FileUploadService
            elseif ($request->hasFile('model_3d')) {
                // Eliminar modelo anterior si existe
                if ($product->model_3d_file) {
                    $fileService->deleteFile($product->model_3d_file);
                }

                // Subir nuevo modelo 3D
                $productData['model_3d_file'] = $fileService->upload3DModel(
                    $request->file('model_3d'),
                    '3d-models',
                    $product->name
                );

                // Validar tamaño mínimo (al menos 1KB para un modelo válido)
                if ($request->file('model_3d')->getSize() < 1000) {
                    $fileService->deleteFile($productData['model_3d_file']);
                    throw new \Exception('El archivo 3D es demasiado pequeño. Asegúrese de que es un modelo válido.');
                }
            }

            $product->update($productData);

            // Sincronizar relaciones usando ProductService
            $productService->syncPrintingSystems($product, $request->printing_systems);
            $productService->syncPricing($product, $request->pricing);

            if ($request->has_configurator && $request->selected_attributes) {
                $productService->syncProductAttributes($product, $request->selected_attributes);
            } elseif (!$request->has_configurator) {
                // Si no tiene configurador, eliminar todas las relaciones de atributos
                $productService->syncProductAttributes($product, null);
            }

            DB::commit();

            // Invalidar caché de productos (afecta categorías con productos)
            app(\App\Services\Cache\CatalogCacheService::class)->invalidateProductsCache();

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
            $productService = new \App\Services\Product\ProductService();
            $fileService = new \App\Services\File\FileUploadService();

            // Validar si el producto puede eliminarse usando ProductService
            $validation = $productService->canDelete($product);

            if (!$validation['can_delete']) {
                $message = "No se puede eliminar el producto '{$product->name}' porque está incluido en {$validation['details']['orders_count']} pedido(s):\n\n";
                $message .= "• Pedidos: {$validation['details']['orders_sample']}\n\n";
                $message .= "Los productos que forman parte del historial de pedidos no pueden eliminarse para mantener la integridad de los datos.";

                return redirect()->route('admin.products.index')
                                ->with('error', $message);
            }

            DB::beginTransaction();

            // Eliminar imágenes usando FileUploadService
            if ($product->images && is_array($product->images)) {
                $fileService->deleteFiles($product->images);
            }

            // Eliminar modelo 3D usando FileUploadService
            if ($product->model_3d_file) {
                $fileService->deleteFile($product->model_3d_file);
            }

            // Guardar el nombre del producto para el mensaje
            $productName = $product->name;
            
            $product->delete();

            DB::commit();

            // Invalidar caché de productos (afecta categorías con productos)
            app(\App\Services\Cache\CatalogCacheService::class)->invalidateProductsCache();

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
            Storage::disk(config('filesystems.default', 'public'))->delete($imageToDelete);
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

    /**
     * Mostrar vista para gestionar imágenes por atributo
     */
    public function attributeImages(Product $product)
    {
        $product->load([
            'productAttributeValues.productAttribute.attributeGroup',
            'productAttributeValues.attributeGroup'
        ]);

        // Filtrar y agrupar atributos por grupo (solo los que tienen productAttribute válido)
        $attributesByGroup = $product->productAttributeValues
            ->filter(fn($pav) => $pav->productAttribute !== null)
            ->groupBy(function($pav) {
                return $pav->attributeGroup->name ?? 'Sin grupo';
            });

        $breadcrumbs = [
            ['name' => 'Productos', 'url' => route('admin.products.index')],
            ['name' => $product->name, 'url' => route('admin.products.show', $product)],
            ['name' => 'Imágenes por Atributo', 'url' => '#']
        ];

        return view('admin.products.attribute-images', compact(
            'product',
            'attributesByGroup',
            'breadcrumbs'
        ));
    }

    /**
     * Guardar imágenes para un atributo específico del producto
     */
    public function storeAttributeImages(Request $request, Product $product, $attributeValueId)
    {
        try {
            \Log::info('storeAttributeImages called', [
                'product_id' => $product->id,
                'attributeValueId' => $attributeValueId,
                'has_files' => $request->hasFile('images'),
            ]);

            $request->validate([
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120'
            ]);

            // Buscar directamente en ProductAttributeValue
            $attributeValue = ProductAttributeValue::where('product_id', $product->id)
                ->where('id', $attributeValueId)
                ->firstOrFail();

            \Log::info('Found attributeValue', ['id' => $attributeValue->id, 'current_images' => $attributeValue->images]);

            // Obtener imágenes existentes
            $existingImages = $attributeValue->images ?? [];

            // Subir nuevas imágenes
            $disk = config('filesystems.default', 'public');
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products/attributes/' . $product->id, $disk);
                    $existingImages[] = $path;
                    \Log::info('Imagen guardada', ['path' => $path, 'disk' => $disk]);
                }
            }

            // Actualizar el registro usando save() en lugar de update()
            $attributeValue->images = $existingImages;
            $saved = $attributeValue->save();

            \Log::info('Attribute saved', ['saved' => $saved, 'new_images' => $attributeValue->images]);

            // Convertir paths a URLs completas para el frontend
            $imageUrls = array_map(function($path) use ($disk) {
                if (!$path) return null;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }
                try {
                    return \Storage::disk($disk)->url($path);
                } catch (\Exception $e) {
                    return url('/api/storage/' . $path);
                }
            }, $existingImages);

            return response()->json([
                'success' => true,
                'images' => $imageUrls,
                'message' => 'Imágenes guardadas correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error uploading attribute images: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una imagen específica de un atributo
     */
    public function deleteAttributeImage(Request $request, Product $product, $attributeValueId, $imageIndex)
    {
        try {
            $attributeValue = $product->productAttributeValues()->findOrFail($attributeValueId);

            $images = $attributeValue->images ?? [];

            if (!isset($images[$imageIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imagen no encontrada'
                ], 404);
            }

            // Eliminar archivo físico
            \Storage::disk(config('filesystems.default', 'public'))->delete($images[$imageIndex]);

            // Eliminar del array
            unset($images[$imageIndex]);
            $images = array_values($images); // Reindexar

            // Actualizar el registro
            $attributeValue->update(['images' => $images]);

            // Convertir paths a URLs completas para el frontend
            $disk = config('filesystems.default', 'public');
            $imageUrls = array_map(function($path) use ($disk) {
                if (!$path) return null;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }
                try {
                    return \Storage::disk($disk)->url($path);
                } catch (\Exception $e) {
                    return url('/api/storage/' . $path);
                }
            }, $images);

            return response()->json([
                'success' => true,
                'images' => $imageUrls,
                'message' => 'Imagen eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting attribute image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}