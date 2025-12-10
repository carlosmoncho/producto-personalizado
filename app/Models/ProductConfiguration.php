<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'attributes_base',
        'personalization',
        'files',
        'calculated',
        'status',
        'is_valid',
        'validation_errors',
        'expires_at',
    ];

    protected $casts = [
        'attributes_base' => 'array',
        'personalization' => 'array',
        'files' => 'array',
        'calculated' => 'array',
        'validation_errors' => 'array',
        'is_valid' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now())
                     ->orWhereNull('expires_at');
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Métodos de utilidad
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function extendExpiration($days = 7)
    {
        $this->update(['expires_at' => now()->addDays($days)]);
    }

    /**
     * Obtener la selección completa combinando base y personalización
     */
    public function getFullSelection()
    {
        return array_merge(
            $this->attributes_base ?? [],
            $this->personalization ?? []
        );
    }

    /**
     * Verificar si la configuración tiene todos los atributos requeridos
     */
    public function isComplete()
    {
        $required = ['color', 'material', 'size'];
        $base = $this->attributes_base ?? [];

        foreach ($required as $attr) {
            if (empty($base[$attr])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcular precio total de la configuración
     */
    public function calculateTotalPrice()
    {
        $calculated = $this->calculated ?? [];
        return $calculated['pricing']['total_price'] ?? 0;
    }

    /**
     * Obtener resumen de la configuración para mostrar al usuario
     */
    public function getSummary()
    {
        $summary = [];
        $fullSelection = $this->getFullSelection();

        if (!empty($fullSelection)) {
            $attributes = ProductAttribute::whereIn('id', array_values($fullSelection))->get();
            
            foreach ($attributes as $attribute) {
                $summary[$attribute->type] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'value' => $attribute->value,
                    'hex_code' => $attribute->hex_code,
                ];
            }
        }

        return $summary;
    }

    /**
     * Generar estructura de datos para el frontend
     */
    public function toConfigurationArray()
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'status' => $this->status,
            'is_valid' => $this->is_valid,
            'validation_errors' => $this->validation_errors,
            'attributes_base' => $this->attributes_base ?? [],
            'personalization' => $this->personalization ?? [],
            'calculated' => $this->calculated ?? [],
            'summary' => $this->getSummary(),
            'is_complete' => $this->isComplete(),
            'expires_at' => $this->expires_at ? $this->expires_at->toISOString() : null,
        ];
    }

    /**
     * Marcar como completada
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'is_valid' => true,
        ]);
    }

    /**
     * Duplicar configuración (útil para variaciones)
     */
    public function duplicate()
    {
        $copy = $this->replicate();
        $copy->status = 'draft';
        $copy->is_valid = false;
        $copy->expires_at = now()->addDays(7);
        $copy->save();

        return $copy;
    }
}