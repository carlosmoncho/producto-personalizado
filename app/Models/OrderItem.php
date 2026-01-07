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
        'extras', // Extras fijos (cliché, etc.) - no dependen de la cantidad
        'selected_size',
        'selected_color',
        'selected_print_colors',
        'design_image',
        'preview_3d', // Captura del modelo 3D personalizado
        'model_3d_config', // Configuración para recrear el modelo 3D (JSON)
        'design_comments',
        'configuration', // Configuración de atributos personalizados (nuevo sistema)
        // Campos de tinta personalizada
        'has_custom_ink',
        'custom_ink_hex',
        'custom_ink_name',
        'custom_ink_pantone',
        'custom_ink_notes',
        'custom_ink_price',
        'custom_inks', // Array de tintas personalizadas [{hex, name?, pantone?}]
    ];

    protected $casts = [
        'selected_print_colors' => 'array',
        'configuration' => 'array', // JSON con configuración de atributos
        'model_3d_config' => 'array', // JSON con configuración del modelo 3D
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'extras' => 'decimal:2',
        // Casts de tinta personalizada
        'has_custom_ink' => 'boolean',
        'custom_ink_price' => 'decimal:2',
        'custom_inks' => 'array', // Array de tintas personalizadas
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

    // ==================== MÉTODOS DE TINTA PERSONALIZADA ====================

    /**
     * Verificar si el item tiene tinta personalizada
     */
    public function hasCustomInk(): bool
    {
        return $this->has_custom_ink ?? false;
    }

    /**
     * Obtener información completa de la tinta personalizada
     */
    public function getCustomInkInfo(): ?array
    {
        if (!$this->hasCustomInk()) {
            return null;
        }

        return [
            'hex' => $this->custom_ink_hex,
            'name' => $this->custom_ink_name,
            'pantone' => $this->custom_ink_pantone,
            'notes' => $this->custom_ink_notes,
            'price' => (float) $this->custom_ink_price,
        ];
    }

    /**
     * Establecer información de tinta personalizada
     */
    public function setCustomInk(string $hex, ?string $name = null, ?string $pantone = null, ?string $notes = null, float $price = 0): self
    {
        $this->has_custom_ink = true;
        $this->custom_ink_hex = $hex;
        $this->custom_ink_name = $name;
        $this->custom_ink_pantone = $pantone;
        $this->custom_ink_notes = $notes;
        $this->custom_ink_price = $price;

        return $this;
    }

    /**
     * Limpiar información de tinta personalizada
     */
    public function clearCustomInk(): self
    {
        $this->has_custom_ink = false;
        $this->custom_ink_hex = null;
        $this->custom_ink_name = null;
        $this->custom_ink_pantone = null;
        $this->custom_ink_notes = null;
        $this->custom_ink_price = 0;

        return $this;
    }

    /**
     * Obtener todos los colores de tinta (predefinidos + personalizados)
     */
    public function getAllInkColors(): array
    {
        $colors = $this->getSelectedPrintColorsWithHex();

        // Usar el nuevo campo custom_inks si existe
        if (!empty($this->custom_inks)) {
            foreach ($this->custom_inks as $ink) {
                $colors[] = [
                    'name' => $ink['name'] ?? 'Color personalizado',
                    'hex_code' => $ink['hex'],
                    'is_custom' => true,
                    'pantone' => $ink['pantone'] ?? null,
                ];
            }
        }
        // Fallback al campo antiguo custom_ink_hex
        elseif ($this->hasCustomInk()) {
            $colors[] = [
                'name' => $this->custom_ink_name ?? 'Color personalizado',
                'hex_code' => $this->custom_ink_hex,
                'is_custom' => true,
                'pantone' => $this->custom_ink_pantone,
            ];
        }

        return $colors;
    }

    /**
     * Obtener tintas personalizadas (nuevo formato array)
     */
    public function getCustomInks(): array
    {
        return $this->custom_inks ?? [];
    }

    /**
     * Establecer múltiples tintas personalizadas
     * @param array $inks Array de tintas [{hex: string, name?: string, pantone?: string}]
     */
    public function setCustomInks(array $inks): self
    {
        $this->custom_inks = $inks;
        $this->has_custom_ink = !empty($inks);

        // También establecer el campo antiguo con el primer color para compatibilidad
        if (!empty($inks)) {
            $this->custom_ink_hex = $inks[0]['hex'] ?? null;
            $this->custom_ink_name = $inks[0]['name'] ?? null;
            $this->custom_ink_pantone = $inks[0]['pantone'] ?? null;
        }

        return $this;
    }
}