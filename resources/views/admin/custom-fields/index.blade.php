@extends('layouts.admin')

@section('title', 'Campos Personalizados')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Campos Personalizados</h2>
    <a href="{{ route('admin.custom-fields.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Campo
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.custom-fields.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre del campo">
                </div>
                <div class="col-md-3">
                    <label for="field_type" class="form-label">Tipo de Campo</label>
                    <select class="form-select" id="field_type" name="field_type">
                        <option value="">Todos los tipos</option>
                        <option value="text" {{ request('field_type') == 'text' ? 'selected' : '' }}>Texto</option>
                        <option value="number" {{ request('field_type') == 'number' ? 'selected' : '' }}>Número</option>
                        <option value="email" {{ request('field_type') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="select" {{ request('field_type') == 'select' ? 'selected' : '' }}>Select</option>
                        <option value="textarea" {{ request('field_type') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                        <option value="checkbox" {{ request('field_type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                        <option value="date" {{ request('field_type') == 'date' ? 'selected' : '' }}>Fecha</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('admin.custom-fields.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Lista de Campos Personalizados ({{ $customFields->total() }})</h5>
    </div>
    <div class="card-body">
        @if($customFields->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th>Vista Previa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customFields as $field)
                            <tr>
                                <td>
                                    <strong>{{ $field->name }}</strong>
                                    @if($field->help_text)
                                        <br><small class="text-muted">{{ Str::limit($field->help_text, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($field->field_type) }}</span>
                                </td>
                                <td>
                                    @if($field->required)
                                        <span class="badge bg-warning">Requerido</span>
                                    @else
                                        <span class="badge bg-secondary">Opcional</span>
                                    @endif
                                </td>
                                <td>
                                    @if($field->active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $field->sort_order }}</td>
                                <td>
                                    <div style="max-width: 200px; font-size: 0.85em;">
                                        @if($field->field_type == 'text')
                                            <input type="text" class="form-control form-control-sm" 
                                                   placeholder="{{ $field->placeholder }}" disabled>
                                        @elseif($field->field_type == 'textarea')
                                            <textarea class="form-control form-control-sm" rows="2" 
                                                      placeholder="{{ $field->placeholder }}" disabled></textarea>
                                        @elseif($field->field_type == 'select' && $field->options)
                                            <select class="form-select form-select-sm" disabled>
                                                <option>Seleccionar...</option>
                                                @foreach($field->options as $option)
                                                    <option>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($field->field_type == 'checkbox')
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" disabled>
                                                <label class="form-check-label">{{ $field->name }}</label>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.custom-fields.show', $field) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.custom-fields.edit', $field) }}" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.custom-fields.destroy', $field) }}" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-delete" 
                                                    data-item-name="{{ $field->name }}">
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
            <div class="d-flex justify-content-center">
                {{ $customFields->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-sliders display-1 text-muted"></i>
                <h4 class="mt-3">No hay campos personalizados</h4>
                <p class="text-muted">Comienza creando tu primer campo personalizado.</p>
                <a href="{{ route('admin.custom-fields.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Primer Campo
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
