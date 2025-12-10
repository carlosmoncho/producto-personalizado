<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'notes',
        'active',
        'last_order_at',
        'total_orders_amount',
        'total_orders_count'
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_order_at' => 'datetime',
        'total_orders_amount' => 'decimal:2',
        'total_orders_count' => 'integer'
    ];

    // Relación con pedidos
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_email', 'email');
    }

    // Relación con direcciones
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // Obtener dirección de envío por defecto
    public function defaultShippingAddress()
    {
        return $this->hasOne(Address::class)->where('type', 'shipping')->where('is_default', true);
    }

    // Obtener dirección de facturación por defecto
    public function defaultBillingAddress()
    {
        return $this->hasOne(Address::class)->where('type', 'billing')->where('is_default', true);
    }

    // Scope para clientes activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('tax_id', 'like', "%{$search}%");
        });
    }

    // Accessor para nombre completo con empresa
    public function getFullNameAttribute()
    {
        return $this->company ? "{$this->name} ({$this->company})" : $this->name;
    }

    // Accessor para dirección completa
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    // Método para actualizar estadísticas de pedidos
    public function updateOrderStats()
    {
        $orders = $this->orders;
        
        $this->update([
            'total_orders_count' => $orders->count(),
            'total_orders_amount' => $orders->sum('total_amount'),
            'last_order_at' => $orders->max('created_at')
        ]);
    }
}
