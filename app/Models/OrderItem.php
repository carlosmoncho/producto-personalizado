<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'selected_size',
        'design_image',
        'design_comments',
        'custom_field_values'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'custom_field_values' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getDesignImageUrl()
    {
        if ($this->design_image && Storage::disk('public')->exists($this->design_image)) {
            return Storage::disk('public')->url($this->design_image);
        }
        return null;
    }

    public function deleteDesignImage()
    {
        if ($this->design_image && Storage::disk('public')->exists($this->design_image)) {
            Storage::disk('public')->delete($this->design_image);
        }
    }
}
