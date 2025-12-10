<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'name',
        'phone',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relación con el cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope para direcciones de envío
     */
    public function scopeShipping($query)
    {
        return $query->where('type', 'shipping');
    }

    /**
     * Scope para direcciones de facturación
     */
    public function scopeBilling($query)
    {
        return $query->where('type', 'billing');
    }

    /**
     * Scope para direcciones por defecto
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Obtener la dirección completa formateada
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->postal_code . ' ' . $this->city,
            $this->state,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Obtener el label de la dirección
     */
    public function getLabelAttribute(): string
    {
        $type = $this->type === 'shipping' ? 'Envío' : 'Facturación';
        $default = $this->is_default ? ' (Por defecto)' : '';
        return "{$type}: {$this->city}{$default}";
    }
}
