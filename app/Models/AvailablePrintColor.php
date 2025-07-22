<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailablePrintColor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hex_code',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Verificar si el color está siendo usado por algún producto
     */
    public function isInUse()
    {
        return Product::whereJsonContains('print_colors', $this->name)->exists();
    }

    /**
     * Obtener productos que usan este color
     */
    public function products()
    {
        return Product::whereJsonContains('print_colors', $this->name)->get();
    }
}