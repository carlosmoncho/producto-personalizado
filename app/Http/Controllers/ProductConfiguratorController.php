<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\AttributeGroup;
use App\Models\AttributeDependency;
use App\Models\ProductConfiguration;
use App\Models\PriceRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductConfiguratorController extends Controller
{
    /**
     * Mostrar el configurador de productos
     */
    public function show(Product $product)
    {
        // Verificar si el producto tiene configurador habilitado
        if (!$product->has_configurator) {
            return redirect()->route('admin.products.show', $product)
                ->with('error', 'Este producto no tiene configurador habilitado.');
        }

        // Cargar relaciones necesarias
        $product->load(['productAttributes.attributeGroup', 'pricing']);

        // Cargar configuración existente o crear una nueva
        $sessionId = session()->getId();
        $configuration = ProductConfiguration::where('session_id', $sessionId)
            ->where('product_id', $product->id)
            ->first();

        if (!$configuration) {
            $configuration = ProductConfiguration::create([
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'attributes_base' => [],
                'status' => 'draft',
                'expires_at' => now()->addDays(7),
            ]);
        }

        // Obtener colores disponibles para este producto
        $availableColors = $product->productAttributes()
            ->where('type', ProductAttribute::TYPE_COLOR)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        // Si no hay colores específicos del producto, obtener todos los activos
        if ($availableColors->isEmpty()) {
            $availableColors = ProductAttribute::byType(ProductAttribute::TYPE_COLOR)
                ->active()
                ->orderBy('sort_order')
                ->get();
        }

        return view('configurator.show', [
            'product' => $product,
            'configuration' => $configuration,
            'availableColors' => $availableColors,
        ]);
    }

    /**
     * API: Obtener atributos disponibles basados en la selección actual
     */
    public function getAvailableAttributes(Request $request)
    {
        $type = $request->input('type');
        $currentSelection = $request->input('selection', []);

        // Usar caché para mejorar rendimiento
        $cacheKey = 'attributes_' . $type . '_' . md5(json_encode($currentSelection));
        
        $attributes = Cache::remember($cacheKey, 300, function() use ($type, $currentSelection) {
            return ProductAttribute::getAvailableAttributes($type, $currentSelection);
        });

        // Añadir información de compatibilidad y recomendaciones
        $result = $attributes->map(function ($attribute) use ($currentSelection) {
            $data = $attribute->toArray();
            $data['is_compatible'] = $attribute->isCompatibleWith($currentSelection);
            $data['certifications'] = $attribute->getApplicableCertifications($currentSelection);
            // Note: price_modifier and price_percentage are now in product_attribute_values pivot table
            // They are set per-product basis, not globally on attributes

            return $data;
        });

        // Obtener auto-selecciones si el método existe
        $autoSelect = [];
        if (method_exists(AttributeDependency::class, 'getAutoSelectAttributes')) {
            $autoSelect = AttributeDependency::getAutoSelectAttributes($currentSelection);
        }

        return response()->json([
            'success' => true,
            'attributes' => $result,
            'auto_select' => $autoSelect,
        ]);
    }

    /**
     * API: Obtener tintas recomendadas
     */
    public function getRecommendedInks(Request $request)
    {
        $colorHex = $request->input('color_hex');
        $materialType = $request->input('material_type');

        if (!$colorHex) {
            return response()->json(['error' => 'Color hex code is required'], 400);
        }

        $inks = ProductAttribute::getRecommendedInks($colorHex, $materialType);

        return response()->json([
            'success' => true,
            'recommended_inks' => $inks,
            'contrast_info' => $this->getContrastInfo($colorHex),
        ]);
    }

    /**
     * API: Calcular precio dinámico
     */
    public function calculatePrice(Request $request)
    {
        $productId = $request->input('product_id');
        $selection = $request->input('selection', []);
        $quantity = $request->input('quantity', 1);

        if (!$productId) {
            return response()->json(['error' => 'Product ID is required'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        // Precio base del producto
        $basePrice = $product->pricing()
            ->where('quantity_from', '<=', $quantity)
            ->where('quantity_to', '>=', $quantity)
            ->first();

        if (!$basePrice) {
            // Usar precio del configurador como fallback
            if ($product->configurator_base_price && $product->configurator_base_price > 0) {
                $unitPrice = $product->configurator_base_price;
            } else {
                return response()->json(['error' => 'No pricing available for this quantity'], 400);
            }
        } else {
            $unitPrice = $basePrice->unit_price;
        }

        // Aplicar modificadores de atributos
        $selectedAttributes = ProductAttribute::whereIn('id', array_values($selection))->get();

        foreach ($selectedAttributes as $attribute) {
            $unitPrice = $attribute->calculatePrice($unitPrice, $quantity, $productId);
        }

        // Aplicar impactos de dependencias si el método existe
        if (method_exists(AttributeDependency::class, 'calculatePriceImpact')) {
            $dependencyImpact = AttributeDependency::calculatePriceImpact($selection, $unitPrice, $productId);
            $unitPrice += $dependencyImpact;
        }

        // Aplicar reglas de precios dinámicos si existen
        $finalUnitPrice = $unitPrice;
        if (method_exists(PriceRule::class, 'applyRules')) {
            $finalUnitPrice = PriceRule::applyRules($unitPrice, $selection, $quantity, $productId, $product->category_id);
        }

        // Calcular descuentos por volumen (solo si no hay reglas de precios que los manejen)
        $volumeDiscount = $this->calculateVolumeDiscount($quantity);
        $finalUnitPrice = $finalUnitPrice * (1 - $volumeDiscount);

        $totalPrice = $finalUnitPrice * $quantity;

        // Obtener certificaciones aplicables
        $certifications = $this->getApplicableCertifications($selection);

        return response()->json([
            'success' => true,
            'pricing' => [
                'unit_price' => round($finalUnitPrice, 4),
                'total_price' => round($totalPrice, 2),
                'base_price' => $basePrice ? $basePrice->unit_price : $product->configurator_base_price,
                'volume_discount' => $volumeDiscount * 100, // porcentaje
                'quantity' => $quantity,
            ],
            'certifications' => $certifications,
            'production_time' => $this->estimateProductionTime($selection, $quantity),
        ]);
    }

    /**
     * API: Actualizar configuración
     */
    public function updateConfiguration(Request $request)
    {
        $configurationId = $request->input('configuration_id');
        $attributeType = $request->input('attribute_type');
        $attributeId = $request->input('attribute_id');
        $section = $request->input('section', 'attributes_base'); // attributes_base o personalization

        $configuration = ProductConfiguration::findOrFail($configurationId);

        // Authorization: Check if user owns this configuration
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'No autorizado para modificar esta configuración');
        }

        // Obtener selección actual
        $currentData = $configuration->{$section} ?? [];
        
        // Actualizar selección
        $currentData[$attributeType] = $attributeId;

        // Verificar si necesitamos resetear dependencias
        if ($attributeId) {
            $toReset = AttributeDependency::getAttributesToReset($attributeId, $currentData, $configuration->product_id);

            // Remover atributos que deben resetearse
            $allAttributeTypes = array_keys($currentData);
            foreach ($allAttributeTypes as $type) {
                if (in_array($currentData[$type], $toReset)) {
                    unset($currentData[$type]);
                }
            }
        }

        // Aplicar auto-selecciones
        $autoSelect = AttributeDependency::getAutoSelectAttributes($currentData, $configuration->product_id);
        foreach ($autoSelect as $attributeId) {
            $attribute = ProductAttribute::find($attributeId);
            if ($attribute) {
                $currentData[$attribute->type] = $attributeId;
            }
        }

        // Actualizar configuración
        $configuration->update([
            $section => $currentData,
            'is_valid' => false, // Re-validar en el siguiente paso
        ]);

        // Validar configuración
        $validationErrors = AttributeDependency::validateSelection($currentData);
        
        if (empty($validationErrors)) {
            $configuration->update([
                'is_valid' => true,
                'validation_errors' => null,
            ]);
        } else {
            $configuration->update([
                'validation_errors' => $validationErrors,
            ]);
        }

        return response()->json([
            'success' => true,
            'configuration' => $configuration->fresh(),
            'validation_errors' => $validationErrors,
            'reset_attributes' => $toReset ?? [],
            'auto_selected' => $autoSelect,
        ]);
    }

    /**
     * API: Validar configuración completa
     */
    public function validateConfiguration(Request $request)
    {
        $configurationId = $request->input('configuration_id');
        $configuration = ProductConfiguration::findOrFail($configurationId);

        $allSelection = array_merge(
            $configuration->attributes_base ?? [],
            $configuration->personalization ?? []
        );

        $errors = AttributeDependency::validateSelection($allSelection, $configuration->product_id);

        // Validaciones específicas del negocio
        $businessErrors = $this->validateBusinessRules($configuration);
        $errors = array_merge($errors, $businessErrors);

        $isValid = empty($errors);

        $configuration->update([
            'is_valid' => $isValid,
            'validation_errors' => $errors,
        ]);

        return response()->json([
            'success' => true,
            'is_valid' => $isValid,
            'errors' => $errors,
        ]);
    }

    /**
     * Calcular descuento por volumen
     */
    private function calculateVolumeDiscount($quantity)
    {
        if ($quantity >= 64200) return 0.09; // 9%
        if ($quantity >= 32100) return 0.045; // 4.5%
        return 0;
    }

    /**
     * Obtener información de contraste
     */
    private function getContrastInfo($colorHex)
    {
        $luminosity = $this->calculateLuminosity($colorHex);
        
        return [
            'luminosity' => $luminosity,
            'is_light' => $luminosity > 0.5,
            'recommended_ink_type' => $luminosity > 0.5 ? 'dark' : 'light_or_metallic',
        ];
    }

    private function calculateLuminosity($hex)
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        return 0.299 * $r + 0.587 * $g + 0.114 * $b;
    }

    /**
     * Obtener certificaciones aplicables
     */
    private function getApplicableCertifications($selection)
    {
        $certifications = [];
        $attributes = ProductAttribute::whereIn('id', array_values($selection))->get();

        foreach ($attributes as $attribute) {
            $attrCerts = $attribute->getApplicableCertifications($selection);
            $certifications = array_merge($certifications, $attrCerts);
        }

        return array_unique($certifications);
    }

    /**
     * Estimar tiempo de producción
     */
    private function estimateProductionTime($selection, $quantity)
    {
        $baseDays = 10; // Tiempo base
        
        // Ajustar por cantidad
        if ($quantity > 50000) $baseDays += 5;
        if ($quantity > 100000) $baseDays += 3;

        // Ajustar por complejidad (número de colores)
        $numColors = 1; // Por defecto
        // Lógica para calcular número de colores basado en selección
        
        if ($numColors > 1) $baseDays += ($numColors - 1) * 2;

        return $baseDays;
    }

    /**
     * Validar reglas específicas del negocio
     */
    private function validateBusinessRules($configuration)
    {
        $errors = [];
        $base = $configuration->attributes_base ?? [];
        $personalization = $configuration->personalization ?? [];

        // Validar que se haya seleccionado color
        if (empty($base['color'])) {
            $errors[] = 'Debe seleccionar un color';
        }

        // Validar que se haya seleccionado material
        if (empty($base['material'])) {
            $errors[] = 'Debe seleccionar un material';
        }

        // Si hay personalización, validar tintas
        if (!empty($personalization['number_of_colors'])) {
            $numColors = $personalization['number_of_colors'];
            $selectedInks = $personalization['selected_inks'] ?? [];
            
            if (count($selectedInks) !== $numColors) {
                $errors[] = "Debe seleccionar exactamente {$numColors} tinta(s) de impresión";
            }
        }

        return $errors;
    }
}