<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Actualizar estadísticas del cliente cuando se crea un pedido
        static::created(function ($order) {
            $order->updateCustomerStats();
        });

        // Actualizar estadísticas del cliente cuando se actualiza un pedido
        static::updated(function ($order) {
            $order->updateCustomerStats();
        });

        // Actualizar estadísticas del cliente cuando se elimina un pedido
        static::deleted(function ($order) {
            $order->updateCustomerStats();
        });
    }

    /**
     * Actualizar estadísticas del cliente asociado
     */
    protected function updateCustomerStats()
    {
        $customer = Customer::where('email', $this->customer_email)->first();
        if ($customer) {
            $customer->updateOrderStats();
        }
    }

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'shipping_address', // Dirección de envío separada
        'billing_address',  // Dirección de facturación separada
        'company_name',     // Nombre de empresa para factura
        'nif_cif',          // NIF/CIF para factura
        'status',
        'subtotal',         // Subtotal sin IVA
        'tax_rate',         // Porcentaje de IVA (21%)
        'tax_amount',       // Importe del IVA
        'total_amount',     // Total con IVA
        'notes',
        'approved_at',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getRouteKeyName()
    {
        return 'order_number';
    }

    public static function generateOrderNumber()
    {
        $year = date('Y');
        $prefix = 'ORD-' . $year . '-';

        // Obtener el último número de orden del año actual
        $lastOrder = static::where('order_number', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            // Extraer el número del último pedido
            $lastNumber = (int) substr($lastOrder->order_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'approved' => 'Aprobado',
            'in_production' => 'En Producción',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
