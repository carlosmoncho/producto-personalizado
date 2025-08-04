<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_colors',
        'min_units',
        'price_per_unit',
        'description',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
        'total_colors' => 'integer',
        'min_units' => 'integer',
        'price_per_unit' => 'decimal:2'
    ];

    /**
     * Relación muchos a muchos con productos
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_printing_system')
                    ->withTimestamps();
    }

    /**
     * Verificar si está siendo usado
     */
    public function isInUse()
    {
        return $this->products()->exists();
    }
}