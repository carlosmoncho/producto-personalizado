<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Verificar si el tamaño está siendo usado por algún producto
     */
    public function isInUse()
    {
        return Product::whereJsonContains('sizes', $this->name)->exists();
    }

    /**
     * Obtener productos que usan este tamaño
     */
    public function products()
    {
        return Product::whereJsonContains('sizes', $this->name)->get();
    }
}