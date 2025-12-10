@extends('layouts.admin')

@section('title', 'Ver Atributo')

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
                        <a href="{{ route('admin.product-attributes.index') }}">Atributos</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $productAttribute->name }}</li>
                </ol>
            </nav>
            <h2>{{ $productAttribute->name }}</h2>
            <p class="text-muted">
                @php
                    $typeLabels = [
                        'color' => 'Color',
                        'material' => 'Material',
                        'size' => 'Tamaño',
                        'ink' => 'Tinta de Impresión',
                        'quantity' => 'Cantidad',
                        'system' => 'Sistema de Impresión'
                    ];
                @endphp
                {{ $typeLabels[$productAttribute->type] ?? $productAttribute->type }} - 
                @if($productAttribute->active)
                    <span class="badge bg-success">Activo</span>
                @else
                    <span class="badge bg-secondary">Inactivo</span>
                @endif
                @if($productAttribute->is_recommended)
                    <span class="badge bg-primary ms-1">Recomendado</span>
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('admin.product-attributes.edit', $productAttribute) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
            <button type="button" class="btn btn-info me-2 btn-duplicate" data-attribute-id="{{ $productAttribute->id }}">
                <i class="bi bi-files"></i> Duplicar
            </button>
            <a href="{{ route('admin.product-attributes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <!-- Vista del atributo -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Vista del Atributo</h5>
                            <small>Cómo se muestra a los usuarios</small>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="attribute-display py-4">
                        @switch($productAttribute->type)
                            @case('color')
                                @if($productAttribute->hex_code)
                                    <div class="color-preview mx-auto mb-3" 
                                         style="width: 120px; height: 120px; background-color: {{ $productAttribute->hex_code }}; 
                                                border-radius: 15px; border: 4px solid #fff; box-shadow: 0 6px 20px rgba(0,0,0,0.15);">
                                    </div>
                                @else
                                    <div class="text-danger mb-3">
                                        <i class="bi bi-exclamation-triangle display-4"></i>
                                    </div>
                                @endif
                                @break
                                
                            @case('ink')
                                @if($productAttribute->hex_code)
                                    <div class="ink-preview mx-auto mb-3" 
                                         style="width: 100px; height: 100px; background-color: {{ $productAttribute->hex_code }}; 
                                                border-radius: 12px; border: 3px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                    </div>
                                @else
                                    <div class="text-primary mb-3">
                                        <i class="bi bi-droplet-fill display-4"></i>
                                    </div>
                                @endif
                                @break

                            @case('ink_color')
                                @if($productAttribute->hex_code)
                                    <div class="color-preview mx-auto mb-3"
                                         style="width: 120px; height: 120px; background-color: {{ $productAttribute->hex_code }};
                                                border-radius: 15px; border: 4px solid #fff; box-shadow: 0 6px 20px rgba(0,0,0,0.15);">
                                    </div>
                                @else
                                    <div class="text-primary mb-3">
                                        <i class="bi bi-palette-fill display-4"></i>
                                    </div>
                                @endif
                                @break

                            @case('material')
                                <div class="text-success mb-3">
                                    <i class="bi bi-layers display-4"></i>
                                </div>
                                @break
                                
                            @case('size')
                                <div class="text-warning mb-3">
                                    <i class="bi bi-rulers display-4"></i>
                                </div>
                                @break
                                
                            @case('quantity')
                                <div class="text-secondary mb-3">
                                    <i class="bi bi-boxes display-4"></i>
                                </div>
                                @break
                                
                            @case('system')
                                <div class="text-dark mb-3">
                                    <i class="bi bi-gear display-4"></i>
                                </div>
                                @break

                            @case('cliche')
                                <div class="text-info mb-3">
                                    <i class="bi bi-stamp display-4"></i>
                                </div>
                                @break
                        @endswitch
                        
                        <h4 class="mb-1">{{ $productAttribute->name }}</h4>
                        <small class="text-muted">{{ $productAttribute->value }}</small>
                        
                        <div class="mt-3">
                            <span class="badge bg-secondary fs-6">
                                Precio por dependencias
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información técnica -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Información Técnica</h5>
                            <small>Detalles de configuración</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Tipo</label>
                                <div>
                                    <span class="badge bg-primary fs-6">{{ $typeLabels[$productAttribute->type] ?? $productAttribute->type }}</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Valor Técnico</label>
                                <div class="font-monospace">{{ $productAttribute->value }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Orden de Visualización</label>
                                <div>{{ $productAttribute->sort_order ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($productAttribute->type === 'color' && $productAttribute->hex_code)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Código de Color</label>
                                    <div class="font-monospace">{{ $productAttribute->hex_code }}</div>
                                </div>
                                
                                @if(isset($productAttribute->metadata['luminosity']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Luminosidad</label>
                                        <div>{{ number_format($productAttribute->metadata['luminosity'], 3) }}</div>
                                    </div>
                                @endif
                            @endif
                            
                            @if($productAttribute->type === 'ink')
                                @if($productAttribute->hex_code)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Color de la Tinta</label>
                                        <div class="font-monospace">{{ $productAttribute->hex_code }}</div>
                                    </div>
                                @endif
                                
                                @if(isset($productAttribute->metadata['opacity']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Opacidad</label>
                                        <div class="text-capitalize">{{ $productAttribute->metadata['opacity'] }}</div>
                                    </div>
                                @endif
                                
                                @if(isset($productAttribute->metadata['is_metallic']) && $productAttribute->metadata['is_metallic'])
                                    <div class="mb-3">
                                        <span class="badge bg-warning">Tinta Metálica</span>
                                    </div>
                                @endif
                            @endif

                            @if($productAttribute->type === 'ink_color')
                                @if($productAttribute->hex_code)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Color de Tinta</label>
                                        <div class="font-monospace">{{ $productAttribute->hex_code }}</div>
                                    </div>
                                @endif

                                @if(isset($productAttribute->metadata['color_family']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Familia de Color</label>
                                        <div class="text-capitalize">{{ $productAttribute->metadata['color_family'] }}</div>
                                    </div>
                                @endif

                                @if(isset($productAttribute->metadata['is_metallic']) && $productAttribute->metadata['is_metallic'])
                                    <span class="badge bg-warning me-1">Metálica</span>
                                @endif
                                @if(isset($productAttribute->metadata['is_fluorescent']) && $productAttribute->metadata['is_fluorescent'])
                                    <span class="badge bg-success">Fluorescente</span>
                                @endif
                            @endif

                            @if($productAttribute->type === 'cliche')
                                @if(isset($productAttribute->metadata['cliche_type']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Tipo de Cliché</label>
                                        <div class="text-capitalize">{{ $productAttribute->metadata['cliche_type'] }}</div>
                                    </div>
                                @endif
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>El precio del cliché se configura mediante dependencias.</small>
                                </div>
                            @endif

                            @if($productAttribute->type === 'quantity')
                                @if(isset($productAttribute->metadata['packaging']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Empaquetado</label>
                                        <div>{{ $productAttribute->metadata['packaging'] }}</div>
                                    </div>
                                @endif
                                
                                @if(isset($productAttribute->metadata['unit_price']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Precio Unitario</label>
                                        <div>€{{ number_format($productAttribute->metadata['unit_price'], 3) }}</div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impacto en precios -->

            <!-- Certificaciones -->
            @if(isset($productAttribute->metadata['certifications']) && count($productAttribute->metadata['certifications']) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-award"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Certificaciones</h5>
                            <small>Estándares y certificados</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($productAttribute->metadata['certifications'] as $certification)
                            <span class="badge bg-success fs-6">{{ $certification }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <!-- Estado -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-toggle-on"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Estado</h5>
                            <small>Configuración actual</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-light rounded me-3" style="width: 32px; height: 32px;">
                            <i class="bi bi-power {{ $productAttribute->active ? 'text-success' : 'text-secondary' }}"></i>
                        </div>
                        <div>
                            <div class="fw-medium">
                                @if($productAttribute->active)
                                    Activo
                                @else
                                    Inactivo
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($productAttribute->active)
                                    Disponible para usar
                                @else
                                    No disponible
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-light rounded me-3" style="width: 32px; height: 32px;">
                            <i class="bi bi-star {{ $productAttribute->is_recommended ? 'text-warning' : 'text-muted' }}"></i>
                        </div>
                        <div>
                            <div class="fw-medium">
                                @if($productAttribute->is_recommended)
                                    Recomendado
                                @else
                                    Normal
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($productAttribute->is_recommended)
                                    Se destaca como opción recomendada
                                @else
                                    Opción estándar
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dependencias -->
            @php
                $parentDeps = $productAttribute->parentDependencies;
                $childDeps = $productAttribute->childDependencies;
            @endphp
            @if($parentDeps->count() > 0 || $childDeps->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Dependencias</h5>
                            <small>Relaciones con otros atributos</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($parentDeps->count() > 0)
                        <div class="mb-3">
                            <h6 class="text-muted">Como Padre ({{ $parentDeps->count() }})</h6>
                            @foreach($parentDeps->take(3) as $dep)
                                <div class="small mb-1">
                                    <i class="bi bi-arrow-right text-primary"></i>
                                    {{ $dep->dependentAttribute->name }}
                                    <span class="badge badge-sm bg-secondary">{{ $dep->condition_type }}</span>
                                </div>
                            @endforeach
                            @if($parentDeps->count() > 3)
                                <small class="text-muted">... y {{ $parentDeps->count() - 3 }} más</small>
                            @endif
                        </div>
                    @endif

                    @if($childDeps->count() > 0)
                        <div class="mb-3">
                            <h6 class="text-muted">Como Dependiente ({{ $childDeps->count() }})</h6>
                            @foreach($childDeps->take(3) as $dep)
                                <div class="small mb-1">
                                    <i class="bi bi-arrow-left text-warning"></i>
                                    {{ $dep->parentAttribute->name }}
                                    <span class="badge badge-sm bg-secondary">{{ $dep->condition_type }}</span>
                                </div>
                            @endforeach
                            @if($childDeps->count() > 3)
                                <small class="text-muted">... y {{ $childDeps->count() - 3 }} más</small>
                            @endif
                        </div>
                    @endif

                    <a href="{{ route('admin.attribute-dependencies.index', ['search' => $productAttribute->name]) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>Ver Todas
                    </a>
                </div>
            </div>
            @endif

            <!-- Información del sistema -->
            <div class="card shadow-sm">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Información del Sistema</h5>
                            <small>Datos técnicos</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">ID</label>
                        <div class="font-monospace">{{ $productAttribute->id }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Creado</label>
                        <div class="small">{{ $productAttribute->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Última modificación</label>
                        <div class="small">{{ $productAttribute->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    @if($productAttribute->metadata)
                        <div class="mb-3">
                            <label class="form-label text-muted">Metadata</label>
                            <details>
                                <summary class="small text-primary" style="cursor: pointer;">Ver JSON</summary>
                                <pre class="small mt-2 p-2 bg-light rounded"><code>{{ json_encode($productAttribute->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                            </details>
                        </div>
                    @endif
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
            const attributeId = this.dataset.attributeId;
            
            if (confirm('¿Desea duplicar este atributo?')) {
                // Crear formulario para duplicar
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/product-attributes/${attributeId}/duplicate`;
                
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

@push('styles')
<style>
.color-preview, .ink-preview {
    transition: all 0.3s ease;
}

.color-preview:hover, .ink-preview:hover {
    transform: scale(1.02);
}

.icon-square {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.attribute-display {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

details summary {
    outline: none;
}

.badge-sm {
    font-size: 0.65em;
}
</style>
@endpush