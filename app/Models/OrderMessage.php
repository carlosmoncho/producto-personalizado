<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'direction',
        'subject',
        'body',
        'body_format',
        'from_email',
        'from_name',
        'to_email',
        'to_name',
        'message_id',
        'in_reply_to',
        'attachments',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Relación con el pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación con el usuario (admin que envió el mensaje)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para mensajes salientes (admin -> cliente)
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope para mensajes entrantes (cliente -> admin)
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope para mensajes pendientes de envío
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para mensajes enviados
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope para mensajes fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Generar Message-ID único para threading de emails
     * Sin los brackets <> porque Symfony los añade automáticamente
     */
    public static function generateMessageId(): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'hostelking.es';
        return sprintf('%s.%s@%s', Str::uuid(), time(), $host);
    }

    /**
     * Verificar si el mensaje es saliente
     */
    public function isOutgoing(): bool
    {
        return $this->direction === 'outgoing';
    }

    /**
     * Verificar si el mensaje es entrante
     */
    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }

    /**
     * Verificar si el mensaje fue enviado exitosamente
     */
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'read']);
    }

    /**
     * Verificar si el mensaje falló
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Obtener etiqueta del estado en español
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'sent' => 'Enviado',
            'delivered' => 'Entregado',
            'failed' => 'Fallido',
            'read' => 'Leído',
            default => $this->status,
        };
    }

    /**
     * Obtener color del badge según estado
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'sent' => 'info',
            'delivered' => 'success',
            'failed' => 'danger',
            'read' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Obtener icono según dirección
     */
    public function getDirectionIconAttribute(): string
    {
        return $this->isOutgoing() ? 'bi-send' : 'bi-envelope';
    }

    /**
     * Obtener etiqueta de dirección
     */
    public function getDirectionLabelAttribute(): string
    {
        return $this->isOutgoing() ? 'Enviado al cliente' : 'Recibido del cliente';
    }

    /**
     * Verificar si tiene adjuntos
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Obtener número de adjuntos
     */
    public function getAttachmentsCountAttribute(): int
    {
        return count($this->attachments ?? []);
    }

    /**
     * Marcar como leído
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Obtener extracto del cuerpo del mensaje
     */
    public function getBodyExcerptAttribute(): string
    {
        $text = strip_tags($this->body);
        return Str::limit($text, 150);
    }
}
