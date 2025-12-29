<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Services\OrderMessage\OrderMessageService;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
    protected OrderMessageService $messageService;

    public function __construct()
    {
        $this->messageService = new OrderMessageService();
    }

    /**
     * Mostrar historial de mensajes del pedido
     */
    public function index(Order $order)
    {
        $messages = $this->messageService->getConversation($order);
        $stats = $this->messageService->getMessageStats($order);
        $templates = $this->messageService->getTemplates();

        // Marcar mensajes entrantes como leídos
        $this->messageService->markAllAsRead($order);

        $breadcrumbs = [
            ['name' => 'Pedidos', 'url' => route('admin.orders.index')],
            ['name' => $order->order_number, 'url' => route('admin.orders.show', $order)],
            ['name' => 'Mensajes', 'url' => '#'],
        ];

        return view('admin.orders.messages.index', compact(
            'order',
            'messages',
            'stats',
            'templates',
            'breadcrumbs'
        ));
    }

    /**
     * Enviar un nuevo mensaje al cliente
     */
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max por archivo
        ]);

        $attachments = [];

        // Procesar adjuntos si los hay
        if ($request->hasFile('attachments')) {
            $attachments = $this->messageService->saveAttachments(
                $order,
                $request->file('attachments')
            );
        }

        // Enviar el mensaje
        $message = $this->messageService->sendMessage(
            $order,
            $validated['subject'],
            $validated['body'],
            auth()->user(),
            $attachments
        );

        return redirect()
            ->route('admin.orders.messages.index', $order)
            ->with('success', 'Mensaje enviado correctamente.');
    }

    /**
     * Registrar un mensaje entrante manualmente
     */
    public function storeIncoming(Request $request, Order $order)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $attachments = [];

        if ($request->hasFile('attachments')) {
            $attachments = $this->messageService->saveAttachments(
                $order,
                $request->file('attachments')
            );
        }

        $this->messageService->registerIncomingMessage(
            $order,
            $validated['subject'],
            $validated['body'],
            $validated['from_email'] ?? null,
            $validated['from_name'] ?? null,
            $attachments
        );

        return redirect()
            ->route('admin.orders.messages.index', $order)
            ->with('success', 'Mensaje del cliente registrado correctamente.');
    }

    /**
     * Obtener una plantilla de mensaje
     */
    public function getTemplate(Request $request, Order $order)
    {
        $templateKey = $request->get('template');
        $template = $this->messageService->getTemplate($templateKey);

        if (!$template) {
            return response()->json(['error' => 'Plantilla no encontrada'], 404);
        }

        // Reemplazar variables
        $subject = $this->messageService->replaceVariables($template['subject'], $order);
        $body = $this->messageService->replaceVariables($template['body'], $order);

        return response()->json([
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    /**
     * Reintentar envío de un mensaje fallido
     */
    public function retry(Order $order, OrderMessage $message)
    {
        // Verificar que el mensaje pertenece al pedido
        if ($message->order_id !== $order->id) {
            abort(404);
        }

        if (!$message->isFailed()) {
            return redirect()
                ->route('admin.orders.messages.index', $order)
                ->with('error', 'Solo se pueden reintentar mensajes fallidos.');
        }

        $this->messageService->retryFailedMessage($message);

        return redirect()
            ->route('admin.orders.messages.index', $order)
            ->with('success', 'Reintentando envío del mensaje...');
    }

    /**
     * Eliminar un mensaje
     */
    public function destroy(Order $order, OrderMessage $message)
    {
        // Verificar que el mensaje pertenece al pedido
        if ($message->order_id !== $order->id) {
            abort(404);
        }

        // Eliminar adjuntos
        $this->messageService->deleteAttachments($message);

        $message->delete();

        return redirect()
            ->route('admin.orders.messages.index', $order)
            ->with('success', 'Mensaje eliminado correctamente.');
    }

    /**
     * Ver un mensaje específico
     */
    public function show(Order $order, OrderMessage $message)
    {
        if ($message->order_id !== $order->id) {
            abort(404);
        }

        // Marcar como leído si es entrante
        if ($message->isIncoming()) {
            $message->markAsRead();
        }

        return response()->json([
            'id' => $message->id,
            'direction' => $message->direction,
            'direction_label' => $message->direction_label,
            'subject' => $message->subject,
            'body' => $message->body,
            'from_email' => $message->from_email,
            'from_name' => $message->from_name,
            'to_email' => $message->to_email,
            'to_name' => $message->to_name,
            'status' => $message->status,
            'status_label' => $message->status_label,
            'attachments' => $message->attachments,
            'created_at' => $message->created_at->format('d/m/Y H:i'),
            'sent_at' => $message->sent_at?->format('d/m/Y H:i'),
            'user' => $message->user?->name,
        ]);
    }
}
