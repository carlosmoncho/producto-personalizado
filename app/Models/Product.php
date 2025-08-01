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
        'material',
        'sizes',
        'printing_system',
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
}