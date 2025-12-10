@extends('layouts.admin')

@section('title', 'Dependencias de Atributos')

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
                <li class="breadcrumb-item active" aria-current="page">Dependencias de Atributos</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <h2 class="mb-0">Dependencias de Atributos</h2>
                <small class="text-muted">Gestiona las relaciones y dependencias entre atributos de productos</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-info" id="validateConfigBtn">
            <i class="bi bi-shield-check me-2"></i>Validar Configuración
        </button>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-plus-circle me-2"></i>Crear Nuevo
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.attribute-dependencies.create-individual') }}">
                        <i class="bi bi-currency-euro me-2 text-info"></i>
                        <div>
                            <div class="fw-medium">Modificador Individual</div>
                            <small class="text-muted">Precio que se aplica al seleccionar un atributo</small>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.attribute-dependencies.create-combination') }}">
                        <i class="bi bi-arrow-left-right me-2 text-success"></i>
                        <div>
                            <div class="fw-medium">Dependencia por Combinación</div>
                            <small class="text-muted">Relación entre dos atributos específicos</small>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.attribute-dependencies.create') }}">
                        <i class="bi bi-gear me-2 text-secondary"></i>
                        <div>
                            <div class="fw-medium">Formulario Completo</div>
                            <small class="text-muted">Todas las opciones (avanzado)</small>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-golden border-bottom-0 py-3">
        <div class="d-flex align-items-center">
            <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <h5 class="mb-0">Filtros de Búsqueda</h5>
                <small>Filtrar dependencias por tipo y condición</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attribute-dependencies.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="parent_type" class="form-label">Atributo Padre</label>
                    <select class="form-select" id="parent_type" name="parent_type">
                        <option value="">Todos los tipos</option>
                        @foreach($parentTypes as $type)
                            <option value="{{ $type }}" {{ request('parent_type') == $type ? 'selected' : '' }}>
                                {{ $typeLabels[$type] ?? $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dependent_type" class="form-label">Atributo Dependiente</label>
                    <select class="form-select" id="dependent_type" name="dependent_type">
                        <option value="">Todos los tipos</option>
                        @foreach($dependentTypes as $type)
                            <option value="{{ $type }}" {{ request('dependent_type') == $type ? 'selected' : '' }}>
                                {{ $typeLabels[$type] ?? $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="condition_type" class="form-label">Tipo de Condición</label>
                    <select class="form-select" id="condition_type" name="condition_type">
                        <option value="">Todas las condiciones</option>
                        @foreach($conditionTypes as $type => $label)
                            <option value="{{ $type }}" {{ request('condition_type') == $type ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nombre de atributo...">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('admin.attribute-dependencies.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-golden border-bottom-0 py-3">
        <div class="d-flex align-items-center">
            <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                <i class="bi bi-list-ul"></i>
            </div>
            <div>
                <h5 class="mb-0">Dependencias & Modificadores ({{ $dependencies->total() }})</h5>
                <small>Modificadores individuales y dependencias por combinación ordenados por prioridad</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($dependencies->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Atributo Padre</th>
                            <th>Relación / Atributo Dependiente</th>
                            <th>Modificador de Precio</th>
                            <th>Producto</th>
                            <th>Prioridad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dependencies as $dependency)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $parentTypeColors = [
                                                'color' => 'text-danger',
                                                'material' => 'text-warning',
                                                'size' => 'text-info',
                                                'ink' => 'text-primary',
                                                'quantity' => 'text-success',
                                                'system' => 'text-secondary'
                                            ];
                                            $parentColor = $parentTypeColors[$dependency->parentAttribute->type] ?? 'text-muted';
                                        @endphp
                                        <div class="icon-square bg-light rounded me-2" style="width: 32px; height: 32px;">
                                            <i class="bi bi-circle-fill {{ $parentColor }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $dependency->parentAttribute->name }}</div>
                                            <small class="text-muted">
                                                {{ $typeLabels[$dependency->parentAttribute->type] ?? $dependency->parentAttribute->type }}
                                                @if($dependency->parentAttribute->value)
                                                    - {{ $dependency->parentAttribute->value }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $conditionLabels = [
                                            'allows' => ['Permite', 'bg-success', 'bi-arrow-right'],
                                            'blocks' => ['Bloquea', 'bg-danger', 'bi-x'],
                                            'requires' => ['Requiere', 'bg-warning', 'bi-exclamation-triangle'],
                                            'sets_price' => ['Modifica Precio', 'bg-info', 'bi-currency-euro']
                                        ];
                                        $conditionData = $conditionLabels[$dependency->condition_type] ?? ['Desconocido', 'bg-secondary', 'bi-question'];
                                    @endphp
                                    <div class="text-center">
                                        <span class="badge {{ $conditionData[1] }}">
                                            <i class="bi {{ $conditionData[2] }} me-1"></i>{{ $conditionData[0] }}
                                        </span>
                                        @if($dependency->auto_select)
                                            <div><small class="text-primary"><i class="bi bi-magic"></i> Auto-select</small></div>
                                        @endif
                                        @if($dependency->reset_dependents)
                                            <div><small class="text-warning"><i class="bi bi-arrow-clockwise"></i> Reset</small></div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($dependency->dependentAttribute)
                                        <div class="d-flex align-items-center">
                                            @php
                                                $dependentColor = $parentTypeColors[$dependency->dependentAttribute->type] ?? 'text-muted';
                                            @endphp
                                            <div class="icon-square bg-light rounded me-2" style="width: 32px; height: 32px;">
                                                <i class="bi bi-circle-fill {{ $dependentColor }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $dependency->dependentAttribute->name }}</div>
                                                <small class="text-muted">
                                                    {{ $typeLabels[$dependency->dependentAttribute->type] ?? $dependency->dependentAttribute->type }}
                                                    @if($dependency->dependentAttribute->value)
                                                        - {{ $dependency->dependentAttribute->value }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="icon-square bg-light rounded me-2" style="width: 32px; height: 32px;">
                                                <i class="bi bi-dash-circle text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium text-muted">Sin dependiente</div>
                                                <small class="text-info">Modificador individual</small>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($dependency->condition_type === 'sets_price' && $dependency->price_impact)
                                        <span class="fw-medium {{ $dependency->price_impact > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $dependency->price_impact > 0 ? '+' : '' }}€{{ number_format($dependency->price_impact, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $dependency->priority ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.attribute-dependencies.show', $dependency) }}" 
                                           class="btn btn-outline-primary btn-sm"
                                           title="Ver dependencia">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.attribute-dependencies.edit', $dependency) }}" 
                                           class="btn btn-outline-secondary btn-sm"
                                           title="Editar dependencia">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm btn-duplicate" 
                                                data-dependency-id="{{ $dependency->id }}"
                                                title="Duplicar dependencia">
                                            <i class="bi bi-files"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.attribute-dependencies.destroy', $dependency) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete"
                                                    data-item-name="{{ $dependency->parentAttribute->name }}{{ $dependency->dependentAttribute ? ' → ' . $dependency->dependentAttribute->name : ' (modificador individual)' }}"
                                                    title="Eliminar {{ $dependency->dependentAttribute ? 'dependencia' : 'modificador individual' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando {{ $dependencies->firstItem() ?? 0 }} a {{ $dependencies->lastItem() ?? 0 }} de {{ $dependencies->total() }} dependencias
                </div>
                <div>
                    {{ $dependencies->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-diagram-3 display-1 text-muted"></i>
                <h4 class="mt-3">No hay dependencias de atributos</h4>
                <p class="text-muted">Las dependencias entre atributos aparecerán aquí cuando las agregues.</p>
                <a href="{{ route('admin.attribute-dependencies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Primera Dependencia
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal de validación -->
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-check me-2"></i>Validación de Configuración
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="validationResults">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Validando...</span>
                        </div>
                        <div class="mt-2">Validando configuración...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botones de duplicar
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

    // Manejar botones de eliminar
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const dependencyName = this.dataset.itemName;
            const form = this.closest('form');
            
            if (confirm(`¿Está seguro de eliminar la dependencia "${dependencyName}"?\\n\\nEsta acción no se puede deshacer y puede afectar el funcionamiento del configurador.`)) {
                form.submit();
            }
        });
    });

    // Manejar validación de configuración
    const validateBtn = document.getElementById('validateConfigBtn');
    const validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
    
    validateBtn.addEventListener('click', function() {
        validationModal.show();
        
        // Hacer petición de validación
        fetch('{{ route("admin.api.attribute-dependencies.validate") }}')
            .then(response => response.json())
            .then(data => {
                displayValidationResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('validationResults').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error al validar la configuración: ${error.message}
                    </div>
                `;
            });
    });
    
    function displayValidationResults(data) {
        let html = '';
        
        if (data.is_valid) {
            html = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>¡Configuración válida!</strong> No se encontraron errores en las dependencias.
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Configuración inválida</strong> - Se encontraron errores que deben corregirse.
                </div>
            `;
        }
        
        if (data.errors && data.errors.length > 0) {
            html += `
                <div class="mb-3">
                    <h6 class="text-danger"><i class="bi bi-x-circle me-1"></i>Errores:</h6>
                    <ul class="list-unstyled">
            `;
            data.errors.forEach(error => {
                html += `<li class="text-danger">• ${error}</li>`;
            });
            html += '</ul></div>';
        }
        
        if (data.warnings && data.warnings.length > 0) {
            html += `
                <div class="mb-3">
                    <h6 class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Advertencias:</h6>
                    <ul class="list-unstyled">
            `;
            data.warnings.forEach(warning => {
                html += `<li class="text-warning">• ${warning}</li>`;
            });
            html += '</ul></div>';
        }
        
        if ((!data.errors || data.errors.length === 0) && (!data.warnings || data.warnings.length === 0)) {
            html += `
                <div class="text-center text-muted">
                    <i class="bi bi-check-circle display-4 text-success"></i>
                    <div class="mt-2">No se encontraron problemas en la configuración</div>
                </div>
            `;
        }
        
        document.getElementById('validationResults').innerHTML = html;
    }
});
</script>
@endpush