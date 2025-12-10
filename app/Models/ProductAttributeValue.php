<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_group_id',
        'product_attribute_id',
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
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_available' => 'boolean',
        'metadata' => 'array',
        'images' => 'array',
        'custom_price_modifier' => 'decimal:4',
        'custom_price_percentage' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    public function productAttribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getPriceModifier()
    {
        // Si tiene un modificador personalizado, usarlo
        if ($this->custom_price_modifier !== null) {
            return $this->custom_price_modifier;
        }
        
        // Si no, usar el del atributo
        return $this->productAttribute->price_modifier ?? 0;
    }

    public function getPricePercentage()
    {
        // Si tiene un porcentaje personalizado, usarlo
        if ($this->custom_price_percentage !== null) {
            return $this->custom_price_percentage;
        }
        
        // Si no, usar el del atributo
        return $this->productAttribute->price_percentage ?? 0;
    }

    public function calculatePrice($basePrice, $quantity = 1)
    {
        $price = $basePrice;
        
        // Aplicar modificador fijo
        $price += $this->getPriceModifier();
        
        // Aplicar porcentaje
        $percentage = $this->getPricePercentage();
        if ($percentage != 0) {
            $price += ($basePrice * ($percentage / 100));
        }
        
        // Aplicar descuentos por cantidad si existen
        if ($quantity >= ($this->min_quantity ?? 1)) {
            // Aquí se podrían aplicar descuentos por volumen
            if (isset($this->metadata['quantity_discounts'])) {
                foreach ($this->metadata['quantity_discounts'] as $discount) {
                    if ($quantity >= $discount['min_qty']) {
                        $price *= (1 - ($discount['discount_percent'] / 100));
                    }
                }
            }
        }
        
        return round($price, 4);
    }

    public function isCompatibleWithQuantity($quantity)
    {
        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return false;
        }
        
        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }
        
        return true;
    }

    public function getProductionDays()
    {
        $baseDays = $this->product->production_days ?? 0;
        return $baseDays + $this->additional_production_days;
    }
}