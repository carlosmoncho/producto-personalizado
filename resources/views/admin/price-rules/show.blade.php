@extends('layouts.admin')

@section('title', 'Ver Regla de Precios')

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
                    <a href="{{ route('admin.price-rules.index') }}">Reglas de Precios</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $priceRule->name }}</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-info text-white rounded me-3">
                <i class="bi bi-eye"></i>
            </div>
            <div>
                <h2 class="mb-0">{{ $priceRule->name }}</h2>
                <small class="text-muted">
                    @if($priceRule->active)
                        <span class="badge bg-success">Activa</span>
                    @else
                        <span class="badge bg-secondary">Inactiva</span>
                    @endif
                    
                    @if($priceRule->valid_from && $priceRule->valid_from > now())
                        <span class="badge bg-warning ms-1">Pendiente</span>
                    @elseif($priceRule->valid_until && $priceRule->valid_until < now())
                        <span class="badge bg-danger ms-1">Expirada</span>
                    @endif
                </small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.price-rules.edit', $priceRule) }}" class="btn btn-warning">
            <i class="bi bi-pencil-square me-2"></i>Editar
        </a>
        <button type="button" class="btn btn-info btn-duplicate" data-rule-id="{{ $priceRule->id }}">
            <i class="bi bi-files me-2"></i>Duplicar
        </button>
        <a href="{{ route('admin.price-rules.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Columna principal -->
    <div class="col-lg-8">
        <!-- Información básica -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Información de la Regla</h5>
                        <small>Detalles y configuración básica</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre</label>
                            <div class="fw-medium">{{ $priceRule->name }}</div>
                        </div>
                        
                        @if($priceRule->description)
                        <div class="mb-3">
                            <label class="form-label text-muted">Descripción</label>
                            <div>{{ $priceRule->description }}</div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Prioridad</label>
                            <div>
                                <span class="badge bg-secondary fs-6">{{ $priceRule->priority }}</span>
                                <small class="text-muted ms-2">
                                    @if($priceRule->priority >= 80)
                                        Muy alta
                                    @elseif($priceRule->priority >= 60)
                                        Alta
                                    @elseif($priceRule->priority >= 40)
                                        Media
                                    @elseif($priceRule->priority >= 20)
                                        Baja
                                    @else
                                        Muy baja
                                    @endif
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Estado</label>
                            <div>
                                @if($priceRule->active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Activa
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-pause-circle me-1"></i>Inactiva
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de la regla -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Configuración</h5>
                        <small>Tipo de regla y acción a ejecutar</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Tipo de Regla</label>
                            <div>
                                @php
                                    $typeLabels = [
                                        'combination' => ['Combinación de Atributos', 'bg-primary'],
                                        'volume' => ['Descuento por Volumen', 'bg-success'],
                                        'attribute_specific' => ['Atributo Específico', 'bg-info'],
                                        'conditional' => ['Condicional', 'bg-warning']
                                    ];
                                    $typeData = $typeLabels[$priceRule->rule_type] ?? ['Desconocido', 'bg-secondary'];
                                @endphp
                                <span class="badge {{ $typeData[1] }} fs-6">{{ $typeData[0] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Acción</label>
                            <div>
                                @php
                                    $actionLabels = [
                                        'add_fixed' => ['Sumar €' . number_format($priceRule->action_value, 2), 'text-success'],
                                        'add_percentage' => ['Sumar ' . $priceRule->action_value . '%', 'text-success'],
                                        'multiply' => ['Multiplicar x' . $priceRule->action_value, 'text-info'],
                                        'set_fixed' => ['Fijar €' . number_format($priceRule->action_value, 2), 'text-primary'],
                                        'set_percentage' => ['Fijar ' . $priceRule->action_value . '%', 'text-primary']
                                    ];
                                    $actionData = $actionLabels[$priceRule->action_type] ?? ['Desconocido', 'text-secondary'];
                                @endphp
                                <span class="fw-medium fs-5 {{ $actionData[1] }}">{{ $actionData[0] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condiciones -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-funnel"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Condiciones</h5>
                        <small>Criterios para aplicar la regla</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($priceRule->conditions)
                    @switch($priceRule->rule_type)
                        @case('combination')
                            @if(isset($priceRule->conditions['attributes']) && count($priceRule->conditions['attributes']) > 0)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Atributos Requeridos</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($priceRule->conditions['attributes'] as $attr)
                                            @php
                                                $attribute = \App\Models\ProductAttribute::find($attr['id'] ?? null);
                                            @endphp
                                            @if($attribute)
                                                <span class="badge bg-light text-dark border">
                                                    <strong>{{ ucfirst($attribute->type) }}:</strong> 
                                                    {{ $attribute->name }}
                                                    @if($attribute->value)
                                                        ({{ $attribute->value }})
                                                    @endif
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">No se han definido combinaciones de atributos</div>
                            @endif
                            @break
                            
                        @case('volume')
                            <div class="mb-3">
                                <label class="form-label text-muted">Cantidad Mínima</label>
                                <div class="fs-5">
                                    <i class="bi bi-hash text-primary"></i>
                                    <span class="fw-medium">{{ number_format($priceRule->conditions['volume_min'] ?? 0) }}</span>
                                    unidades
                                </div>
                            </div>
                            @break
                            
                        @case('attribute_specific')
                            @php
                                $attribute = \App\Models\ProductAttribute::find($priceRule->conditions['attribute_id'] ?? null);
                            @endphp
                            <div class="mb-3">
                                <label class="form-label text-muted">Atributo Específico</label>
                                <div>
                                    @if($attribute)
                                        <span class="badge bg-info text-white fs-6">
                                            <strong>{{ ucfirst($attribute->type) }}:</strong> 
                                            {{ $attribute->name }}
                                            @if($attribute->value)
                                                ({{ $attribute->value }})
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-danger">Atributo no encontrado</span>
                                    @endif
                                </div>
                            </div>
                            @break
                            
                        @case('conditional')
                            <div class="mb-3">
                                <label class="form-label text-muted">Condiciones Personalizadas</label>
                                <div class="bg-light p-3 rounded">
                                    <pre class="mb-0"><code>{{ json_encode($priceRule->conditions['custom'] ?? [], JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                            @break
                    @endswitch
                @else
                    <div class="text-muted">No se han definido condiciones</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Columna lateral -->
    <div class="col-lg-4">
        <!-- Alcance -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-target"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Alcance</h5>
                        <small>Dónde se aplica la regla</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Aplicación</label>
                    <div>
                        @if($priceRule->product)
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box-seam text-primary me-2"></i>
                                <div>
                                    <div class="fw-medium">{{ $priceRule->product->name }}</div>
                                    <small class="text-muted">Producto específico</small>
                                </div>
                            </div>
                        @elseif($priceRule->category)
                            <div class="d-flex align-items-center">
                                <i class="bi bi-folder text-warning me-2"></i>
                                <div>
                                    <div class="fw-medium">{{ $priceRule->category->name }}</div>
                                    <small class="text-muted">Categoría completa</small>
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center">
                                <i class="bi bi-globe text-success me-2"></i>
                                <div>
                                    <div class="fw-medium">Global</div>
                                    <small class="text-muted">Todos los productos</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($priceRule->quantity_min || $priceRule->quantity_max)
                <div class="mb-3">
                    <label class="form-label text-muted">Rango de Cantidad</label>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-hash text-info me-2"></i>
                        <span class="fw-medium">
                            {{ $priceRule->quantity_min ? number_format($priceRule->quantity_min) : '1' }} 
                            - 
                            {{ $priceRule->quantity_max ? number_format($priceRule->quantity_max) : '∞' }}
                        </span>
                        <small class="text-muted ms-1">unidades</small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Vigencia -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Vigencia</h5>
                        <small>Período de validez</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($priceRule->valid_from || $priceRule->valid_until)
                    @if($priceRule->valid_from)
                    <div class="mb-3">
                        <label class="form-label text-muted">Válida desde</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-check text-success me-2"></i>
                            <span class="fw-medium">{{ $priceRule->valid_from->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    @endif

                    @if($priceRule->valid_until)
                    <div class="mb-3">
                        <label class="form-label text-muted">Válida hasta</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-x text-danger me-2"></i>
                            <span class="fw-medium">{{ $priceRule->valid_until->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- Estado actual -->
                    <div class="alert alert-sm
                        @if($priceRule->valid_from && $priceRule->valid_from > now())
                            alert-warning
                        @elseif($priceRule->valid_until && $priceRule->valid_until < now())
                            alert-danger
                        @else
                            alert-success
                        @endif
                    ">
                        <i class="bi bi-info-circle me-2"></i>
                        @if($priceRule->valid_from && $priceRule->valid_from > now())
                            La regla estará activa desde el {{ $priceRule->valid_from->format('d/m/Y') }}
                        @elseif($priceRule->valid_until && $priceRule->valid_until < now())
                            La regla expiró el {{ $priceRule->valid_until->format('d/m/Y') }}
                        @else
                            La regla está vigente actualmente
                        @endif
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="bi bi-infinity display-6"></i>
                        <div class="mt-2">Sin límite de tiempo</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Metadatos -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Información del Sistema</h5>
                        <small>Fechas de creación y modificación</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Creada</label>
                    <div class="small">{{ $priceRule->created_at->format('d/m/Y H:i') }}</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Última modificación</label>
                    <div class="small">{{ $priceRule->updated_at->format('d/m/Y H:i') }}</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Orden de aplicación</label>
                    <div class="small">{{ $priceRule->sort_order }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botón de duplicar
    const duplicateButton = document.querySelector('.btn-duplicate');
    if (duplicateButton) {
        duplicateButton.addEventListener('click', function() {
            const ruleId = this.dataset.ruleId;
            
            if (confirm('¿Desea duplicar esta regla de precio?')) {
                // Crear formulario para duplicar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/price-rules/${ruleId}/duplicate`;
                
                // Agregar token CSRF
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>
@endpush