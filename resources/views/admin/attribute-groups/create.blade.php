@extends('layouts.admin')

@section('title', 'Crear Grupo de Atributos')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.attribute-groups.index') }}">Grupos de Atributos</a>
                    </li>
                    <li class="breadcrumb-item active">Crear Grupo</li>
                </ol>
            </nav>
            <h2>Crear Nuevo Grupo de Atributos</h2>
            <p class="text-muted">Define un nuevo grupo para organizar atributos relacionados</p>
        </div>
        <div>
            <a href="{{ route('admin.attribute-groups.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.attribute-groups.store') }}">
        @csrf
        <div class="row">
            <!-- Columna principal -->
            <div class="col-lg-8">
                <!-- Información básica -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Información Básica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre del Grupo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="ej. Colores Disponibles">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">Slug (URL)</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}"
                                       placeholder="Se genera automáticamente">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Dejar vacío para generar automáticamente</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Tipo de Atributos <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="">Selecciona un tipo</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Orden de Visualización</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                       min="0" max="9999">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Menor número = aparece primero</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3"
                                      placeholder="Describe el propósito de este grupo de atributos...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Configuración de comportamiento -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2"></i>Configuración de Comportamiento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_required" name="is_required" value="1" 
                                           {{ old('is_required') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_required">
                                        <strong>Grupo Requerido</strong>
                                        <div class="form-text">El cliente debe seleccionar al menos una opción</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="allow_multiple" name="allow_multiple" value="1" 
                                           {{ old('allow_multiple') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_multiple">
                                        <strong>Permitir Selección Múltiple</strong>
                                        <div class="form-text">El cliente puede seleccionar varias opciones</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="show_in_filter" name="show_in_filter" value="1" 
                                           {{ old('show_in_filter', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_in_filter">
                                        <strong>Mostrar en Filtros</strong>
                                        <div class="form-text">Disponible para filtrar productos</div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="affects_price" name="affects_price" value="1" 
                                           {{ old('affects_price') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="affects_price">
                                        <strong>Afecta al Precio</strong>
                                        <div class="form-text">Los atributos pueden modificar el precio</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="affects_stock" name="affects_stock" value="1" 
                                           {{ old('affects_stock') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="affects_stock">
                                        <strong>Afecta al Stock</strong>
                                        <div class="form-text">Control de inventario por variante</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="active" name="active" value="1" 
                                           {{ old('active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        <strong>Activo</strong>
                                        <div class="form-text">El grupo está disponible para usar</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna lateral -->
            <div class="col-lg-4">
                <!-- Ayuda contextual -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-question-circle me-2"></i>Ayuda
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6>¿Qué es un grupo de atributos?</h6>
                        <p class="small text-muted">
                            Los grupos organizan atributos relacionados (como todos los colores disponibles) 
                            y definen cómo se comportan en el configurador de productos.
                        </p>
                        
                        <h6 class="mt-3">Ejemplos de uso:</h6>
                        <ul class="small text-muted">
                            <li><strong>Colores:</strong> Agrupa todos los colores disponibles</li>
                            <li><strong>Tamaños:</strong> Define las dimensiones del producto</li>
                            <li><strong>Materiales:</strong> Lista los materiales de fabricación</li>
                            <li><strong>Acabados:</strong> Opciones de terminación del producto</li>
                        </ul>

                        <h6 class="mt-3">Configuraciones importantes:</h6>
                        <ul class="small text-muted">
                            <li><strong>Requerido:</strong> Obliga al cliente a seleccionar una opción</li>
                            <li><strong>Múltiple:</strong> Permite combinar varios atributos</li>
                            <li><strong>Afecta precio:</strong> Los atributos pueden tener costos adicionales</li>
                            <li><strong>Afecta stock:</strong> Cada variante tiene su propio inventario</li>
                        </ul>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-check-circle me-2"></i>Crear Grupo
                        </button>
                        <a href="{{ route('admin.attribute-groups.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Auto-generar slug desde el nombre
document.getElementById('name').addEventListener('input', function() {
    const slug = document.getElementById('slug');
    if (!slug.value || slug.dataset.manual !== 'true') {
        slug.value = this.value
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});

// Marcar el slug como manual si el usuario lo edita
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = 'true';
});
</script>
@endpush