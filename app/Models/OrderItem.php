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
        'selected_color',
        'selected_print_colors',
        'design_image',
        'design_comments',
    ];

    protected $casts = [
        'selected_print_colors' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
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
        return $this->design_image 
            ? Storage::disk('public')->url($this->design_image)
            : null;
    }

    public function deleteDesignImage()
    {
        if ($this->design_image && Storage::disk('public')->exists($this->design_image)) {
            Storage::disk('public')->delete($this->design_image);
        }
    }

    // Helper para obtener el color seleccionado con su código hex
    public function getSelectedColorWithHex()
    {
        if (!$this->selected_color) {
            return null;
        }

        $availableColor = \App\Models\AvailableColor::where('name', $this->selected_color)->first();
        
        return $availableColor ? [
            'name' => $availableColor->name,
            'hex_code' => $availableColor->hex_code
        ] : ['name' => $this->selected_color, 'hex_code' => '#000000'];
    }

    // Helper para obtener los colores de impresión con sus códigos hex
    public function getSelectedPrintColorsWithHex()
    {
        if (!$this->selected_print_colors || !is_array($this->selected_print_colors)) {
            return [];
        }

        $printColors = \App\Models\AvailablePrintColor::whereIn('name', $this->selected_print_colors)->get();
        
        return $printColors->map(function($color) {
            return [
                'name' => $color->name,
                'hex_code' => $color->hex_code
            ];
        })->toArray();
    }
}