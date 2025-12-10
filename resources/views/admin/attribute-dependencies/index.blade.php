@extends('layouts.admin')

@section('title', 'Dependencias & Precios')

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
                <li class="breadcrumb-item active" aria-current="page">Dependencias & Precios</li>
            </ol>
        </nav>

        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <h2 class="mb-0">Dependencias & Precios</h2>
                <small class="text-muted">Gestiona modificadores individuales y dependencias por combinación</small>
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
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attribute-dependencies.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="filter_type" class="form-label">Tipo</label>
                <select class="form-select" id="filter_type" name="filter_type">
                    <option value="">Todos los tipos</option>
                    <option value="individual" {{ ($filters['type'] ?? '') == 'individual' ? 'selected' : '' }}>Modificadores Individuales</option>
                    <option value="combination" {{ ($filters['type'] ?? '') == 'combination' ? 'selected' : '' }}>Dependencias por Combinación</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_product" class="form-label">Producto</label>
                <select class="form-select" id="filter_product" name="filter_product">
                    <option value="">Todos los productos</option>
                    <option value="global" {{ ($filters['product'] ?? '') == 'global' ? 'selected' : '' }}>Solo globales</option>
                    @if(isset($products))
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ ($filters['product'] ?? '') == (string)$product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_attribute_type" class="form-label">Tipo de Atributo</label>
                <select class="form-select" id="filter_attribute_type" name="filter_attribute_type">
                    <option value="">Todos los tipos</option>
                    <option value="color" {{ ($filters['attribute_type'] ?? '') == 'color' ? 'selected' : '' }}>Color</option>
                    <option value="material" {{ ($filters['attribute_type'] ?? '') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="size" {{ ($filters['attribute_type'] ?? '') == 'size' ? 'selected' : '' }}>Tamaño</option>
                    <option value="ink" {{ ($filters['attribute_type'] ?? '') == 'ink' ? 'selected' : '' }}>Tinta</option>
                    <option value="quantity" {{ ($filters['attribute_type'] ?? '') == 'quantity' ? 'selected' : '' }}>Cantidad</option>
                    <option value="system" {{ ($filters['attribute_type'] ?? '') == 'system' ? 'selected' : '' }}>Sistema</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="{{ route('admin.attribute-dependencies.index', ['clear_filters' => 1]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista -->
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
                            @php
                                $isIndividual = !$dependency->dependentAttribute;
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
                            <tr>
                                <!-- Tipo -->
                                <td>
                                    @if($isIndividual)
                                        <div class="d-flex align-items-center">
                                            <div class="icon-square bg-info bg-opacity-10 rounded me-2" style="width: 32px; height: 32px;">
                                                <i class="bi bi-currency-euro text-info"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium text-info">Individual</div>
                                                <small class="text-muted">Modificador</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="icon-square bg-success bg-opacity-10 rounded me-2" style="width: 32px; height: 32px;">
                                                <i class="bi bi-arrow-left-right text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium text-success">Combinación</div>
                                                <small class="text-muted">Dependencia</small>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Atributo Padre -->
                                <td>
                                    <div class="d-flex align-items-center">
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

                                <!-- Relación / Atributo Dependiente -->
                                <td>
                                    @if($isIndividual)
                                        <div class="text-center">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <i class="bi bi-arrow-down me-1"></i>Automático
                                            </span>
                                            <div class="mt-1">
                                                <small class="text-muted">Se aplica al seleccionar el atributo</small>
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $conditionLabels = [
                                                'allows' => ['Permite', 'bg-success', 'bi-arrow-right'],
                                                'blocks' => ['Bloquea', 'bg-danger', 'bi-x'],
                                                'requires' => ['Requiere', 'bg-warning', 'bi-exclamation-triangle'],
                                                'sets_price' => ['Modifica Precio', 'bg-info', 'bi-currency-euro'],
                                                'price_modifier' => ['Precio', 'bg-info', 'bi-currency-euro']
                                            ];
                                            $conditionData = $conditionLabels[$dependency->condition_type] ?? ['Desconocido', 'bg-secondary', 'bi-question'];
                                            $dependentColor = $parentTypeColors[$dependency->dependentAttribute->type] ?? 'text-muted';
                                            $thirdColor = $dependency->thirdAttribute ? ($parentTypeColors[$dependency->thirdAttribute->type] ?? 'text-muted') : null;
                                            $numAttributes = $dependency->thirdAttribute ? 3 : 2;
                                        @endphp
                                        <div class="text-center">
                                            <span class="badge {{ $conditionData[1] }}">
                                                <i class="bi {{ $conditionData[2] }} me-1"></i>{{ $conditionData[0] }}
                                            </span>
                                            @if($numAttributes == 3)
                                                <span class="badge bg-secondary ms-1" title="Combinación de 3 atributos">x3</span>
                                            @endif
                                            <div class="d-flex align-items-center mt-2">
                                                <div class="icon-square bg-light rounded me-2" style="width: 24px; height: 24px;">
                                                    <i class="bi bi-circle-fill {{ $dependentColor }}" style="font-size: 12px;"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium">{{ $dependency->dependentAttribute->name }}</div>
                                                    <small class="text-muted">{{ $typeLabels[$dependency->dependentAttribute->type] ?? $dependency->dependentAttribute->type }}</small>
                                                </div>
                                            </div>
                                            @if($dependency->thirdAttribute)
                                                <div class="d-flex align-items-center mt-1">
                                                    <div class="icon-square bg-light rounded me-2" style="width: 24px; height: 24px;">
                                                        <i class="bi bi-circle-fill {{ $thirdColor }}" style="font-size: 12px;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $dependency->thirdAttribute->name }}</div>
                                                        <small class="text-muted">{{ $typeLabels[$dependency->thirdAttribute->type] ?? $dependency->thirdAttribute->type }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($dependency->auto_select)
                                                <div class="mt-1"><small class="text-primary"><i class="bi bi-magic"></i> Auto-select</small></div>
                                            @endif
                                            @if($dependency->reset_dependents)
                                                <div class="mt-1"><small class="text-warning"><i class="bi bi-arrow-clockwise"></i> Reset</small></div>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- Modificador de Precio -->
                                <td class="text-center">
                                    @php
                                        $hasFixed = $dependency->price_modifier && $dependency->price_modifier != 0;
                                        $hasPercentage = $dependency->price_percentage && $dependency->price_percentage != 0;
                                    @endphp
                                    @if($hasFixed || $hasPercentage)
                                        @if($hasFixed)
                                            <span class="fw-medium {{ $dependency->price_modifier > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $dependency->price_modifier > 0 ? '+' : '' }}€{{ number_format($dependency->price_modifier, 3) }}
                                            </span>
                                        @endif
                                        @if($hasPercentage)
                                            <span class="fw-medium {{ $dependency->price_percentage > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $dependency->price_percentage > 0 ? '+' : '' }}{{ number_format($dependency->price_percentage, 3) }}%
                                            </span>
                                        @endif
                                        <div>
                                            @if($dependency->price_applies_to === 'total')
                                                <span class="badge bg-warning text-dark">al total</span>
                                            @else
                                                <small class="text-muted">por unidad</small>
                                            @endif
                                        </div>
                                    @elseif($dependency->condition_type === 'sets_price' && $dependency->price_impact)
                                        <span class="fw-medium {{ $dependency->price_impact > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $dependency->price_impact > 0 ? '+' : '' }}€{{ number_format($dependency->price_impact, 3) }}
                                        </span>
                                        <div><small class="text-muted">legacy</small></div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <!-- Producto -->
                                <td>
                                    @if($dependency->product)
                                        <div class="fw-medium">{{ $dependency->product->name }}</div>
                                        @if($dependency->product->sku)
                                            <small class="text-muted">SKU: {{ $dependency->product->sku }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted fst-italic">Todos los productos</span>
                                    @endif
                                </td>

                                <!-- Prioridad -->
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ $dependency->priority ?? 0 }}</span>
                                </td>

                                <!-- Acciones -->
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.attribute-dependencies.show', $dependency) }}"
                                           class="btn btn-outline-info btn-sm" title="Ver dependencia">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.attribute-dependencies.edit', $dependency) }}"
                                           class="btn btn-outline-primary btn-sm" title="Editar dependencia">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.attribute-dependencies.duplicate', $dependency) }}"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Duplicar dependencia">
                                                <i class="bi bi-files"></i>
                                            </button>
                                        </form>
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
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Mostrando {{ $dependencies->firstItem() }} a {{ $dependencies->lastItem() }}
                        de {{ $dependencies->total() }} resultados
                    </small>
                </div>
                <div>
                    {{ $dependencies->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-diagram-3 display-1 text-muted mb-3"></i>
                <h4 class="text-muted mb-3">No hay dependencias creadas</h4>
                <p class="text-muted mb-4">Crea tu primera dependencia o modificador individual para comenzar</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('admin.attribute-dependencies.create-individual') }}" class="btn btn-info">
                        <i class="bi bi-currency-euro me-2"></i>Crear Modificador Individual
                    </a>
                    <a href="{{ route('admin.attribute-dependencies.create-combination') }}" class="btn btn-success">
                        <i class="bi bi-arrow-left-right me-2"></i>Crear Dependencia por Combinación
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmación de eliminación
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const itemName = this.getAttribute('data-item-name');

            if (confirm(`¿Estás seguro de que quieres eliminar "${itemName}"?`)) {
                this.closest('form').submit();
            }
        });
    });

    // Validar configuración
    document.getElementById('validateConfigBtn')?.addEventListener('click', function() {
        // Implementar validación de configuración
        alert('Función de validación en desarrollo');
    });
});
</script>
@endpush