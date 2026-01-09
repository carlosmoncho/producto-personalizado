@extends('layouts.admin')

@section('title', 'Reglas de Precios')

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
                <li class="breadcrumb-item active" aria-current="page">Reglas de Precios</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-calculator"></i>
            </div>
            <div>
                <h2 class="mb-0">Reglas de Precios Dinámicos</h2>
                <small class="text-muted">Gestiona reglas automáticas para cálculo de precios</small>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.price-rules.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nueva Regla
        </a>
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
                <small>Filtrar reglas por tipo y estado</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.price-rules.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="rule_type" class="form-label">Tipo de Regla</label>
                    <select class="form-select" id="rule_type" name="rule_type">
                        <option value="">Todos los tipos</option>
                        <option value="combination" {{ request('rule_type') == 'combination' ? 'selected' : '' }}>Combinación</option>
                        <option value="volume" {{ request('rule_type') == 'volume' ? 'selected' : '' }}>Volumen</option>
                        <option value="attribute_specific" {{ request('rule_type') == 'attribute_specific' ? 'selected' : '' }}>Atributo Específico</option>
                        <option value="conditional" {{ request('rule_type') == 'conditional' ? 'selected' : '' }}>Condicional</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Producto</label>
                    <select class="form-select" id="product_id" name="product_id">
                        <option value="">Todos los productos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="active" class="form-label">Estado</label>
                    <select class="form-select" id="active" name="active">
                        <option value="">Todos</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activas</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.price-rules.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-golden border-bottom-0 py-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                    <i class="bi bi-list-ul"></i>
                </div>
                <div>
                    <h5 class="mb-0">Lista de Reglas ({{ $rules->total() }})</h5>
                    <small>Reglas ordenadas por prioridad y orden</small>
                </div>
            </div>
            <!-- Botón eliminar en bloque -->
            <div id="bulkDeleteContainer" style="display: none;">
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Eliminar seleccionadas (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($rules->count() > 0)
            <form id="bulkDeleteForm" method="POST" action="{{ route('admin.price-rules.destroy-bulk') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll" title="Seleccionar todas">
                                </th>
                                <th>Regla</th>
                                <th>Tipo</th>
                                <th>Acción</th>
                                <th>Alcance</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                        @foreach($rules as $rule)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $rule->id }}">
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $rule->name }}</strong>
                                        @if($rule->description)
                                            <br><small class="text-muted">{{ Str::limit($rule->description, 100) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'combination' => ['Combinación', 'bg-primary'],
                                            'volume' => ['Volumen', 'bg-success'],
                                            'attribute_specific' => ['Atributo', 'bg-info'],
                                            'conditional' => ['Condicional', 'bg-warning']
                                        ];
                                        $typeData = $typeLabels[$rule->rule_type] ?? ['Desconocido', 'bg-secondary'];
                                    @endphp
                                    <span class="badge {{ $typeData[1] }}">{{ $typeData[0] }}</span>
                                </td>
                                <td>
                                    @php
                                        $actionLabels = [
                                            'add_fixed' => 'Sumar €' . number_format($rule->action_value, 2),
                                            'add_percentage' => 'Sumar ' . $rule->action_value . '%',
                                            'multiply' => 'Multiplicar x' . $rule->action_value,
                                            'set_fixed' => 'Fijar €' . number_format($rule->action_value, 2),
                                            'set_percentage' => 'Fijar ' . $rule->action_value . '%'
                                        ];
                                        $actionLabel = $actionLabels[$rule->action_type] ?? 'Desconocido';
                                        $actionColor = $rule->action_value > 0 ? 'text-success' : 'text-danger';
                                    @endphp
                                    <span class="fw-medium {{ $actionColor }}">{{ $actionLabel }}</span>
                                </td>
                                <td>
                                    <div class="small">
                                        @if($rule->product)
                                            <div><i class="bi bi-box-seam text-primary"></i> {{ $rule->product->name }}</div>
                                        @elseif($rule->category)
                                            <div><i class="bi bi-folder text-warning"></i> {{ $rule->category->name }}</div>
                                        @else
                                            <div><i class="bi bi-globe text-success"></i> Global</div>
                                        @endif
                                        
                                        @if($rule->quantity_min || $rule->quantity_max)
                                            <div class="text-muted">
                                                <i class="bi bi-hash"></i>
                                                {{ $rule->quantity_min ?? '∞' }} - {{ $rule->quantity_max ?? '∞' }} uds
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $rule->priority }}</span>
                                </td>
                                <td>
                                    @if($rule->active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                    
                                    @if($rule->valid_from && $rule->valid_from > now())
                                        <div><small class="text-warning">Pendiente desde {{ $rule->valid_from->format('d/m/Y') }}</small></div>
                                    @elseif($rule->valid_until && $rule->valid_until < now())
                                        <div><small class="text-danger">Expirada el {{ $rule->valid_until->format('d/m/Y') }}</small></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.price-rules.show', $rule) }}" 
                                           class="btn btn-outline-primary btn-sm"
                                           title="Ver regla">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.price-rules.edit', $rule) }}" 
                                           class="btn btn-outline-secondary btn-sm"
                                           title="Editar regla">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm btn-duplicate" 
                                                data-rule-id="{{ $rule->id }}"
                                                title="Duplicar regla">
                                            <i class="bi bi-files"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.price-rules.destroy', $rule) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                                    data-item-name="{{ $rule->name }}"
                                                    title="Eliminar regla">
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
            </form>

            <!-- Paginación -->
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Mostrando {{ $rules->firstItem() ?? 0 }} a {{ $rules->lastItem() ?? 0 }} de {{ $rules->total() }} reglas
                </div>
                <div>
                    {{ $rules->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-calculator display-1 text-muted"></i>
                <h4 class="mt-3">No hay reglas de precios</h4>
                <p class="text-muted">Las reglas de precios aparecerán aquí cuando las agregues.</p>
                <a href="{{ route('admin.price-rules.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Primera Regla
                </a>
            </div>
        @endif
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
            const ruleId = this.dataset.ruleId;

            if (confirm('¿Desea duplicar esta regla de precio?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/price-rules/${ruleId}/duplicate`;

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

    // Manejar botones de eliminar individual
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const ruleName = this.dataset.itemName;
            const form = this.closest('form');

            if (confirm(`¿Está seguro de eliminar la regla "${ruleName}"?\n\nEsta acción no se puede deshacer.`)) {
                form.submit();
            }
        });
    });

    // === ELIMINACIÓN EN BLOQUE ===
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteContainer = document.getElementById('bulkDeleteContainer');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');

    function updateBulkDeleteUI() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;

        selectedCountSpan.textContent = count;
        bulkDeleteContainer.style.display = count > 0 ? 'block' : 'none';

        if (rowCheckboxes.length > 0) {
            selectAllCheckbox.checked = count === rowCheckboxes.length;
            selectAllCheckbox.indeterminate = count > 0 && count < rowCheckboxes.length;
        }
    }

    selectAllCheckbox?.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteUI();
    });

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteUI);
    });

    bulkDeleteBtn?.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;

        if (count === 0) {
            alert('Selecciona al menos una regla para eliminar.');
            return;
        }

        if (confirm(`¿Estás seguro de que quieres eliminar ${count} regla(s) de precio?\n\nEsta acción no se puede deshacer.`)) {
            bulkDeleteForm.submit();
        }
    });
});
</script>
@endpush