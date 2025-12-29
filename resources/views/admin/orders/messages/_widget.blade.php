{{-- Widget de mensajes para la vista de pedido --}}
@php
    $messagesCount = $order->messages()->count();
    $unreadCount = $order->unreadMessages()->count();
    $lastMessage = $order->latestMessage;
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>Comunicación con el cliente
        </h6>
        <div>
            @if($messagesCount > 0)
                <span class="badge bg-primary">{{ $messagesCount }}</span>
            @endif
            @if($unreadCount > 0)
                <span class="badge bg-danger">{{ $unreadCount }} nuevos</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($lastMessage)
            <div class="mb-3 p-2 bg-light rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge {{ $lastMessage->isOutgoing() ? 'bg-warning text-dark' : 'bg-info' }}">
                        {{ $lastMessage->isOutgoing() ? 'Último enviado' : 'Último recibido' }}
                    </span>
                    <small class="text-muted">{{ $lastMessage->created_at->diffForHumans() }}</small>
                </div>
                <strong class="d-block">{{ $lastMessage->subject }}</strong>
                <small class="text-muted">{{ Str::limit(strip_tags($lastMessage->body), 100) }}</small>
            </div>
        @else
            <p class="text-muted mb-3 text-center">
                <i class="bi bi-envelope fs-4 d-block mb-2"></i>
                No hay mensajes todavía
            </p>
        @endif

        <a href="{{ route('admin.orders.messages.index', $order) }}" class="btn btn-primary w-100">
            <i class="bi bi-envelope me-2"></i>
            {{ $messagesCount > 0 ? 'Ver conversación' : 'Enviar mensaje' }}
        </a>
    </div>
</div>
