@extends('layouts.admin')

@section('title', 'Ver Dependencia de Atributos')

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
                    <a href="{{ route('admin.attribute-dependencies.index') }}">Dependencias de Atributos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $attributeDependency->parentAttribute->name }} → {{ $attributeDependency->dependentAttribute->name }}
                </li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-info text-white rounded me-3">
                <i class="bi bi-eye"></i>
            </div>
            <div>
                <h2 class="mb-0">Detalle de Dependencia</h2>
                <small class="text-muted">
                    <span class="badge bg-secondary">Prioridad {{ $attributeDependency->priority ?? 0 }}</span>
                    @if($attributeDependency->auto_select)
                        <span class="badge bg-primary ms-1">Auto-selección</span>
                    @endif
                    @if($attributeDependency->reset_dependents)
                        <span class="badge bg-warning ms-1">Reset dependientes</span>
                    @endif
                </small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attribute-dependencies.edit', $attributeDependency) }}" class="btn btn-warning">
            <i class="bi bi-pencil-square me-2"></i>Editar
        </a>
        <button type="button" class="btn btn-info btn-duplicate" data-dependency-id="{{ $attributeDependency->id }}">
            <i class="bi bi-files me-2"></i>Duplicar
        </button>
        <a href="{{ route('admin.attribute-dependencies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Columna principal -->
    <div class="col-lg-8">
        <!-- Información de la dependencia -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Relación de Dependencia</h5>
                        <small>Visualización de cómo un atributo afecta al otro</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <!-- Atributo padre -->
                        <div class="text-center p-4 border rounded bg-light">
                            @php
                                $typeColors = [
                                    'color' => 'text-danger',
                                    'material' => 'text-warning',
                                    'size' => 'text-info',
                                    'ink' => 'text-primary',
                                    'quantity' => 'text-success',
                                    'system' => 'text-secondary'
                                ];
                                $parentColor = $typeColors[$attributeDependency->parentAttribute->type] ?? 'text-muted';
                                $dependentColor = $typeColors[$attributeDependency->dependentAttribute->type] ?? 'text-muted';
                            @endphp
                            <div class="icon-square bg-white rounded mx-auto mb-3" style="width: 64px; height: 64px; font-size: 24px;">
                                <i class="bi bi-circle-fill {{ $parentColor }}"></i>
                            </div>
                            <h6 class="fw-bold">Atributo Padre</h6>
                            <div class="fw-medium">{{ $attributeDependency->parentAttribute->name }}</div>
                            <div class="badge bg-secondary mt-2">
                                {{ ucfirst($attributeDependency->parentAttribute->type) }}
                            </div>
                            @if($attributeDependency->parentAttribute->value)
                                <div class="text-muted mt-1">{{ $attributeDependency->parentAttribute->value }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <!-- Relación -->
                        @php
                            $conditionIcons = [
                                'allows' => ['bi-arrow-right', 'text-success', 'Permite'],
                                'blocks' => ['bi-x', 'text-danger', 'Bloquea'],
                                'requires' => ['bi-exclamation-triangle', 'text-warning', 'Requiere'],
                                'sets_price' => ['bi-currency-euro', 'text-info', 'Modifica Precio']
                            ];
                            $conditionData = $conditionIcons[$attributeDependency->condition_type] ?? ['bi-question', 'text-secondary', 'Desconocido'];
                        @endphp
                        <div class="text-center">
                            <div class="icon-square bg-white rounded mx-auto mb-2 border" style="width: 48px; height: 48px; font-size: 20px;">
                                <i class="bi {{ $conditionData[0] }} {{ $conditionData[1] }}"></i>
                            </div>
                            <div class="badge bg-{{ $conditionData[1] === 'text-success' ? 'success' : ($conditionData[1] === 'text-danger' ? 'danger' : ($conditionData[1] === 'text-warning' ? 'warning' : 'info')) }}">
                                {{ $conditionData[2] }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <!-- Atributo dependiente -->
                        <div class="text-center p-4 border rounded bg-light">
                            <div class="icon-square bg-white rounded mx-auto mb-3" style="width: 64px; height: 64px; font-size: 24px;">
                                <i class="bi bi-circle-fill {{ $dependentColor }}"></i>
                            </div>
                            <h6 class="fw-bold">Atributo Dependiente</h6>
                            <div class="fw-medium">{{ $attributeDependency->dependentAttribute->name }}</div>
                            <div class="badge bg-secondary mt-2">
                                {{ ucfirst($attributeDependency->dependentAttribute->type) }}
                            </div>
                            @if($attributeDependency->dependentAttribute->value)
                                <div class="text-muted mt-1">{{ $attributeDependency->dependentAttribute->value }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles de la condición -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Detalles de la Condición</h5>
                        <small>Configuración específica de la relación</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Tipo de Condición</label>
                            <div>
                                <span class="badge bg-{{ $conditionData[1] === 'text-success' ? 'success' : ($conditionData[1] === 'text-danger' ? 'danger' : ($conditionData[1] === 'text-warning' ? 'warning' : 'info')) }} fs-6">
                                    {{ $conditionData[2] }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Descripción</label>
                            <div>
                                @switch($attributeDependency->condition_type)
                                    @case('allows')
                                        Cuando se selecciona <strong>{{ $attributeDependency->parentAttribute->name }}</strong>, 
                                        se habilita la selección de <strong>{{ $attributeDependency->dependentAttribute->name }}</strong>.
                                        @break
                                    @case('blocks')
                                        Cuando se selecciona <strong>{{ $attributeDependency->parentAttribute->name }}</strong>, 
                                        se deshabilita la selección de <strong>{{ $attributeDependency->dependentAttribute->name }}</strong>.
                                        @break
                                    @case('requires')
                                        Cuando se selecciona <strong>{{ $attributeDependency->parentAttribute->name }}</strong>, 
                                        es obligatorio seleccionar <strong>{{ $attributeDependency->dependentAttribute->name }}</strong>.
                                        @break
                                    @case('sets_price')
                                        Cuando ambos atributos están seleccionados, se modifica el precio del producto.
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if($attributeDependency->condition_type === 'sets_price' && $attributeDependency->price_impact)
                            <div class="mb-3">
                                <label class="form-label text-muted">Impacto en el Precio</label>
                                <div>
                                    <span class="fs-4 fw-bold {{ $attributeDependency->price_impact > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $attributeDependency->price_impact > 0 ? '+' : '' }}€{{ number_format($attributeDependency->price_impact, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Prioridad</label>
                            <div>
                                <span class="badge bg-secondary fs-6">{{ $attributeDependency->priority ?? 0 }}</span>
                                <small class="text-muted ms-2">
                                    @if(($attributeDependency->priority ?? 0) >= 800)
                                        Crítica
                                    @elseif(($attributeDependency->priority ?? 0) >= 600)
                                        Alta
                                    @elseif(($attributeDependency->priority ?? 0) >= 400)
                                        Media
                                    @elseif(($attributeDependency->priority ?? 0) >= 200)
                                        Baja
                                    @else
                                        Muy baja
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condiciones personalizadas -->
        @if($attributeDependency->conditions)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-code"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Condiciones Personalizadas</h5>
                        <small>Configuración avanzada en JSON</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="bg-light p-3 rounded">
                    <pre class="mb-0"><code>{{ json_encode($attributeDependency->conditions, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>
        @endif

        <!-- Dependencias relacionadas -->
        @if($relatedDependencies->count() > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-share"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Dependencias Relacionadas</h5>
                        <small>Otras dependencias que involucran estos atributos</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($relatedDependencies as $related)
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="small">
                                            <strong>{{ $related->parentAttribute->name }}</strong>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                            <strong>{{ $related->dependentAttribute->name }}</strong>
                                        </div>
                                        <div>
                                            @php
                                                $relatedConditionData = $conditionIcons[$related->condition_type] ?? ['bi-question', 'text-secondary', 'Desconocido'];
                                            @endphp
                                            <span class="badge bg-{{ $relatedConditionData[1] === 'text-success' ? 'success' : ($relatedConditionData[1] === 'text-danger' ? 'danger' : ($relatedConditionData[1] === 'text-warning' ? 'warning' : 'info')) }} badge-sm">
                                                {{ $relatedConditionData[2] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <a href="{{ route('admin.attribute-dependencies.show', $related) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Columna lateral -->
    <div class="col-lg-4">
        <!-- Opciones de comportamiento -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-toggles"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Comportamiento</h5>
                        <small>Opciones de funcionamiento</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-light rounded me-3" style="width: 32px; height: 32px;">
                            <i class="bi bi-magic {{ $attributeDependency->auto_select ? 'text-primary' : 'text-muted' }}"></i>
                        </div>
                        <div>
                            <div class="fw-medium">Auto-selección</div>
                            <small class="text-muted">
                                @if($attributeDependency->auto_select)
                                    Activada - El atributo dependiente se selecciona automáticamente
                                @else
                                    Desactivada - Requiere selección manual
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-light rounded me-3" style="width: 32px; height: 32px;">
                            <i class="bi bi-arrow-clockwise {{ $attributeDependency->reset_dependents ? 'text-warning' : 'text-muted' }}"></i>
                        </div>
                        <div>
                            <div class="fw-medium">Resetear Dependientes</div>
                            <small class="text-muted">
                                @if($attributeDependency->reset_dependents)
                                    Activado - Limpia selecciones cuando cambia el padre
                                @else
                                    Desactivado - Mantiene selecciones existentes
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información técnica -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Información Técnica</h5>
                        <small>Detalles del sistema</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">ID de la Dependencia</label>
                    <div class="font-monospace">{{ $attributeDependency->id }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">IDs de Atributos</label>
                    <div class="small">
                        <div>Padre: <span class="font-monospace">{{ $attributeDependency->parent_attribute_id }}</span></div>
                        <div>Dependiente: <span class="font-monospace">{{ $attributeDependency->dependent_attribute_id }}</span></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted">Fechas</label>
                    <div class="small">
                        <div>Creado: {{ $attributeDependency->created_at->format('d/m/Y H:i') }}</div>
                        <div>Modificado: {{ $attributeDependency->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-golden border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Acciones Rápidas</h5>
                        <small>Operaciones comunes</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.attribute-dependencies.edit', $attributeDependency) }}" class="btn btn-warning">
                        <i class="bi bi-pencil-square me-2"></i>Editar Dependencia
                    </a>
                    <button type="button" class="btn btn-info btn-duplicate" data-dependency-id="{{ $attributeDependency->id }}">
                        <i class="bi bi-files me-2"></i>Duplicar Dependencia
                    </button>
                    <a href="{{ route('admin.attribute-dependencies.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Dependencia
                    </a>
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
    const duplicateButtons = document.querySelectorAll('.btn-duplicate');
    duplicateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const dependencyId = this.dataset.dependencyId;
            
            if (confirm('¿Desea duplicar esta dependencia?')) {
                // Crear formulario para duplicar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/attribute-dependencies/${dependencyId}/duplicate`;
                
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
    });
});
</script>
@endpush