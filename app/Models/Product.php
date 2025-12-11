<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sku',
        'colors',
        'materials',
        'sizes',
        'face_count',
        'print_colors_count',
        'print_colors',
        'images',
        'model_3d_file',
        'category_id',
        'subcategory_id',
        'active',
        // Campos del configurador
        'has_configurator',
        'configurator_base_price',
        'available_colors',
        'available_materials',
        'available_sizes',
        'available_inks',
        'available_quantities',
        'available_systems',
        'available_weights',
        'configurator_rules',
        'base_pricing',
        'max_print_colors',
        'allow_file_upload',
        'file_upload_types',
        'price_modifiers',
        'configurator_description',
        'configurator_settings',
        // Campos de unidad de precio
        'pricing_unit',           // 'unit' o 'thousand'
        'pricing_unit_quantity',  // Cantidad por unidad de venta (1 o 1000)
    ];

    /**
     * Campos protegidos contra mass assignment
     * Estos campos solo deben modificarse de forma explícita
     */
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'colors' => 'array',
        'materials' => 'array',
        'sizes' => 'array',
        'print_colors' => 'array',
        'images' => 'array',
        'active' => 'boolean',
        // Casts del configurador
        'has_configurator' => 'boolean',
        'available_colors' => 'array',
        'available_materials' => 'array',
        'available_sizes' => 'array',
        'available_inks' => 'array',
        'available_quantities' => 'array',
        'available_systems' => 'array',
        'available_weights' => 'array',
        'configurator_rules' => 'array',
        'base_pricing' => 'array',
        'allow_file_upload' => 'boolean',
        'file_upload_types' => 'array',
        'configurator_base_price' => 'decimal:4',
        'price_modifiers' => 'array',
        'configurator_settings' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    // Nuevas relaciones para el sistema mejorado de atributos
    public function attributeDependencies()
    {
        return $this->hasMany(AttributeDependency::class);
    }

    public function attributeGroups()
    {
        return $this->belongsToMany(AttributeGroup::class, 'product_attribute_values')
            ->withPivot([
                'product_attribute_id',
                'is_default',
                'is_available',
                'min_quantity',
                'max_quantity',
                'custom_price_modifier',
                'custom_price_percentage',
                'additional_production_days',
                'sort_order'
            ])
            ->withTimestamps();
    }

    public function productAttributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_attribute_values')
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
                'metadata',
                'images'
            ])
            ->withTimestamps();
    }

    public function productAttributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    public function defaultVariant()
    {
        return $this->variants()->where('is_default', true)->first();
    }

    public function getVariantByCombination(array $attributeIds)
    {
        foreach ($this->variants as $variant) {
            if ($variant->matchesAttributes($attributeIds)) {
                return $variant;
            }
        }
        return null;
    }

    // Relación muchos a muchos con sistemas de impresión
    public function printingSystems()
    {
        return $this->belongsToMany(PrintingSystem::class, 'product_printing_system')
                    ->withTimestamps();
    }

    // Método de compatibilidad para obtener el primer sistema de impresión
    public function printingSystem()
    {
        return $this->printingSystems()->first();
    }

    public function pricing()
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPriceForQuantity($quantity)
    {
        return $this->pricing()
                    ->where('quantity_from', '<=', $quantity)
                    ->where('quantity_to', '>=', $quantity)
                    ->first();
    }

    public function getImagesUrls()
    {
        if (!$this->images || !is_array($this->images)) {
            return [];
        }

        return array_map(function($image) {
            return Storage::disk(config('filesystems.default', 'public'))->url($image);
        }, $this->images);
    }

    public function getFirstImageUrl()
    {
        $images = $this->getImagesUrls();
        return $images ? $images[0] : asset('images/no-image.png');
    }

    public function getModel3dUrl()
    {
        if (!$this->model_3d_file) {
            return null;
        }

        // Usar Storage URL directamente ya que está en disco público
        // Esto genera: /storage/3d-models/archivo.glb
        return \Illuminate\Support\Facades\Storage::disk(config('filesystems.default', 'public'))->url($this->model_3d_file);
    }

    /**
     * Obtener los materiales como string separado por comas
     */
    public function getMaterialsListAttribute()
    {
        return $this->materials ? implode(', ', $this->materials) : '';
    }

    /**
     * Obtener los colores como string separado por comas
     */
    public function getColorsListAttribute()
    {
        return $this->colors ? implode(', ', $this->colors) : '';
    }

    /**
     * Obtener los tamaños como string separado por comas
     */
    public function getSizesListAttribute()
    {
        return $this->sizes ? implode(', ', $this->sizes) : '';
    }

    /**
     * Obtener los colores de impresión como string separado por comas
     */
    public function getPrintColorsListAttribute()
    {
        return $this->print_colors ? implode(', ', $this->print_colors) : '';
    }

    /**
     * Obtener los sistemas de impresión como string separado por comas
     */
    public function getPrintingSystemsListAttribute()
    {
        return $this->printingSystems->pluck('name')->implode(', ');
    }

    /**
     * Verificar si el producto tiene un sistema de impresión específico
     */
    public function hasPrintingSystem($printingSystemId)
    {
        return $this->printingSystems()->where('printing_system_id', $printingSystemId)->exists();
    }

    /**
     * Obtener el rango de precios del producto
     */
    public function getPriceRangeAttribute()
    {
        $prices = $this->pricing()->orderBy('unit_price')->get();
        
        if ($prices->isEmpty()) {
            return 'Sin precio';
        }
        
        $min = $prices->first()->unit_price;
        $max = $prices->last()->unit_price;
        
        if ($min == $max) {
            return '€' . number_format($min, 2);
        }
        
        return '€' . number_format($min, 2) . ' - €' . number_format($max, 2);
    }

    /**
     * Scope para productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope para filtrar por subcategoría
     */
    public function scopeInSubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    /**
     * Scope para búsqueda segura de productos
     * Busca en nombre, SKU y descripción sin SQL injection
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        // Sanitizar el término de búsqueda
        $searchTerm = trim($searchTerm);

        // Limitar longitud para prevenir DoS
        $searchTerm = substr($searchTerm, 0, 100);

        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * Scope para productos con configurador activo
     */
    public function scopeWithConfigurator($query)
    {
        return $query->where('has_configurator', true);
    }

    /**
     * Scope para productos por rango de precio
     */
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        return $query->when($minPrice || $maxPrice, function($q) use ($minPrice, $maxPrice) {
            $q->whereHas('pricing', function($pricingQuery) use ($minPrice, $maxPrice) {
                if ($minPrice !== null) {
                    $pricingQuery->where('unit_price', '>=', $minPrice);
                }
                if ($maxPrice !== null) {
                    $pricingQuery->where('unit_price', '<=', $maxPrice);
                }
            });
        });
    }

    // ==================== MÉTODOS DEL CONFIGURADOR ====================

    /**
     * Verificar si el producto tiene configurador activo
     */
    public function hasActiveConfigurator()
    {
        return $this->has_configurator && $this->active;
    }

    /**
     * Obtener atributos disponibles por tipo para este producto
     */
    public function getAvailableAttributesByType($type)
    {
        $fieldName = "available_{$type}s";
        $attributeIds = $this->$fieldName ?? [];
        
        if (empty($attributeIds)) {
            return collect();
        }

        return ProductAttribute::whereIn('id', $attributeIds)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Obtener todos los atributos disponibles para este producto
     */
    public function getAllAvailableAttributes()
    {
        $types = ['color', 'material', 'size', 'ink', 'quantity', 'system', 'weight'];
        $attributes = [];

        foreach ($types as $type) {
            $attributes[$type] = $this->getAvailableAttributesByType($type);
        }

        return $attributes;
    }

    /**
     * Obtener las reglas del configurador para este producto
     */
    public function getConfiguratorRules()
    {
        return $this->configurator_rules ?? [];
    }

    /**
     * Verificar si un atributo específico está disponible para este producto
     */
    public function hasProductAttribute($attributeId, $type)
    {
        $fieldName = "available_{$type}s";
        $attributeIds = $this->$fieldName ?? [];
        
        return in_array($attributeId, $attributeIds);
    }

    /**
     * Obtener precio base del configurador
     */
    public function getConfiguratorBasePrice()
    {
        return $this->configurator_base_price ?? 0;
    }

    /**
     * Obtener modificadores de precio del configurador
     */
    public function getPriceModifiers()
    {
        return $this->price_modifiers ?? [];
    }

    /**
     * Verificar si permite subida de archivos
     */
    public function allowsFileUpload()
    {
        return $this->allow_file_upload;
    }

    /**
     * Obtener tipos de archivo permitidos
     */
    public function getAllowedFileTypes()
    {
        return $this->file_upload_types ?? ['jpg', 'png', 'pdf'];
    }

    /**
     * Obtener configuración del configurador
     */
    public function getConfiguratorSettings()
    {
        return $this->configurator_settings ?? [];
    }

    /**
     * Obtener número máximo de colores de impresión
     */
    public function getMaxPrintColors()
    {
        return $this->max_print_colors ?? 1;
    }

    /**
     * Obtener estadísticas del configurador para el producto
     */
    public function getConfiguratorStats()
    {
        $attributes = $this->getAllAvailableAttributes();
        
        return [
            'total_attributes' => collect($attributes)->flatten()->count(),
            'colors_count' => $attributes['color']->count(),
            'materials_count' => $attributes['material']->count(),
            'sizes_count' => $attributes['size']->count(),
            'inks_count' => $attributes['ink']->count(),
            'quantities_count' => $attributes['quantity']->count(),
            'systems_count' => $attributes['system']->count(),
            'has_file_upload' => $this->allowsFileUpload(),
            'max_print_colors' => $this->getMaxPrintColors(),
        ];
    }

    /**
     * Validar configuración del configurador
     */
    public function validateConfiguratorSetup()
    {
        $errors = [];

        if ($this->has_configurator) {
            // Verificar que tenga al menos colores
            if (empty($this->available_colors)) {
                $errors[] = 'Debe tener al menos un color disponible';
            }

            // Verificar que tenga materiales
            if (empty($this->available_materials)) {
                $errors[] = 'Debe tener al menos un material disponible';
            }

            // Verificar que tenga tamaños
            if (empty($this->available_sizes)) {
                $errors[] = 'Debe tener al menos un tamaño disponible';
            }

            // Verificar que tenga cantidades
            if (empty($this->available_quantities)) {
                $errors[] = 'Debe tener al menos una cantidad disponible';
            }

            // Verificar precio base
            if (!$this->configurator_base_price || $this->configurator_base_price <= 0) {
                $errors[] = 'Debe definir un precio base válido para el configurador';
            }
        }

        return $errors;
    }

    // ==================== MÉTODOS DE UNIDAD DE PRECIO ====================

    /**
     * Verificar si el producto usa precio por millar
     */
    public function isPricedPerThousand(): bool
    {
        return $this->pricing_unit === 'thousand';
    }

    /**
     * Verificar si el producto usa precio por unidad
     */
    public function isPricedPerUnit(): bool
    {
        return $this->pricing_unit === 'unit' || $this->pricing_unit === null;
    }

    /**
     * Obtener la cantidad base de la unidad de precio
     * Por unidad = 1, Por millar = 1000
     */
    public function getPricingUnitQuantity(): int
    {
        if ($this->pricing_unit === 'thousand') {
            return $this->pricing_unit_quantity ?? 1000;
        }
        return 1;
    }

    /**
     * Obtener la etiqueta de la unidad de precio
     */
    public function getPricingUnitLabel(): string
    {
        return $this->pricing_unit === 'thousand' ? 'millar' : 'unidad';
    }

    /**
     * Calcular el precio por unidad individual a partir del precio por millar
     */
    public function getUnitPriceFromThousandPrice(float $pricePerThousand): float
    {
        $unitQty = $this->getPricingUnitQuantity();
        return $unitQty > 0 ? $pricePerThousand / $unitQty : $pricePerThousand;
    }

    /**
     * Calcular cuántos millares (o unidades de venta) representa una cantidad
     */
    public function getSellingUnits(int $quantity): float
    {
        $unitQty = $this->getPricingUnitQuantity();
        return $unitQty > 0 ? $quantity / $unitQty : $quantity;
    }
}