<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeDependency;
use App\Models\ProductConfiguration;
use App\Models\PriceRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * API Controller for Product Configurator
 *
 * Provides endpoints for configuring custom products with attributes,
 * dependencies, dynamic pricing, and validation.
 *
 * @package App\Http\Controllers\Api\V1
 */
class ConfiguratorController extends Controller
{
    /**
     * Get the storage URL for a file path
     * Returns full S3 URL in production, /api/storage/ proxy in local
     */
    protected function getStorageUrl(?string $path): ?string
    {
        if (!$path) return null;

        // Si ya es una URL absoluta, devolverla tal cual
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = config('filesystems.default', 'public');

        // En S3, devolver URL completa de S3
        if ($disk === 's3') {
            return Storage::disk('s3')->url($path);
        }

        // En local, usar el proxy /api/storage/
        return url('/api/storage/' . $path);
    }

    /**
     * Get configurator initial data
     *
     * Returns all necessary data to initialize the configurator for a product:
     * - Product information
     * - Available attributes grouped by type
     * - Attribute dependencies
     * - Pricing rules
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "product": {...},
     *     "attributes_by_type": {...},
     *     "dependencies": [...],
     *     "pricing_info": {...}
     *   }
     * }
     */
    public function getConfig(Product $product)
    {
        if (!$product->has_configurator) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto no tiene configurador habilitado.'
            ], 404);
        }

        // Cargar atributos del producto con sus grupos, ordenados por sort_order del grupo
        $product->load(['productAttributes.attributeGroup', 'pricing', 'productAttributeValues']);

        // Obtener atributos asociados al producto (con pivot data)
        $productAttributes = $product->productAttributes()
            ->with('attributeGroup')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        // Si el producto no tiene atributos específicos, usar todos los atributos globales
        if ($productAttributes->isEmpty()) {
            // Obtener TODOS los atributos activos (sin filtrar por producto)
            $allAttributes = \App\Models\ProductAttribute::with('attributeGroup')
                ->active()
                ->orderBy('sort_order')
                ->get();

            // Obtener grupos de estos atributos
            $attributeGroups = \App\Models\AttributeGroup::whereIn('id', $allAttributes->pluck('attribute_group_id')->unique())
                ->active()
                ->orderBy('sort_order')
                ->get();

            // Agrupar por tipo (todos no disponibles)
            $attributesByType = collect();
            foreach ($attributeGroups as $group) {
                $groupAttributes = $allAttributes->where('attribute_group_id', $group->id);
                if ($groupAttributes->count() > 0) {
                    $attributesByType->put($group->type, $groupAttributes->values());
                }
            }
        } else {
            // SOLO obtener atributos que están asignados al producto
            // No mostrar todos los atributos globales, solo los del producto

            // Obtener grupos únicos de los atributos del producto
            $attributeGroups = \App\Models\AttributeGroup::whereIn('id', $productAttributes->pluck('attribute_group_id')->unique())
                ->active()
                ->orderBy('sort_order')
                ->get();

            // Crear mapa de imágenes por attribute_id desde productAttributeValues
            $imagesMap = $product->productAttributeValues->pluck('images', 'product_attribute_id')->toArray();

            // Agrupar atributos del producto por tipo
            $attributesByType = collect();
            foreach ($attributeGroups as $group) {
                $groupAttributes = $productAttributes->where('attribute_group_id', $group->id)->map(function($attr) use ($imagesMap) {
                    // Solo incluir datos del pivot
                    $attr->is_available_for_product = true; // Por definición, si está en productAttributes, está disponible
                    $attr->pivot_is_available = $attr->pivot->is_available ?? true;
                    $attr->pivot_is_default = $attr->pivot->is_default ?? false;
                    $attr->pivot_price_modifier = $attr->pivot->custom_price_modifier ?? 0;
                    $attr->pivot_images = $imagesMap[$attr->id] ?? [];

                    return $attr;
                });

                if ($groupAttributes->count() > 0) {
                    $attributesByType->put($group->type, $groupAttributes->values());
                }
            }
        }

        // Obtener dependencias del producto
        $dependencies = AttributeDependency::where(function($query) use ($product) {
                $query->where('product_id', $product->id)
                      ->orWhere('product_id', null);
            })
            ->active()
            ->with(['parentAttribute', 'dependentAttribute', 'thirdAttribute'])
            ->orderBy('priority', 'desc')
            ->get();

        // Obtener rangos de precios
        $pricingRanges = $product->pricing()
            ->orderBy('quantity_from')
            ->get(['quantity_from', 'quantity_to', 'price', 'unit_price']);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'model_3d_url' => $product->model_3d_file ? $this->getStorageUrl($product->model_3d_file) : null,
                    'configurator_base_price' => (float) $product->configurator_base_price,
                    'max_print_colors' => $product->max_print_colors,
                    'allow_file_upload' => (bool) $product->allow_file_upload,
                    // Información de unidad de precio
                    'pricing_unit' => $product->pricing_unit ?? 'unit',
                    'pricing_unit_quantity' => $product->getPricingUnitQuantity(),
                    'pricing_unit_label' => $product->getPricingUnitLabel(),
                ],
                'attributes_by_type' => $attributesByType->mapWithKeys(function ($attributes, $type) {
                    return [$type => $attributes->map(function ($attr) {
                        // Convertir imágenes a URLs completas
                        $images = $attr->pivot_images ?? [];
                        $imageUrls = [];
                        if (!empty($images) && is_array($images)) {
                            foreach ($images as $img) {
                                $imageUrls[] = $this->getStorageUrl($img);
                            }
                        }

                        return [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'value' => $attr->value,
                            'hex_code' => $attr->hex_code,
                            'description' => $attr->description,
                            // is_available indica si este atributo está disponible para este producto
                            'is_available' => (bool) ($attr->pivot_is_available ?? $attr->is_available_for_product ?? false),
                            'is_default' => (bool) ($attr->pivot_is_default ?? false),
                            'price_modifier' => (float) ($attr->pivot_price_modifier ?? 0),
                            'sort_order' => $attr->sort_order,
                            'images' => $imageUrls,
                        ];
                    })->values()];
                }),
                'attribute_groups' => $attributeGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'type' => $group->type,
                        'sort_order' => $group->sort_order,
                        'is_required' => (bool) $group->is_required,
                        'allow_multiple' => (bool) $group->allow_multiple,
                        'affects_price' => (bool) $group->affects_price,
                    ];
                })->values(),
                'dependencies' => $dependencies->map(function ($dep) {
                    return [
                        'id' => $dep->id,
                        'parent_attribute_id' => $dep->parent_attribute_id,
                        'dependent_attribute_id' => $dep->dependent_attribute_id,
                        'third_attribute_id' => $dep->third_attribute_id,
                        'condition_type' => $dep->condition_type,
                        'price_impact' => (float) ($dep->price_impact ?? 0),
                        'price_modifier' => (float) ($dep->price_modifier ?? 0),
                        'auto_select' => (bool) $dep->auto_select,
                        'reset_dependents' => (bool) $dep->reset_dependents,
                        'priority' => $dep->priority,
                    ];
                })->values(),
                'pricing_info' => [
                    'base_price' => (float) $product->configurator_base_price,
                    'pricing_ranges' => $pricingRanges,
                    'max_print_colors' => $product->max_print_colors,
                    // Información de unidad de precio
                    'pricing_unit' => $product->pricing_unit ?? 'unit',
                    'pricing_unit_quantity' => $product->getPricingUnitQuantity(),
                    'pricing_unit_label' => $product->getPricingUnitLabel(),
                ],
            ]
        ]);
    }

    /**
     * Get available attributes based on current selection
     *
     * Returns filtered attributes based on:
     * - Current selection
     * - Attribute dependencies
     * - Compatibility rules
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam type string required Attribute type (color, material, size, etc.)
     * @bodyParam selection array Current selection of attributes
     */
    public function getAvailableAttributes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:color,material,size,ink,system,quantity',
            'selection' => 'array',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->input('type');
        $currentSelection = $request->input('selection', []);
        $productId = $request->input('product_id');

        // Obtener atributos disponibles para el tipo
        $attributes = ProductAttribute::byType($type)
            ->active()
            ->where(function($query) use ($productId) {
                $query->whereHas('products', function($q) use ($productId) {
                    $q->where('products.id', $productId)
                      ->where('product_attribute_values.is_available', true);
                })
                ->orWhere(function($q) use ($productId) {
                    $q->whereDoesntHave('products');
                });
            })
            ->orderBy('sort_order')
            ->get();

        // Aplicar filtros de compatibilidad y dependencias
        $attributes = $attributes->filter(function ($attribute) use ($currentSelection) {
            return $attribute->isCompatibleWith($currentSelection);
        });

        return response()->json([
            'success' => true,
            'data' => AttributeResource::collection($attributes),
        ]);
    }

    /**
     * Calculate dynamic price
     *
     * Calculates the total price based on:
     * - Base price
     * - Selected attributes
     * - Attribute dependencies
     * - Price rules
     * - Volume discounts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam product_id integer required Product ID
     * @bodyParam selection array required Selected attribute IDs
     * @bodyParam quantity integer Quantity (default: 1)
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'selection' => 'required|array',
            'quantity' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productId = $request->input('product_id');
        $selection = $request->input('selection', []);
        $quantity = $request->input('quantity', 1);

        $product = Product::findOrFail($productId);

        // Obtener IDs de atributos seleccionados
        $selectedAttributeIds = is_array($selection) ? array_values($selection) : [];

        // Usar PricingService para calcular precio
        $pricingService = new \App\Services\Pricing\PricingService();
        $data = $pricingService->calculateProductPrice($product, $selectedAttributeIds, $quantity);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Validate configuration
     *
     * Validates that the configuration meets all requirements:
     * - Required attributes selected
     * - Dependency rules met
     * - Business rules satisfied
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam product_id integer required Product ID
     * @bodyParam selection array required Selected attribute IDs
     */
    public function validateConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'selection' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productId = $request->input('product_id');
        $selection = $request->input('selection', []);

        $product = Product::findOrFail($productId);

        // Validar dependencias
        $errors = [];

        if (method_exists(AttributeDependency::class, 'validateSelection')) {
            $dependencyErrors = AttributeDependency::validateSelection($selection, $productId);
            $errors = array_merge($errors, $dependencyErrors);
        }

        // Validar atributos requeridos
        $requiredGroups = \App\Models\AttributeGroup::where('is_required', true)
            ->active()
            ->get();

        foreach ($requiredGroups as $group) {
            $hasSelection = false;
            foreach ($selection as $attributeId) {
                $attribute = ProductAttribute::find($attributeId);
                if ($attribute && $attribute->attribute_group_id == $group->id) {
                    $hasSelection = true;
                    break;
                }
            }

            if (!$hasSelection) {
                $errors[] = "Debe seleccionar un valor para: {$group->name}";
            }
        }

        $isValid = empty($errors);

        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $isValid,
                'errors' => $errors,
            ]
        ]);
    }

    /**
     * Save configuration
     *
     * Saves the configuration in the database for later retrieval
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam product_id integer required Product ID
     * @bodyParam selection array required Selected attribute IDs
     * @bodyParam quantity integer Quantity
     */
    public function saveConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'selection' => 'required|array',
            'quantity' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        $configuration = ProductConfiguration::updateOrCreate(
            [
                'session_id' => $sessionId,
                'product_id' => $request->product_id,
                'user_id' => $userId,
            ],
            [
                'attributes_base' => $request->selection,
                'status' => 'draft',
                'expires_at' => now()->addDays(7),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => new ConfigurationResource($configuration),
        ], 201);
    }

    /**
     * Get saved configuration
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfiguration(Request $request)
    {
        $sessionId = $request->session()->getId();
        $productId = $request->input('product_id');

        if (!$productId) {
            return response()->json([
                'success' => false,
                'message' => 'product_id es requerido'
            ], 422);
        }

        $configuration = ProductConfiguration::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();

        if (!$configuration) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró configuración para esta sesión'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ConfigurationResource($configuration),
        ]);
    }

    /**
     * Get recommended inks based on color contrast
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecommendedInks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'color_hex' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'material_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $colorHex = $request->input('color_hex');
        $materialType = $request->input('material_type');

        if (method_exists(ProductAttribute::class, 'getRecommendedInks')) {
            $inks = ProductAttribute::getRecommendedInks($colorHex, $materialType);
        } else {
            $inks = [];
        }

        // Usar PricingService para obtener información de contraste
        $pricingService = new \App\Services\Pricing\PricingService();
        $contrastInfo = $pricingService->getContrastInfo($colorHex);

        return response()->json([
            'success' => true,
            'data' => [
                'recommended_inks' => AttributeResource::collection($inks),
                'contrast_info' => $contrastInfo,
            ]
        ]);
    }

}
