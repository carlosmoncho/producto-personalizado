<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_sku',
        'variant_name',
        'attribute_combination',
        'price',
        'compare_price',
        'cost',
        'stock_quantity',
        'reserved_quantity',
        'track_inventory',
        'allow_backorder',
        'weight',
        'barcode',
        'image_path',
        'gallery_paths',
        'production_days',
        'is_default',
        'is_active',
        'min_order_quantity',
        'max_order_quantity',
        'metadata'
    ];

    protected $casts = [
        'attribute_combination' => 'array',
        'gallery_paths' => 'array',
        'metadata' => 'array',
        'price' => 'decimal:4',
        'compare_price' => 'decimal:4',
        'cost' => 'decimal:4',
        'weight' => 'decimal:3',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Generar SKU si no existe
            if (empty($model->variant_sku)) {
                $model->variant_sku = $model->generateSku();
            }
            
            // Generar nombre si no existe
            if (empty($model->variant_name)) {
                $model->variant_name = $model->generateName();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getAttributes()
    {
        if (empty($this->attribute_combination)) {
            return collect();
        }
        
        return ProductAttribute::whereIn('id', $this->attribute_combination)->get();
    }

    public function generateSku()
    {
        $baseSku = $this->product->sku ?? 'PROD';
        $attributes = $this->getAttributes();
        
        $suffixes = [];
        foreach ($attributes as $attribute) {
            if ($attribute->sku_suffix) {
                $suffixes[] = $attribute->sku_suffix;
            } else {
                $suffixes[] = strtoupper(substr($attribute->value, 0, 3));
            }
        }
        
        return $baseSku . '-' . implode('-', $suffixes);
    }

    public function generateName()
    {
        $baseName = $this->product->name ?? 'Producto';
        $attributes = $this->getAttributes();
        
        $attributeNames = $attributes->pluck('name')->toArray();
        
        if (empty($attributeNames)) {
            return $baseName;
        }
        
        return $baseName . ' - ' . implode(' / ', $attributeNames);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('track_inventory', false)
              ->orWhere('stock_quantity', '>', 0)
              ->orWhere('allow_backorder', true);
        });
    }

    public function getAvailableQuantity()
    {
        if (!$this->track_inventory) {
            return PHP_INT_MAX;
        }
        
        $available = $this->stock_quantity - $this->reserved_quantity;
        
        if ($this->allow_backorder) {
            return PHP_INT_MAX;
        }
        
        return max(0, $available);
    }

    public function isAvailable($quantity = 1)
    {
        if (!$this->is_active) {
            return false;
        }
        
        if ($quantity < $this->min_order_quantity) {
            return false;
        }
        
        if ($this->max_order_quantity && $quantity > $this->max_order_quantity) {
            return false;
        }
        
        return $this->getAvailableQuantity() >= $quantity;
    }

    public function reserveStock($quantity)
    {
        if ($this->track_inventory) {
            $this->reserved_quantity += $quantity;
            $this->save();
        }
    }

    public function releaseStock($quantity)
    {
        if ($this->track_inventory) {
            $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
            $this->save();
        }
    }

    public function reduceStock($quantity)
    {
        if ($this->track_inventory) {
            $this->stock_quantity = max(0, $this->stock_quantity - $quantity);
            $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
            $this->save();
        }
    }

    public function getDiscountPercentage()
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return 0;
        }
        
        return round((($this->compare_price - $this->price) / $this->compare_price) * 100, 2);
    }

    public function getProfitMargin()
    {
        if (!$this->cost || $this->cost <= 0) {
            return null;
        }
        
        return round((($this->price - $this->cost) / $this->price) * 100, 2);
    }

    public function hasAttribute($attributeId)
    {
        return in_array($attributeId, $this->attribute_combination ?? []);
    }

    public function matchesAttributes(array $attributeIds)
    {
        $combination = $this->attribute_combination ?? [];
        
        // Verificar que todos los IDs proporcionados estén en la combinación
        foreach ($attributeIds as $id) {
            if (!in_array($id, $combination)) {
                return false;
            }
        }
        
        // Verificar que la combinación no tenga más atributos que los proporcionados
        return count($combination) === count($attributeIds);
    }
}