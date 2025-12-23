<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeDependency;
use App\Models\PriceRule;

/**
 * Servicio de cálculo de precios para productos configurables
 *
 * Centraliza toda la lógica de pricing:
 * - Precio base + modificadores de atributos
 * - Impacto de dependencias
 * - Descuentos por volumen
 * - Reglas dinámicas de precio
 *
 * @package App\Services\Pricing
 */
class PricingService
{
    /**
     * Descuentos por volumen (cantidad => porcentaje)
     * DESACTIVADO: Sin descuentos automáticos por volumen
     */
    private const VOLUME_DISCOUNTS = [
        // 64200 => 0.09,   // 9% para 64,200+
        // 32100 => 0.045,  // 4.5% para 32,100+
    ];

    /**
     * Tiempo base de producción en días
     */
    private const BASE_PRODUCTION_DAYS = 10;

    /**
     * Calcular precio completo de un producto configurado
     *
     * @param Product $product Producto a calcular
     * @param array $selection Array de attribute IDs seleccionados
     * @param int $quantity Cantidad de productos
     * @return array Array con información de pricing
     */
    public function calculateProductPrice(Product $product, array $selection, int $quantity = 1): array
    {
        // 1. Obtener precio base según cantidad
        $basePrice = $this->getBasePrice($product, $quantity);
        $unitPrice = $basePrice['unit_price'];

        // Información de unidad de precio (por unidad o por millar)
        $pricingUnit = $product->pricing_unit ?? 'unit';
        $pricingUnitQuantity = $product->getPricingUnitQuantity();

        // 1.5 Auto-inyectar atributo de cantidad del tramo correspondiente
        //     para que las dependencias de precio apliquen correctamente
        $selectionWithTier = $this->injectQuantityTierAttribute($product, $selection, $quantity);

        // 2. Aplicar modificadores de atributos
        $attributeModifiers = $this->calculateAttributeModifiers($product, $selectionWithTier, $unitPrice);

        // 3. Aplicar impacto de dependencias (separado por unit/total)
        $dependencyImpact = $this->calculateDependencyPriceImpact($selectionWithTier, $product->id);
        $unitDependencyImpact = $dependencyImpact['unit'];
        $totalDependencyImpact = $dependencyImpact['total'];
        $percentages = $dependencyImpact['percentages'] ?? [];

        // 4. Precio después de modificadores (solo impacto unitario)
        $priceAfterModifiers = $unitPrice + $attributeModifiers + $unitDependencyImpact;

        // 4.5 Aplicar modificadores porcentuales al precio unitario
        foreach ($percentages as $pct) {
            if ($pct['applies_to'] === 'unit') {
                $priceAfterModifiers *= (1 + $pct['percentage'] / 100);
            }
        }

        // 5. Aplicar reglas de precio dinámico (si existen)
        $finalUnitPrice = $this->applyPriceRules(
            $priceAfterModifiers,
            $selection,
            $quantity,
            $product->id,
            $product->category_id
        );

        // 6. Aplicar descuento por volumen
        $volumeDiscount = $this->calculateVolumeDiscount($quantity);
        $finalUnitPrice = $finalUnitPrice * (1 - $volumeDiscount);

        // 6.5 Redondear precio unitario a 4 decimales ANTES de calcular total
        $finalUnitPrice = round($finalUnitPrice, 4);

        // 7. Calcular precio total (unitario × cantidad + impactos al total)
        $totalPrice = ($finalUnitPrice * $quantity) + $totalDependencyImpact;

        // 7.5 Aplicar modificadores porcentuales al total
        foreach ($percentages as $pct) {
            if ($pct['applies_to'] === 'total') {
                $totalPrice *= (1 + $pct['percentage'] / 100);
            }
        }

        // 8. Obtener información adicional
        $certifications = $this->getApplicableCertifications($selection);
        $productionTime = $this->estimateProductionTime($selection, $quantity);

        return [
            'pricing' => [
                'unit_price' => round($finalUnitPrice, 4),
                'total_price' => round($totalPrice, 2),
                'base_price' => $unitPrice,
                'attribute_modifiers' => round($attributeModifiers, 2),
                'dependency_impact' => round($unitDependencyImpact, 2),
                'total_extras' => round($totalDependencyImpact, 2),
                'volume_discount_percentage' => $volumeDiscount * 100,
                'quantity' => $quantity,
                // Información de unidad de precio
                'pricing_unit' => $pricingUnit,                    // 'unit' o 'thousand'
                'pricing_unit_quantity' => $pricingUnitQuantity,   // 1 o 1000
                'pricing_unit_label' => $product->getPricingUnitLabel(), // 'unidad' o 'millar'
            ],
            'certifications' => $certifications,
            'production_time_days' => $productionTime,
        ];
    }

    /**
     * Obtener precio base del producto según cantidad
     *
     * @param Product $product
     * @param int $quantity
     * @return array ['unit_price' => float, 'source' => string]
     */
    public function getBasePrice(Product $product, int $quantity): array
    {
        // 1. Buscar rango de precio exacto que corresponda a la cantidad
        $priceRange = $product->pricing()
            ->where('quantity_from', '<=', $quantity)
            ->where('quantity_to', '>=', $quantity)
            ->first();

        if ($priceRange) {
            return [
                'unit_price' => (float) $priceRange->unit_price,
                'source' => 'pricing_range',
                'range_id' => $priceRange->id
            ];
        }

        // 2. Si no hay rango exacto, buscar el tramo de cantidad apropiado
        //    basándose en los atributos de cantidad del producto
        $tierQuantity = $this->findQuantityTier($product, $quantity);

        if ($tierQuantity) {
            // Buscar precio para el tramo encontrado
            $tierPriceRange = $product->pricing()
                ->where('quantity_from', '<=', $tierQuantity)
                ->where('quantity_to', '>=', $tierQuantity)
                ->first();

            if ($tierPriceRange) {
                return [
                    'unit_price' => (float) $tierPriceRange->unit_price,
                    'source' => 'pricing_tier',
                    'range_id' => $tierPriceRange->id,
                    'tier_quantity' => $tierQuantity
                ];
            }
        }

        // 3. Fallback: usar precio base del configurador
        return [
            'unit_price' => (float) ($product->configurator_base_price ?? 0),
            'source' => 'configurator_base',
            'range_id' => null
        ];
    }

    /**
     * Encontrar el tramo de cantidad apropiado para una cantidad dada
     * Lógica: usa el tramo inferior (floor), mínimo si < min, máximo si > max
     *
     * @param Product $product
     * @param int $quantity
     * @return int|null La cantidad del tramo correspondiente, o null si no hay atributos
     */
    private function findQuantityTier(Product $product, int $quantity): ?int
    {
        // Obtener atributos de cantidad del producto
        $quantityAttributes = $product->productAttributeValues()
            ->with('productAttribute.attributeGroup')
            ->whereHas('productAttribute.attributeGroup', function($query) {
                $query->where('type', 'quantity');
            })
            ->get()
            ->map(function($pav) {
                return [
                    'id' => $pav->productAttribute->id,
                    'value' => (int) $pav->productAttribute->value
                ];
            })
            ->sortBy('value')
            ->values();

        if ($quantityAttributes->isEmpty()) {
            return null;
        }

        $minTier = $quantityAttributes->first()['value'];
        $maxTier = $quantityAttributes->last()['value'];

        // Si es menor que el mínimo, usar el mínimo
        if ($quantity < $minTier) {
            return $minTier;
        }

        // Si es mayor o igual que el máximo, usar el máximo
        if ($quantity >= $maxTier) {
            return $maxTier;
        }

        // Buscar el tramo inferior (floor)
        $tierValue = $minTier;
        foreach ($quantityAttributes as $attr) {
            if ($attr['value'] <= $quantity) {
                $tierValue = $attr['value'];
            } else {
                break;
            }
        }

        return $tierValue;
    }

    /**
     * Auto-inyectar el atributo de cantidad del tramo correspondiente
     * Si la selección no incluye un atributo de cantidad, añade el del tramo correcto
     *
     * @param Product $product
     * @param array $selection
     * @param int $quantity
     * @return array Selection con el atributo de cantidad inyectado si corresponde
     */
    private function injectQuantityTierAttribute(Product $product, array $selection, int $quantity): array
    {
        // Obtener todos los atributos de cantidad del producto
        $quantityAttributes = $product->productAttributeValues()
            ->with('productAttribute.attributeGroup')
            ->whereHas('productAttribute.attributeGroup', function($query) {
                $query->where('type', 'quantity');
            })
            ->get()
            ->map(function($pav) {
                return [
                    'id' => $pav->productAttribute->id,
                    'value' => (int) $pav->productAttribute->value
                ];
            })
            ->sortBy('value')
            ->values();

        if ($quantityAttributes->isEmpty()) {
            return $selection;
        }

        // Verificar si ya hay un atributo de cantidad en la selección
        $quantityAttrIds = $quantityAttributes->pluck('id')->toArray();
        $hasQuantityAttr = !empty(array_intersect($selection, $quantityAttrIds));

        if ($hasQuantityAttr) {
            // Ya tiene atributo de cantidad, no modificar
            return $selection;
        }

        // Encontrar el tramo correcto y añadir su ID
        $minTier = $quantityAttributes->first();
        $maxTier = $quantityAttributes->last();

        // Si es menor que el mínimo, usar el mínimo
        if ($quantity < $minTier['value']) {
            return array_merge($selection, [$minTier['id']]);
        }

        // Si es mayor o igual que el máximo, usar el máximo
        if ($quantity >= $maxTier['value']) {
            return array_merge($selection, [$maxTier['id']]);
        }

        // Buscar el tramo inferior (floor)
        $tierAttr = $minTier;
        foreach ($quantityAttributes as $attr) {
            if ($attr['value'] <= $quantity) {
                $tierAttr = $attr;
            } else {
                break;
            }
        }

        return array_merge($selection, [$tierAttr['id']]);
    }

    /**
     * Calcular modificadores de precio por atributos seleccionados
     *
     * @param Product $product
     * @param array $selectedAttributeIds
     * @param float $basePrice Precio base para calcular porcentajes
     * @return float Total de modificadores
     */
    public function calculateAttributeModifiers(Product $product, array $selectedAttributeIds, float $basePrice): float
    {
        if (empty($selectedAttributeIds)) {
            return 0;
        }

        $selectedAttributes = ProductAttribute::whereIn('id', $selectedAttributeIds)->get();
        $totalModifiers = 0;

        foreach ($selectedAttributes as $attribute) {
            // Obtener precio customizado del pivot si existe
            $pivotData = $product->productAttributeValues()
                ->where('product_attribute_id', $attribute->id)
                ->first();

            if ($pivotData) {
                // Modificador fijo
                if ($pivotData->custom_price_modifier) {
                    $totalModifiers += (float) $pivotData->custom_price_modifier;
                }
                // Modificador porcentual
                elseif ($pivotData->custom_price_percentage) {
                    $totalModifiers += $basePrice * ((float) $pivotData->custom_price_percentage / 100);
                }
            }
        }

        return $totalModifiers;
    }

    /**
     * Calcular impacto de precio por dependencias entre atributos
     *
     * @param array $selectedIds IDs de atributos seleccionados
     * @param int $productId ID del producto
     * @return array ['unit' => float, 'total' => float] Impactos separados
     */
    public function calculateDependencyPriceImpact(array $selectedIds, int $productId): array
    {
        $result = ['unit' => 0, 'total' => 0, 'percentages' => []];

        if (empty($selectedIds)) {
            return $result;
        }

        // Obtener dependencias aplicables (del producto o globales)
        $dependencies = AttributeDependency::where(function($query) use ($productId) {
                $query->where('product_id', $productId)
                      ->orWhere('product_id', null);
            })
            ->active()
            ->get();

        foreach ($dependencies as $dependency) {
            $parentId = $dependency->parent_attribute_id;
            $dependentId = $dependency->dependent_attribute_id;
            $thirdId = $dependency->third_attribute_id;
            $appliesTo = $dependency->price_applies_to ?? 'unit';

            // Verificar si los atributos requeridos están seleccionados
            $parentSelected = in_array($parentId, $selectedIds);
            $dependentSelected = $dependentId ? in_array($dependentId, $selectedIds) : false;
            $thirdSelected = $thirdId ? in_array($thirdId, $selectedIds) : false;

            // Caso 1: Dependencia de 3 atributos (parent + dependent + third)
            if ($thirdId && $parentSelected && $dependentSelected && $thirdSelected) {
                $this->applyDependencyModifiers($dependency, $appliesTo, $result);
            }
            // Caso 2: Dependencia de 2 atributos (parent + dependent, sin third)
            elseif ($dependentId && !$thirdId && $parentSelected && $dependentSelected) {
                $this->applyDependencyModifiers($dependency, $appliesTo, $result);
            }
            // Caso 3: Modificador individual (solo parent, sin dependent ni third)
            elseif (!$dependentId && !$thirdId && $parentSelected) {
                $this->applyDependencyModifiers($dependency, $appliesTo, $result);
            }
        }

        return $result;
    }

    /**
     * Aplicar los modificadores de precio de una dependencia
     *
     * @param AttributeDependency $dependency
     * @param string $appliesTo 'unit' o 'total'
     * @param array &$result Array de resultados por referencia
     */
    private function applyDependencyModifiers(AttributeDependency $dependency, string $appliesTo, array &$result): void
    {
        // price_impact (deprecated pero aún soportado)
        if ($dependency->price_impact) {
            $result[$appliesTo] += (float) $dependency->price_impact;
        }
        // price_modifier (fijo)
        if ($dependency->price_modifier) {
            $result[$appliesTo] += (float) $dependency->price_modifier;
        }
        // price_percentage (porcentual) - se guarda para aplicar después
        if ($dependency->price_percentage) {
            $result['percentages'][] = [
                'percentage' => (float) $dependency->price_percentage,
                'applies_to' => $appliesTo
            ];
        }
    }

    /**
     * Aplicar reglas de precio dinámico (si existen)
     *
     * @param float $currentPrice Precio actual
     * @param array $selection Selección de atributos
     * @param int $quantity Cantidad
     * @param int $productId ID del producto
     * @param int|null $categoryId ID de la categoría
     * @return float Precio después de aplicar reglas
     */
    public function applyPriceRules(
        float $currentPrice,
        array $selection,
        int $quantity,
        int $productId,
        ?int $categoryId
    ): float {
        // Solo aplicar si la clase PriceRule existe y tiene el método
        if (class_exists(PriceRule::class) && method_exists(PriceRule::class, 'applyRules')) {
            return PriceRule::applyRules($currentPrice, $selection, $quantity, $productId, $categoryId);
        }

        return $currentPrice;
    }

    /**
     * Calcular descuento por volumen
     *
     * @param int $quantity Cantidad de productos
     * @return float Porcentaje de descuento (0.09 = 9%)
     */
    public function calculateVolumeDiscount(int $quantity): float
    {
        // Iterar descendente para encontrar el primer match
        foreach (self::VOLUME_DISCOUNTS as $minQuantity => $discount) {
            if ($quantity >= $minQuantity) {
                return $discount;
            }
        }

        return 0;
    }

    /**
     * Obtener certificaciones aplicables según atributos seleccionados
     *
     * @param array $selectedIds IDs de atributos
     * @return array Array de certificaciones únicas
     */
    public function getApplicableCertifications(array $selectedIds): array
    {
        if (empty($selectedIds)) {
            return [];
        }

        $certifications = [];
        $attributes = ProductAttribute::whereIn('id', $selectedIds)->get();

        foreach ($attributes as $attribute) {
            // Buscar certificaciones en metadata del atributo
            if (isset($attribute->metadata['certifications']) && is_array($attribute->metadata['certifications'])) {
                $certifications = array_merge($certifications, $attribute->metadata['certifications']);
            }
        }

        return array_values(array_unique($certifications));
    }

    /**
     * Estimar tiempo de producción en días
     *
     * @param array $selection Selección de atributos
     * @param int $quantity Cantidad de productos
     * @return int Días estimados de producción
     */
    public function estimateProductionTime(array $selection, int $quantity): int
    {
        $baseDays = self::BASE_PRODUCTION_DAYS;

        // Ajustar según cantidad
        if ($quantity > 50000) {
            $baseDays += 5;
        }
        if ($quantity > 100000) {
            $baseDays += 3;
        }

        // TODO: En el futuro podría considerar:
        // - Complejidad de atributos seleccionados
        // - Disponibilidad de materiales
        // - Carga actual de producción

        return $baseDays;
    }

    /**
     * Obtener información de contraste para un color (útil para recomendaciones)
     *
     * @param string $colorHex Color en formato hexadecimal (#RRGGBB)
     * @return array Info de contraste y luminosidad
     */
    public function getContrastInfo(string $colorHex): array
    {
        $luminosity = $this->calculateLuminosity($colorHex);

        return [
            'luminosity' => round($luminosity, 3),
            'is_light' => $luminosity > 0.5,
            'recommended_ink_type' => $luminosity > 0.5 ? 'dark' : 'light_or_metallic',
        ];
    }

    /**
     * Calcular luminosidad de un color hexadecimal
     *
     * Usa la fórmula estándar de luminosidad relativa
     *
     * @param string $hex Color en formato hexadecimal
     * @return float Luminosidad (0.0 = negro, 1.0 = blanco)
     */
    public function calculateLuminosity(string $hex): float
    {
        $hex = ltrim($hex, '#');

        // Validar formato
        if (strlen($hex) !== 6) {
            return 0.5; // Default: medio
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Fórmula estándar de luminosidad relativa
        return 0.299 * $r + 0.587 * $g + 0.114 * $b;
    }

    /**
     * Validar que una selección tiene todos los atributos requeridos
     *
     * @param Product $product
     * @param array $selection
     * @return array ['valid' => bool, 'missing' => array]
     */
    public function validateRequiredAttributes(Product $product, array $selection): array
    {
        // TODO: Implementar cuando se definan atributos requeridos
        // Por ahora retornar válido
        return [
            'valid' => true,
            'missing' => []
        ];
    }
}
