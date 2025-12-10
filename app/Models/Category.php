<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the image URL accessor
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image
                ? '/api/storage/' . $this->image
                : null,
        );
    }

    public function getImageUrl()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return '/api/storage/' . $this->image;
        }
        return null;
    }

    public function deleteImage()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
