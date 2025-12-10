@extends('layouts.admin')

@section('title', 'Editar Pedido')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Pedido: {{ $order->order_number }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                       id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                       id="customer_email" name="customer_email" value="{{ old('customer_email', $order->customer_email) }}" required>
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="customer_phone" class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" 
                               id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <h6 class="text-primary"><i class="bi bi-geo-alt-fill me-2"></i>Direcciones</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">Dirección de Envío</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                              id="shipping_address" name="shipping_address" rows="3">{{ old('shipping_address', $order->shipping_address) }}</textarea>
                                    @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="billing_address" class="form-label">Dirección de Facturación</label>
                                    <textarea class="form-control @error('billing_address') is-invalid @enderror"
                                              id="billing_address" name="billing_address" rows="3">{{ old('billing_address', $order->billing_address) }}</textarea>
                                    @error('billing_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($order->customer_address && !$order->shipping_address && !$order->billing_address)
                            <div class="alert alert-info">
                                <small><strong>Dirección original:</strong> {{ $order->customer_address }}</small>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Actualizar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
