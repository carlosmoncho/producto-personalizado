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
        'active',
        'category_id',
        'subcategory_id',
    ];

    protected $casts = [
        'colors' => 'array',
        'materials' => 'array',
        'sizes' => 'array',
        'print_colors' => 'array',
        'images' => 'array',
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
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
            return Storage::disk('public')->url($image);
        }, $this->images);
    }

    public function getFirstImageUrl()
    {
        $images = $this->getImagesUrls();
        return $images ? $images[0] : asset('images/no-image.png');
    }

    public function getModel3dUrl()
    {
        return $this->model_3d_file 
            ? Storage::disk('public')->url($this->model_3d_file)
            : null;
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
}