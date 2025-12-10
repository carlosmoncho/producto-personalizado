<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPricing extends Model
{
    use HasFactory;

    // AGREGAR ESTA LÍNEA para especificar el nombre de la tabla
    protected $table = 'product_pricing';

    protected $fillable = [
        'product_id',
        'quantity_from',
        'quantity_to',
        'price',
        'unit_price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'unit_price' => 'decimal:5', // 5 decimales para soportar precios por millar
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}