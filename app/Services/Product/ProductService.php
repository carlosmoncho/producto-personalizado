<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Servicio de lógica de negocio para productos
 *
 * Centraliza operaciones comunes como:
 * - Generación de slugs únicos
 * - Sincronización de relaciones (pricing, sistemas, atributos)
 * - Validaciones de negocio
 *
 * @package App\Services\Product
 */
class ProductService
{
    /**
     * Generar slug único para un producto
     *
     * Si el slug ya existe, agrega un contador: producto-1, producto-2, etc.
     *
     * @param string $name Nombre del producto
     * @param int|null $excludeId ID de producto a excluir (útil para updates)
     * @return string Slug único
     */
    public function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Verificar si existe, excluyendo el producto actual si es update
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si un slug existe en la base de datos
     *
     * @param string $slug
     * @param int|null $excludeId ID a excluir de la búsqueda
     * @return bool
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Product::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Sincronizar sistemas de impresión de un producto
     *
     * @param Product $product Producto a actualizar
     * @param array|null $systemIds Array de IDs de sistemas o null
     * @return int Cantidad de sistemas asociados
     */
    public function syncPrintingSystems(Product $product, ?array $systemIds): int
    {
        if ($systemIds && count($systemIds) > 0) {
            // Sincronizar con los IDs proporcionados
            $product->printingSystems()->sync($systemIds);
            return count($systemIds);
        } else {
            // Si no hay sistemas, desasociar todos
            $product->printingSystems()->sync([]);
            return 0;
        }
    }

    /**
     * Sincronizar rangos de precio de un producto
     *
     * Elimina todos los rangos existentes y crea los nuevos
     *
     * @param Product $product Producto a actualizar
     * @param array|null $pricingData Array de datos de pricing o null
     * @return int Cantidad de rangos creados
     */
    public function syncPricing(Product $product, ?array $pricingData): int
    {
        // Eliminar todos los rangos de precio existentes
        $product->pricing()->delete();

        $created = 0;

        // Crear nuevos rangos si existen
        if ($pricingData && is_array($pricingData)) {
            foreach ($pricingData as $priceData) {
                // Validar que tenga los campos necesarios
                if (isset($priceData['quantity_from'], $priceData['quantity_to'], $priceData['unit_price'])) {
                    $product->pricing()->create($priceData);
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Sincronizar atributos del configurador de un producto
     *
     * Preserva las imágenes existentes en product_attribute_values
     *
     * @param Product $product Producto a actualizar
     * @param array|null $selectedAttributes Array de atributos seleccionados [groupId => [attributeIds]]
     * @return int Cantidad de atributos asociados
     */
    public function syncProductAttributes(Product $product, ?array $selectedAttributes): int
    {
        // Obtener registros existentes con sus imágenes
        $existingValues = $product->productAttributeValues()
            ->get()
            ->keyBy('product_attribute_id');

        // Construir lista plana de attribute IDs seleccionados con sus grupos
        $newAttributesMap = []; // [attributeId => groupId]
        if ($selectedAttributes && is_array($selectedAttributes)) {
            foreach ($selectedAttributes as $groupId => $attributeIds) {
                if (is_array($attributeIds)) {
                    foreach ($attributeIds as $attributeId) {
                        $newAttributesMap[$attributeId] = $groupId;
                    }
                }
            }
        }

        // Eliminar atributos que ya no están seleccionados
        $toDelete = $existingValues->keys()->diff(array_keys($newAttributesMap));
        if ($toDelete->isNotEmpty()) {
            $product->productAttributeValues()
                ->whereIn('product_attribute_id', $toDelete)
                ->delete();
        }

        $attached = 0;

        // Crear o actualizar atributos seleccionados
        foreach ($newAttributesMap as $attributeId => $groupId) {
            $existing = $existingValues->get($attributeId);

            if ($existing) {
                // Ya existe, actualizar grupo si es necesario (preservar imágenes)
                if ($existing->attribute_group_id != $groupId) {
                    $existing->update(['attribute_group_id' => $groupId]);
                }
            } else {
                // Crear nuevo registro
                $product->productAttributeValues()->create([
                    'product_attribute_id' => $attributeId,
                    'attribute_group_id' => $groupId,
                    'is_available' => true,
                    'is_default' => false
                ]);
            }
            $attached++;
        }

        return $attached;
    }

    /**
     * Preparar datos base de un producto desde el request
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug Slug generado
     * @return array Datos preparados para crear/actualizar
     */
    public function prepareProductData($request, string $slug): array
    {
        return [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'sku' => $request->sku,
            'colors' => $request->colors ?? [],
            'materials' => $request->materials ?? [],
            'sizes' => $request->sizes ?? [],
            'face_count' => $request->face_count ?? 1,
            'print_colors_count' => $request->print_colors_count ?? 1,
            'print_colors' => $request->print_colors ?? [],
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'active' => $request->boolean('active', true),
            // Campos del configurador
            'has_configurator' => $request->boolean('has_configurator', false),
            'max_print_colors' => $request->max_print_colors ?? 1,
            'allow_file_upload' => $request->boolean('allow_file_upload', false),
            'file_upload_types' => $request->file_upload_types,
            'configurator_base_price' => $request->configurator_base_price,
            'configurator_description' => $request->configurator_description,
            // Unidad de precio (por unidad o por millar)
            'pricing_unit' => $request->pricing_unit ?? 'unit',
            'pricing_unit_quantity' => $request->pricing_unit === 'thousand' ? 1000 : 1,
        ];
    }

    /**
     * Obtener estadísticas de un producto
     *
     * @param Product $product
     * @return array Array con estadísticas
     */
    public function getProductStats(Product $product): array
    {
        return [
            'order_items_count' => $product->orderItems()->count(),
            'total_sold_quantity' => $product->orderItems()->sum('quantity'),
            'total_revenue' => $product->orderItems()->sum('total_price'),
            'printing_systems_count' => $product->printingSystems()->count(),
            'pricing_ranges_count' => $product->pricing()->count(),
            'attributes_count' => $product->productAttributes()->count(),
        ];
    }

    /**
     * Validar si un producto puede ser eliminado
     *
     * Un producto NO puede eliminarse si está en pedidos
     *
     * @param Product $product
     * @return array ['can_delete' => bool, 'reason' => string|null, 'details' => array]
     */
    public function canDelete(Product $product): array
    {
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

            return [
                'can_delete' => false,
                'reason' => "El producto está incluido en {$ordersCount} pedido(s)",
                'details' => [
                    'orders_count' => $ordersCount,
                    'orders_sample' => $ordersList,
                    'total_items' => $orderItemsCount
                ]
            ];
        }

        return [
            'can_delete' => true,
            'reason' => null,
            'details' => []
        ];
    }

    /**
     * Clonar/duplicar un producto con nuevo nombre
     *
     * @param Product $product Producto a clonar
     * @param string $newName Nuevo nombre para el producto clonado
     * @return Product Producto clonado
     */
    public function cloneProduct(Product $product, string $newName): Product
    {
        // Generar slug único para el clon
        $newSlug = $this->generateUniqueSlug($newName);

        // Copiar atributos del producto
        $productData = $product->toArray();
        unset($productData['id'], $productData['created_at'], $productData['updated_at']);

        $productData['name'] = $newName;
        $productData['slug'] = $newSlug;
        $productData['sku'] = $product->sku ? $product->sku . '-copy' : null;

        // Crear nuevo producto
        $clone = Product::create($productData);

        // Copiar relaciones
        if ($product->printingSystems()->count() > 0) {
            $clone->printingSystems()->sync($product->printingSystems->pluck('id'));
        }

        if ($product->pricing()->count() > 0) {
            foreach ($product->pricing as $pricing) {
                $clone->pricing()->create($pricing->only(['quantity_from', 'quantity_to', 'unit_price']));
            }
        }

        if ($product->productAttributes()->count() > 0) {
            foreach ($product->productAttributes as $attribute) {
                $clone->productAttributes()->attach(
                    $attribute->id,
                    $attribute->pivot->only(['attribute_group_id', 'is_available', 'is_default'])
                );
            }
        }

        return $clone;
    }

    /**
     * Buscar productos por término
     *
     * @param string $term Término de búsqueda
     * @param int $limit Límite de resultados
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchProducts(string $term, int $limit = 20)
    {
        return Product::where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->limit($limit)
            ->get();
    }
}
