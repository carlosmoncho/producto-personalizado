<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    const TYPE_COLOR = 'color';
    const TYPE_MATERIAL = 'material';
    const TYPE_SIZE = 'size';
    const TYPE_INK = 'ink';
    const TYPE_INK_COLOR = 'ink_color';
    const TYPE_CLICHE = 'cliche';
    const TYPE_SYSTEM = 'system';
    const TYPE_QUANTITY = 'quantity';
    const TYPE_WEIGHT = 'weight';

    protected $fillable = [
        'attribute_group_id',
        'type',
        'name',
        'description',
        'value',
        'slug',
        'sku_suffix',
        'hex_code',
        'pantone_code',
        'ral_code',
        'image_path',
        'thumbnail_path',
        'stock_quantity',
        'weight_modifier',
        'compatible_materials',
        'incompatible_with',
        'requires_file_upload',
        'metadata',
        'sort_order',
        'active',
        'is_recommended'
    ];

    protected $casts = [
        'metadata' => 'array',
        'compatible_materials' => 'array',
        'incompatible_with' => 'array',
        'weight_modifier' => 'decimal:3',
        'active' => 'boolean',
        'is_recommended' => 'boolean',
        'requires_file_upload' => 'boolean',
    ];

    // Relaciones
    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    public function parentDependencies()
    {
        return $this->hasMany(AttributeDependency::class, 'parent_attribute_id');
    }

    public function childDependencies()
    {
        return $this->hasMany(AttributeDependency::class, 'dependent_attribute_id');
    }

    public function productAttributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->withPivot([
                'attribute_group_id',
                'is_default',
                'is_available',
                'min_quantity',
                'max_quantity',
                'custom_price_modifier',
                'custom_price_percentage',
                'additional_production_days',
                'sort_order',
                'metadata'
            ])
            ->withTimestamps();
    }

    public function variants()
    {
        return ProductVariant::whereJsonContains('attribute_combination', $this->id)->get();
    }

    // Métodos de utilidad
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    /**
     * Obtener atributos disponibles basados en la selección actual
     */
    public static function getAvailableAttributes($type, $currentSelection = [])
    {
        $query = static::byType($type)->active()->orderBy('sort_order');

        if (!empty($currentSelection)) {
            // Aplicar filtros basados en dependencias
            $dependencies = AttributeDependency::whereHas('parentAttribute', function($q) use ($currentSelection) {
                $q->whereIn('id', array_values($currentSelection));
            })
            ->whereHas('dependentAttribute', function($q) use ($type) {
                $q->where('type', $type);
            })
            ->where('active', true)
            ->get();

            $allowedIds = [];
            $blockedIds = [];

            foreach ($dependencies as $dependency) {
                if ($dependency->condition_type === 'allows') {
                    $allowedIds[] = $dependency->dependent_attribute_id;
                } elseif ($dependency->condition_type === 'blocks') {
                    $blockedIds[] = $dependency->dependent_attribute_id;
                }
            }

            if (!empty($allowedIds)) {
                $query->whereIn('id', $allowedIds);
            }

            if (!empty($blockedIds)) {
                $query->whereNotIn('id', $blockedIds);
            }
        }

        return $query->get();
    }

    /**
     * Verificar si este atributo es compatible con la selección actual
     */
    public function isCompatibleWith($currentSelection)
    {
        $blockingDependencies = $this->childDependencies()
            ->where('condition_type', 'blocks')
            ->whereHas('parentAttribute', function($q) use ($currentSelection) {
                $q->whereIn('id', array_values($currentSelection));
            })
            ->count();

        return $blockingDependencies === 0;
    }

    /**
     * Obtener tintas recomendadas para un color de fondo
     */
    public static function getRecommendedInks($colorHex, $materialType = null)
    {
        // Lógica de contraste basada en luminosidad
        $luminosity = self::calculateLuminosity($colorHex);
        
        $query = static::byType(self::TYPE_INK)->active();

        if ($luminosity > 0.5) {
            // Fondo claro - recomendar tintas oscuras
            $query->where(function($q) {
                $q->where('is_recommended', true)
                  ->orWhereRaw('JSON_EXTRACT(metadata, "$.luminosity") < 0.5');
            });
        } else {
            // Fondo oscuro - recomendar tintas claras y metálicas
            $query->where(function($q) {
                $q->whereRaw('JSON_EXTRACT(metadata, "$.luminosity") > 0.5')
                  ->orWhereRaw('JSON_EXTRACT(metadata, "$.is_metallic") = true');
            });
        }

        return $query->orderBy('sort_order')->get();
    }

    /**
     * Calcular luminosidad de un color hexadecimal
     */
    private static function calculateLuminosity($hex)
    {
        $hex = ltrim($hex, '#');
        
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Fórmula de luminosidad relativa
        return 0.299 * $r + 0.587 * $g + 0.114 * $b;
    }

    /**
     * Obtener certificaciones aplicables para esta combinación
     */
    public function getApplicableCertifications($selection = [])
    {
        $certifications = [];

        if (isset($this->metadata['certifications'])) {
            $certifications = array_merge($certifications, $this->metadata['certifications']);
        }

        // Verificar certificaciones condicionales
        if ($this->type === self::TYPE_COLOR && $this->value === 'NATURAL') {
            $certifications[] = 'ECO';
            $certifications[] = 'BIODEGRADABLE';
        }

        return array_unique($certifications);
    }

    /**
     * Calcular el precio aplicando los modificadores de este atributo
     *
     * @param float $basePrice Precio actual
     * @param int $quantity Cantidad (para cálculos futuros)
     * @param int|null $productId ID del producto (para buscar modificadores personalizados)
     * @return float Precio modificado
     */
    public function calculatePrice($basePrice, $quantity = 1, $productId = null)
    {
        $price = $basePrice;

        // Si hay un productId, buscar modificadores personalizados en la tabla pivot
        if ($productId) {
            $pivotData = \DB::table('product_attribute_values')
                ->where('product_id', $productId)
                ->where('product_attribute_id', $this->id)
                ->where('is_available', true)
                ->first();

            if ($pivotData) {
                // Aplicar modificador fijo primero
                if ($pivotData->custom_price_modifier) {
                    $price += $pivotData->custom_price_modifier;
                }

                // Luego aplicar porcentaje
                if ($pivotData->custom_price_percentage) {
                    $price = $price * (1 + ($pivotData->custom_price_percentage / 100));
                }

                return round($price, 4);
            }
        }

        // Si no hay modificadores personalizados en pivot, buscar en dependencias
        // Las dependencias de tipo "price_rule" actúan como modificadores directos
        $priceRuleDependencies = $this->parentDependencies()
            ->where('is_price_rule', true)
            ->where('active', true)
            ->get();

        foreach ($priceRuleDependencies as $dependency) {
            if ($dependency->price_modifier) {
                $price += $dependency->price_modifier;
            }

            if ($dependency->price_impact) {
                $price += $dependency->price_impact;
            }
        }

        return round($price, 4);
    }

}