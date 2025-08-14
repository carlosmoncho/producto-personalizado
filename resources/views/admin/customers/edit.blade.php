@extends('layouts.admin')

@section('title', 'Editar Cliente: ' . $customer->name)

@section('content')
<!-- Header con Breadcrumb -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.customers.index') }}">Clientes</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-pencil-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">Editar Cliente</h2>
                <small class="text-muted">Modifica los datos del cliente {{ $customer->name }}</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Cliente
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf
            @method('PUT')
            
            <!-- Información Personal -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-person-vcard-fill"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Información Personal</h5>
                            <small>Datos básicos del cliente</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $customer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">Empresa</label>
                            <input type="text" class="form-control @error('company') is-invalid @enderror" 
                                   id="company" name="company" value="{{ old('company', $customer->company) }}">
                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tax_id" class="form-label">NIF/CIF</label>
                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                   id="tax_id" name="tax_id" value="{{ old('tax_id', $customer->tax_id) }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">País</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country', $customer->country) }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dirección -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Dirección</h5>
                            <small>Datos de ubicación del cliente</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">Ciudad</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city', $customer->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="postal_code" class="form-label">Código Postal</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                   id="postal_code" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información Adicional -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Información Adicional</h5>
                            <small>Estado y notas del cliente</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="active" class="form-label">Estado</label>
                            <select class="form-select @error('active') is-invalid @enderror" id="active" name="active">
                                <option value="1" {{ old('active', $customer->active) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('active', $customer->active) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Información adicional sobre el cliente">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
    
    <!-- Información del Cliente -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Estadísticas</h5>
                        <small>Resumen de actividad</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 mb-0 text-primary">{{ $customer->total_orders_count }}</div>
                            <small class="text-muted">Total Pedidos</small>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 mb-0 text-success">€{{ number_format($customer->total_orders_amount, 2) }}</div>
                            <small class="text-muted">Total Gastado</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="h6 mb-0 text-info">
                                @if($customer->last_order_at)
                                    {{ $customer->last_order_at->format('d/m/Y') }}
                                @else
                                    Nunca
                                @endif
                            </div>
                            <small class="text-muted">Último Pedido</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <small class="text-muted">Cliente desde {{ $customer->created_at->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection