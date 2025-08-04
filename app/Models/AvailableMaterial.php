<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Verificar si el material está siendo usado por algún producto
     */
    public function isInUse()
    {
        return Product::whereJsonContains('materials', $this->name)->exists();
    }

    /**
     * Obtener productos que usan este material
     */
    public function products()
    {
        return Product::whereJsonContains('materials', $this->name)->get();
    }
}