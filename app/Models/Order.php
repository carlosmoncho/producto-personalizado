<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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
        'total_amount',
        'notes',
        'approved_at',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
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
