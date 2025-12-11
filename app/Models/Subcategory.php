<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'category_id',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrl()
    {
        if ($this->image && Storage::disk(config('filesystems.default', 'public'))->exists($this->image)) {
            return Storage::disk(config('filesystems.default', 'public'))->url($this->image);
        }
        return null;
    }

    public function deleteImage()
    {
        if ($this->image && Storage::disk(config('filesystems.default', 'public'))->exists($this->image)) {
            Storage::disk(config('filesystems.default', 'public'))->delete($this->image);
        }
    }
}
