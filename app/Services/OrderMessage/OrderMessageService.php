<?php

namespace App\Services\OrderMessage;

use App\Jobs\SendOrderMessageJob;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderMessageService
{
    /**
     * Enviar un mensaje al cliente
     */
    public function sendMessage(
        Order $order,
        string $subject,
        string $body,
        ?User $sender = null,
        array $attachments = [],
        ?string $inReplyTo = null
    ): OrderMessage {
        $message = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $sender?->id,
            'direction' => 'outgoing',
            'subject' => $this->replaceVariables($subject, $order),
            'body' => $this->replaceVariables($body, $order),
            'body_format' => 'html',
            'from_email' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'to_email' => $order->customer_email,
            'to_name' => $order->customer_name,
            'message_id' => OrderMessage::generateMessageId(),
            'in_reply_to' => $inReplyTo,
            'attachments' => $attachments,
            'status' => 'pending',
        ]);

        // Dispatch job para enviar el email
        SendOrderMessageJob::dispatch($message);

        return $message;
    }

    /**
     * Registrar un mensaje entrante (manual)
     */
    public function registerIncomingMessage(
        Order $order,
        string $subject,
        string $body,
        ?string $fromEmail = null,
        ?string $fromName = null,
        array $attachments = []
    ): OrderMessage {
        return OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => null,
            'direction' => 'incoming',
            'subject' => $subject,
            'body' => $body,
            'body_format' => 'html',
            'from_email' => $fromEmail ?? $order->customer_email,
            'from_name' => $fromName ?? $order->customer_name,
            'to_email' => config('mail.from.address'),
            'to_name' => config('mail.from.name'),
            'message_id' => OrderMessage::generateMessageId(),
            'attachments' => $attachments,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Obtener la conversación completa de un pedido
     */
    public function getConversation(Order $order): Collection
    {
        return $order->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtener mensajes no leídos de un pedido
     */
    public function getUnreadMessages(Order $order): Collection
    {
        return $order->unreadMessages()->get();
    }

    /**
     * Marcar todos los mensajes entrantes como leídos
     */
    public function markAllAsRead(Order $order): int
    {
        return $order->messages()
            ->incoming()
            ->whereNull('read_at')
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
    }

    /**
     * Reenviar un mensaje fallido
     */
    public function retryFailedMessage(OrderMessage $message): bool
    {
        if (!$message->isFailed()) {
            return false;
        }

        $message->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        SendOrderMessageJob::dispatch($message);

        return true;
    }

    /**
     * Guardar adjuntos de un mensaje
     */
    public function saveAttachments(Order $order, array $files): array
    {
        $attachments = [];
        $directory = 'order-attachments/' . $order->id;

        foreach ($files as $file) {
            $filename = Str::uuid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs($directory, $filename, 'public');

            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ];
        }

        return $attachments;
    }

    /**
     * Eliminar adjuntos de un mensaje
     */
    public function deleteAttachments(OrderMessage $message): void
    {
        if (!$message->hasAttachments()) {
            return;
        }

        foreach ($message->attachments as $attachment) {
            Storage::disk('public')->delete($attachment['path']);
        }
    }

    /**
     * Obtener plantillas de mensajes predefinidas
     */
    public function getTemplates(): array
    {
        return [
            'order_received' => [
                'name' => 'Pedido recibido',
                'subject' => 'Hemos recibido tu pedido {order_number}',
                'body' => '<p>Hola {customer_name},</p>
<p>Hemos recibido correctamente tu pedido <strong>{order_number}</strong> por un importe de <strong>{total_amount}</strong>.</p>
<p>Te mantendremos informado sobre el estado de tu pedido.</p>
<p>Gracias por confiar en nosotros.</p>
<p>Un saludo,<br>El equipo de Hostelking</p>',
            ],
            'order_approved' => [
                'name' => 'Pedido aprobado',
                'subject' => 'Tu pedido {order_number} ha sido aprobado',
                'body' => '<p>Hola {customer_name},</p>
<p>Tu pedido <strong>{order_number}</strong> ha sido aprobado y pasamos a prepararlo.</p>
<p>Te avisaremos cuando lo enviemos.</p>
<p>Un saludo,<br>El equipo de Hostelking</p>',
            ],
            'order_in_production' => [
                'name' => 'Pedido en producción',
                'subject' => 'Tu pedido {order_number} está en producción',
                'body' => '<p>Hola {customer_name},</p>
<p>Tu pedido <strong>{order_number}</strong> está siendo fabricado.</p>
<p>El tiempo estimado de producción es de 10-15 días laborables.</p>
<p>Un saludo,<br>El equipo de Hostelking</p>',
            ],
            'order_shipped' => [
                'name' => 'Pedido enviado',
                'subject' => 'Tu pedido {order_number} ha sido enviado',
                'body' => '<p>Hola {customer_name},</p>
<p>Tu pedido <strong>{order_number}</strong> ha sido enviado a la siguiente dirección:</p>
<p>{shipping_address}</p>
<p>Recibirás tu pedido en los próximos días.</p>
<p>Un saludo,<br>El equipo de Hostelking</p>',
            ],
            'order_delivered' => [
                'name' => 'Pedido entregado',
                'subject' => 'Tu pedido {order_number} ha sido entregado',
                'body' => '<p>Hola {customer_name},</p>
<p>Tu pedido <strong>{order_number}</strong> ha sido entregado correctamente.</p>
<p>Esperamos que estés satisfecho con tu compra. Si tienes cualquier pregunta, no dudes en contactarnos.</p>
<p>Un saludo,<br>El equipo de Hostelking</p>',
            ],
            'request_info' => [
                'name' => 'Solicitar información',
                'subject' => 'Necesitamos información sobre tu pedido {order_number}',
                'body' => '<p>Hola {customer_name},</p>
<p>Para poder procesar tu pedido <strong>{order_number}</strong>, necesitamos que nos proporciones la siguiente información:</p>
<ul>
<li>[Especificar información necesaria]</li>
</ul>
<p>Por favor, responde a este email con los datos solicitados.</p>
<p>Gracias,<br>El equipo de Hostelking</p>',
            ],
            'custom' => [
                'name' => 'Mensaje personalizado',
                'subject' => 'Sobre tu pedido {order_number}',
                'body' => '<p>Hola {customer_name},</p>
<p>[Tu mensaje aquí]</p>
<p>Un saludo,<br>El equipo de Hostelking</p>',
            ],
        ];
    }

    /**
     * Obtener una plantilla específica
     */
    public function getTemplate(string $key): ?array
    {
        return $this->getTemplates()[$key] ?? null;
    }

    /**
     * Reemplazar variables en el contenido
     */
    public function replaceVariables(string $content, Order $order): string
    {
        $variables = [
            '{order_number}' => $order->order_number,
            '{customer_name}' => $order->customer_name,
            '{customer_email}' => $order->customer_email,
            '{total_amount}' => number_format($order->total_amount, 2, ',', '.') . ' €',
            '{subtotal}' => number_format($order->subtotal ?? $order->total_amount, 2, ',', '.') . ' €',
            '{shipping_address}' => $order->shipping_address ?? $order->customer_address ?? 'No especificada',
            '{billing_address}' => $order->billing_address ?? $order->customer_address ?? 'No especificada',
            '{status}' => $order->status_label,
            '{created_date}' => $order->created_at->format('d/m/Y'),
            '{company_name}' => $order->company_name ?? '',
            '{nif_cif}' => $order->nif_cif ?? '',
        ];

        return str_replace(
            array_keys($variables),
            array_values($variables),
            $content
        );
    }

    /**
     * Obtener estadísticas de mensajes de un pedido
     */
    public function getMessageStats(Order $order): array
    {
        $messages = $order->messages;

        return [
            'total' => $messages->count(),
            'outgoing' => $messages->where('direction', 'outgoing')->count(),
            'incoming' => $messages->where('direction', 'incoming')->count(),
            'unread' => $messages->where('direction', 'incoming')->whereNull('read_at')->count(),
            'failed' => $messages->where('status', 'failed')->count(),
            'last_message_at' => $messages->last()?->created_at,
        ];
    }
}
