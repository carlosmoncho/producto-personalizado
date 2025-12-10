<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AttributeGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'sort_order',
        'is_required',
        'allow_multiple',
        'affects_price',
        'affects_stock',
        'show_in_filter',
        'active'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'allow_multiple' => 'boolean',
        'affects_price' => 'boolean',
        'affects_stock' => 'boolean',
        'show_in_filter' => 'boolean',
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
        
        static::updating(function ($model) {
            if ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'attribute_group_id');
    }

    public function activeAttributes()
    {
        return $this->attributes()->where('active', true)->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->withPivot([
                'product_attribute_id',
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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeShowInFilter($query)
    {
        return $query->where('show_in_filter', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getAttributeValues($productId = null)
    {
        $query = $this->attributes()->active();
        
        if ($productId) {
            $query->whereHas('productAttributeValues', function($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->where('is_available', true);
            });
        }
        
        return $query->get();
    }

    public function getDefaultAttribute($productId)
    {
        return $this->attributes()
            ->whereHas('productAttributeValues', function($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->where('is_default', true);
            })
            ->first();
    }

    public function isColorGroup()
    {
        return in_array($this->type, ['color', 'colour']);
    }

    public function isSizeGroup()
    {
        return in_array($this->type, ['size', 'dimension']);
    }

    public function isMaterialGroup()
    {
        return in_array($this->type, ['material', 'fabric']);
    }

    public function isWeightGroup()
    {
        return in_array($this->type, ['weight', 'gramaje']);
    }

    public function isInkGroup()
    {
        return in_array($this->type, ['ink', 'tinta', 'tintas']);
    }

    public function isInkColorGroup()
    {
        return in_array($this->type, ['ink_color', 'color_tinta', 'colores_tintas']);
    }
}