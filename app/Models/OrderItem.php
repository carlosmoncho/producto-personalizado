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
        'preview_3d', // Captura del modelo 3D personalizado
        'model_3d_config', // Configuración para recrear el modelo 3D (JSON)
        'design_comments',
        'configuration', // Configuración de atributos personalizados (nuevo sistema)
    ];

    protected $casts = [
        'selected_print_colors' => 'array',
        'configuration' => 'array', // JSON con configuración de atributos
        'model_3d_config' => 'array', // JSON con configuración del modelo 3D
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
        if (!$this->design_image) {
            return null;
        }

        // Si la imagen ya está en base64, devolverla directamente
        if (str_starts_with($this->design_image, 'data:image')) {
            return $this->design_image;
        }

        // Si es una ruta de archivo, devolver la URL del storage
        return Storage::disk(config('filesystems.default', 'public'))->url($this->design_image);
    }

    public function deleteDesignImage()
    {
        // Si no hay imagen de diseño, no hacer nada
        if (!$this->design_image) {
            return;
        }

        // Si la imagen es base64, no hay archivo que eliminar
        if (str_starts_with($this->design_image, 'data:image')) {
            return;
        }

        // Si es una URL completa de S3, extraer solo el path relativo
        $path = $this->design_image;
        if (str_contains($path, 's3.') && str_contains($path, 'amazonaws.com')) {
            // Extraer el path después del bucket
            $parsed = parse_url($path);
            $path = ltrim($parsed['path'] ?? '', '/');
        }

        // Intentar eliminar del disco configurado (S3 o local)
        $disk = config('filesystems.default', 'public');
        $storage = Storage::disk($disk);

        try {
            if ($storage->exists($path)) {
                $storage->delete($path);
            }
        } catch (\Exception $e) {
            // Log del error pero no fallar la operación
            \Log::warning("Error eliminando imagen de diseño del pedido: {$path}", [
                'error' => $e->getMessage(),
                'disk' => $disk,
                'order_item_id' => $this->id
            ]);
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