@extends('layouts.admin')

@section('title', 'Mensajes - ' . $order->order_number)

@section('content')
<div class="row">
    {{-- Panel izquierdo: Formulario de envío --}}
    <div class="col-lg-5">
        {{-- Info del pedido --}}
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-box me-2"></i>Pedido {{ $order->order_number }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Cliente</small>
                        <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                        <small class="text-muted">{{ $order->customer_email }}</small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">Total</small>
                        <p class="mb-1"><strong class="text-success">{{ number_format($order->total_amount, 2) }} €</strong></p>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'approved' => 'success',
                                'in_production' => 'primary',
                                'shipped' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>Volver al pedido
                    </a>
                </div>
            </div>
        </div>

        {{-- Formulario de nuevo mensaje --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-send me-2"></i>Enviar mensaje al cliente
                </h6>
            </div>
            <div class="card-body">
                {{-- Selector de plantilla --}}
                <div class="mb-3">
                    <label class="form-label">Usar plantilla</label>
                    <select class="form-select form-select-sm" id="templateSelect">
                        <option value="">-- Seleccionar plantilla --</option>
                        @foreach($templates as $key => $template)
                            <option value="{{ $key }}">{{ $template['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <form action="{{ route('admin.orders.messages.store', $order) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="subject" class="form-label">Asunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject" value="{{ old('subject') }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Mensaje <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  id="body" name="body" rows="8" required>{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Variables disponibles: {order_number}, {customer_name}, {total_amount}, {status}
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="attachments" class="form-label">Adjuntos</label>
                        <input type="file" class="form-control form-control-sm @error('attachments.*') is-invalid @enderror"
                               id="attachments" name="attachments[]" multiple>
                        @error('attachments.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Máximo 10MB por archivo</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send me-2"></i>Enviar mensaje
                    </button>
                </form>
            </div>
        </div>

        {{-- Formulario para registrar mensaje entrante --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-envelope me-2"></i>Registrar mensaje del cliente
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Si el cliente ha enviado un email, puedes registrarlo aquí para mantener el historial completo.
                </p>

                <form action="{{ route('admin.orders.messages.store-incoming', $order) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="incoming_subject" class="form-label">Asunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm"
                               id="incoming_subject" name="subject" required>
                    </div>

                    <div class="mb-3">
                        <label for="incoming_body" class="form-label">Mensaje <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm"
                                  id="incoming_body" name="body" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="from_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm"
                                       id="from_name" name="from_name" value="{{ $order->customer_name }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="from_email" class="form-label">Email</label>
                                <input type="email" class="form-control form-control-sm"
                                       id="from_email" name="from_email" value="{{ $order->customer_email }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="incoming_attachments" class="form-label">Adjuntos</label>
                        <input type="file" class="form-control form-control-sm"
                               id="incoming_attachments" name="attachments[]" multiple>
                    </div>

                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-plus-circle me-2"></i>Registrar mensaje
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Panel derecho: Historial de mensajes --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-chat-dots me-2"></i>Historial de comunicación
                </h6>
                <div>
                    <span class="badge bg-primary">{{ $stats['total'] }} mensajes</span>
                    @if($stats['unread'] > 0)
                        <span class="badge bg-danger">{{ $stats['unread'] }} sin leer</span>
                    @endif
                </div>
            </div>
            <div class="card-body" style="max-height: 700px; overflow-y: auto;">
                @forelse($messages as $message)
                    <div class="message-item mb-3 p-3 rounded {{ $message->isOutgoing() ? 'bg-light' : 'bg-info bg-opacity-10' }}"
                         style="border-left: 4px solid {{ $message->isOutgoing() ? '#9a7420' : '#0d6efd' }};">

                        {{-- Header del mensaje --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $message->isOutgoing() ? 'bg-warning text-dark' : 'bg-info' }}">
                                    <i class="bi {{ $message->direction_icon }} me-1"></i>
                                    {{ $message->isOutgoing() ? 'Enviado' : 'Recibido' }}
                                </span>
                                <span class="badge bg-{{ $message->status_color }}">
                                    {{ $message->status_label }}
                                </span>
                                @if($message->hasAttachments())
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-paperclip"></i> {{ $message->attachments_count }}
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>

                        {{-- Info de remitente/destinatario --}}
                        <div class="small text-muted mb-2">
                            @if($message->isOutgoing())
                                <strong>De:</strong> {{ $message->from_name ?? $message->from_email }}
                                @if($message->user)
                                    <span class="text-muted">({{ $message->user->name }})</span>
                                @endif
                                <br>
                                <strong>Para:</strong> {{ $message->to_name }} ({{ $message->to_email }})
                            @else
                                <strong>De:</strong> {{ $message->from_name ?? $message->from_email }}
                            @endif
                        </div>

                        {{-- Asunto --}}
                        <h6 class="mb-2">{{ $message->subject }}</h6>

                        {{-- Cuerpo del mensaje --}}
                        <div class="message-body border rounded p-2 bg-white">
                            {!! $message->body !!}
                        </div>

                        {{-- Adjuntos --}}
                        @if($message->hasAttachments())
                            <div class="mt-2">
                                <small class="text-muted">Adjuntos:</small>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($message->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment['path']) }}"
                                           class="badge bg-light text-dark text-decoration-none"
                                           target="_blank">
                                            <i class="bi bi-file-earmark me-1"></i>
                                            {{ $attachment['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Acciones --}}
                        <div class="mt-2 d-flex gap-2">
                            @if($message->isFailed())
                                <form action="{{ route('admin.orders.messages.retry', [$order, $message]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reintentar
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.orders.messages.destroy', [$order, $message]) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este mensaje?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                            @if($message->error_message)
                                <span class="text-danger small" title="{{ $message->error_message }}">
                                    <i class="bi bi-exclamation-triangle"></i> Error
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-chat-dots fs-1 d-block mb-3"></i>
                        <p>No hay mensajes en este pedido.</p>
                        <p class="small">Envía el primer mensaje al cliente usando el formulario.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('templateSelect');
    const subjectInput = document.getElementById('subject');
    const bodyTextarea = document.getElementById('body');

    templateSelect.addEventListener('change', function() {
        const template = this.value;
        if (!template) return;

        // Fetch template content
        fetch(`{{ route('admin.orders.messages.template', $order) }}?template=${template}`)
            .then(response => response.json())
            .then(data => {
                subjectInput.value = data.subject;
                bodyTextarea.value = data.body;
            })
            .catch(error => {
                console.error('Error loading template:', error);
            });
    });
});
</script>
@endpush
@endsection
