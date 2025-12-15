@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid">
    <x-admin.breadcrumb :items="$breadcrumbs" />

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3">{{ $product->name }}</h1>
            <div>
                @if($product->has_configurator && $product->productAttributeValues->count() > 0)
                    <a href="{{ route('admin.products.attribute-images', $product) }}" class="btn btn-success me-2">
                        <i class="bi bi-images"></i> Imágenes por Atributo
                    </a>
                @endif
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <x-admin.card title="Información Básica">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>SKU:</strong></div>
                    <div class="col-md-9"><code>{{ $product->sku }}</code></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Slug:</strong></div>
                    <div class="col-md-9">{{ $product->slug }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3"><strong>Estado:</strong></div>
                    <div class="col-md-9">
                        @if($product->active)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3"><strong>Categoría:</strong></div>
                    <div class="col-md-9">{{ $product->category->name }} / {{ $product->subcategory->name }}</div>
                </div>

                @if($product->description)
                    <div class="row">
                        <div class="col-md-3"><strong>Descripción:</strong></div>
                        <div class="col-md-9">{{ $product->description }}</div>
                    </div>
                @endif
            </x-admin.card>

            <x-admin.card title="Especificaciones Técnicas" class="border-secondary">
                <!-- Información del Configurador -->
                <div class="row mb-4 p-3 bg-light rounded">
                    <div class="col-12 mb-2">
                        <h6 class="text-primary">
                            <i class="bi bi-gear me-2"></i>Configuración del Producto
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Estado Configurador:</small><br>
                        @if($product->has_configurator)
                            <span class="badge bg-success">Habilitado</span>
                        @else
                            <span class="badge bg-warning">Deshabilitado</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Precio Base:</small><br>
                        <strong class="text-success">€{{ number_format($product->configurator_base_price ?? 0, 2) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Máx. Colores Impresión:</small><br>
                        <span class="badge bg-info">{{ $product->max_print_colors ?? 1 }}</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Subida de Archivos:</small><br>
                        @if($product->allow_file_upload)
                            <span class="badge bg-success">Permitida</span>
                        @else
                            <span class="badge bg-secondary">No permitida</span>
                        @endif
                    </div>
                </div>

                <!-- Especificaciones Legacy -->
                <div class="row mb-4 p-3 bg-white rounded border-top mt-4">
                    <div class="col-12 mb-3">
                        <h6 class="text-secondary">
                            <i class="bi bi-archive me-2"></i>Especificaciones Legacy
                        </h6>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Materiales (Legacy):</strong></div>
                    <div class="col-md-8">{{ $product->materials ? implode(', ', $product->materials) : 'No especificado' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Sistemas de Impresión:</strong></div>
                    <div class="col-md-8">{{ $product->printingSystems->pluck('name')->implode(', ') ?: 'No especificado' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Número de Caras:</strong></div>
                    <div class="col-md-8">{{ $product->face_count }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Colores de Impresión (Legacy):</strong></div>
                    <div class="col-md-8">{{ $product->print_colors_count }} colores</div>
                </div>

                <!-- Resumen del Sistema de Atributos -->
                @if($product->productAttributes && $product->productAttributes->count() > 0)
                    <div class="mt-4 p-3 border-top">
                        <h6 class="text-info mb-3">
                            <i class="bi bi-collection me-2"></i>Resumen del Sistema de Atributos
                        </h6>
                        <div class="row">
                            @php
                                $attributeSummary = $product->productAttributes->groupBy('type');
                            @endphp
                            @foreach($attributeSummary as $type => $attributes)
                                <div class="col-md-4 mb-2">
                                    <small class="text-muted">{{ ucfirst($type) }}s:</small><br>
                                    <span class="badge bg-outline-secondary">{{ $attributes->count() }} disponibles</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-admin.card>

            <x-admin.card title="Grupos de Atributos del Configurador" class="border-info">
                @if($product->productAttributes && $product->productAttributes->count() > 0)
                    @php
                        $attributesByGroup = $product->productAttributes->groupBy('attributeGroup.name');
                        $totalAttributes = $product->productAttributes->count();
                        $activeGroups = $attributesByGroup->count();
                    @endphp
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-collection text-info me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">Total de Atributos Configurados</h6>
                                    <span class="badge bg-info fs-6">{{ $totalAttributes }} atributos</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-folder text-primary me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">Grupos Activos</h6>
                                    <span class="badge bg-primary fs-6">{{ $activeGroups }} grupos</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($attributesByGroup as $groupName => $attributes)
                            @php
                                $group = $attributes->first()->attributeGroup;
                                $groupIcon = match($group->type ?? 'default') {
                                    'color' => 'palette-fill',
                                    'material' => 'boxes',
                                    'size' => 'rulers',
                                    'ink' => 'droplet-fill',
                                    'quantity' => 'hash',
                                    'system' => 'gear-fill',
                                    default => 'collection'
                                };
                                $groupColor = match($group->type ?? 'default') {
                                    'color' => 'primary',
                                    'material' => 'secondary',
                                    'size' => 'info',
                                    'ink' => 'warning',
                                    'quantity' => 'success',
                                    'system' => 'dark',
                                    default => 'light'
                                };
                            @endphp
                            
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 border-{{ $groupColor }}">
                                    <div class="card-header bg-{{ $groupColor }} text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="bi bi-{{ $groupIcon }} me-2"></i>
                                                {{ $groupName }}
                                            </h6>
                                            <div class="d-flex gap-1">
                                                @if($group->is_required ?? false)
                                                    <span class="badge bg-danger">Requerido</span>
                                                @endif
                                                @if($group->allow_multiple ?? false)
                                                    <span class="badge bg-success">Múltiple</span>
                                                @endif
                                                <span class="badge bg-light text-dark">{{ $attributes->count() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if($group->description)
                                            <p class="text-muted small mb-3">{{ $group->description }}</p>
                                        @endif
                                        
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($attributes->sortBy('name') as $attribute)
                                                <div class="d-flex align-items-center bg-light rounded px-3 py-2">
                                                    @if($attribute->type === 'color' && $attribute->hex_code)
                                                        <span class="rounded-circle me-2" 
                                                              style="width: 16px; height: 16px; background-color: {{ $attribute->hex_code }}; border: 1px solid #ddd;"></span>
                                                    @endif
                                                    
                                                    <span class="me-2">{{ $attribute->name }}</span>
                                                    
                                                    @if($attribute->price_modifier != 0)
                                                        <small class="badge bg-{{ $attribute->price_modifier > 0 ? 'danger' : 'success' }}">
                                                            {{ $attribute->price_modifier > 0 ? '+' : '' }}€{{ number_format($attribute->price_modifier, 2) }}
                                                        </small>
                                                    @endif
                                                    
                                                    @if($attribute->pivot && $attribute->pivot->is_default)
                                                        <i class="bi bi-star-fill text-warning ms-1" title="Por defecto"></i>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        @if($group->min_selections || $group->max_selections)
                                            <div class="mt-3 p-2 bg-light rounded">
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    Selecciones: 
                                                    @if($group->min_selections && $group->max_selections)
                                                        {{ $group->min_selections }} - {{ $group->max_selections }}
                                                    @elseif($group->min_selections)
                                                        Mínimo {{ $group->min_selections }}
                                                    @elseif($group->max_selections)
                                                        Máximo {{ $group->max_selections }}
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle me-3"></i>
                        <div>
                            <strong>No hay atributos configurados</strong><br>
                            <small>Configure los atributos del producto en la página de edición para habilitar el sistema de configurador.</small>
                        </div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm ms-auto">
                            <i class="bi bi-gear"></i> Configurar
                        </a>
                    </div>
                @endif
            </x-admin.card>

            @if($dependencies && $dependencies->count() > 0)
                <x-admin.card title="Dependencias de Atributos" class="border-warning">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-diagram-3 me-2"></i>
                        <strong>{{ $dependencies->count() }} dependencia(s) configurada(s)</strong> - 
                        Las dependencias controlan la disponibilidad de atributos según otras selecciones
                    </div>

                    <div class="row">
                        @foreach($dependencies->groupBy('condition_type') as $conditionType => $deps)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-{{ $conditionType === 'allows' ? 'check-circle' : ($conditionType === 'blocks' ? 'x-circle' : ($conditionType === 'requires' ? 'exclamation-circle' : 'currency-euro')) }} me-2"></i>
                                            {{ ucfirst($conditionType) }} ({{ $deps->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        @foreach($deps as $dependency)
                                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                                <div class="small">
                                                    <strong>{{ $dependency->parentAttribute->name ?? 'N/A' }}</strong>
                                                    @if($dependency->dependentAttribute)
                                                        <i class="bi bi-plus mx-1 text-muted"></i>
                                                        <span>{{ $dependency->dependentAttribute->name }}</span>
                                                    @endif
                                                    @if($dependency->thirdAttribute)
                                                        <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                        <span class="text-primary">{{ $dependency->thirdAttribute->name }}</span>
                                                    @elseif($dependency->dependentAttribute)
                                                        {{-- Solo 2 atributos, sin tercero --}}
                                                    @elseif($dependency->condition_type === 'price_modifier')
                                                        <span class="text-muted ms-2">(Modificador de precio)</span>
                                                    @else
                                                        <span class="text-muted ms-2">(Regla de precio)</span>
                                                    @endif
                                                </div>
                                                @if($dependency->price_impact != 0 || $dependency->price_modifier != 0)
                                                    <span class="badge bg-{{ ($dependency->price_impact > 0 || $dependency->price_modifier > 0) ? 'danger' : 'success' }}">
                                                        @if($dependency->price_modifier != 0)
                                                            {{ $dependency->price_modifier > 0 ? '+' : '' }}{{ number_format($dependency->price_modifier, 2) }}%
                                                        @else
                                                            {{ $dependency->price_impact > 0 ? '+' : '' }}€{{ number_format($dependency->price_impact, 2) }}
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            @if($priceRules && $priceRules->count() > 0)
                <x-admin.card title="Reglas de Precio Dinámicas" class="border-danger">
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-calculator me-2"></i>
                        <strong>{{ $priceRules->count() }} regla(s) de precio activa(s)</strong> - 
                        Estas reglas modifican el precio final según las selecciones del usuario
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Acción</th>
                                    <th>Valor</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($priceRules->sortBy('priority') as $rule)
                                    <tr>
                                        <td>
                                            <strong>{{ $rule->name }}</strong>
                                            @if($rule->description)
                                                <br><small class="text-muted">{{ Str::limit($rule->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($rule->rule_type) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $rule->action_type)) }}</span>
                                        </td>
                                        <td>
                                            @if(str_contains($rule->action_type, 'percentage'))
                                                {{ $rule->action_value }}%
                                            @else
                                                €{{ number_format($rule->action_value, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">{{ $rule->priority }}</span>
                                        </td>
                                        <td>
                                            @if($rule->active)
                                                <span class="badge bg-success">Activa</span>
                                            @else
                                                <span class="badge bg-secondary">Inactiva</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            @endif

            <x-admin.card title="Sistema de Precios" class="border-primary">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-currency-euro text-primary me-3 fs-3"></i>
                            <div>
                                <h5 class="mb-1">Precio Base del Configurador</h5>
                                <span class="text-success fs-4 fw-bold">€{{ number_format($product->configurator_base_price ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Este es el precio base antes de aplicar modificadores por atributos seleccionados
                        </small>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-gear me-1"></i> Modificadores de Precio
                            </h6>
                            <div class="d-flex justify-content-between mb-2">
                                <small>Por Atributos:</small>
                                <small class="text-info">Variable según selección</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small>Por Cantidad:</small>
                                <small class="text-warning">Según reglas configuradas</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>Por Combinaciones:</small>
                                <small class="text-success">Según dependencias</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($product->pricing && $product->pricing->count() > 0)
                    <div class="mt-3">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-table me-1"></i> Tabla de Precios por Cantidad (Sistema Legacy)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cantidad</th>
                                        <th>Precio Total</th>
                                        <th>Precio Unitario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->pricing->sortBy('quantity_from') as $price)
                                        <tr>
                                            <td>{{ $price->quantity_from }} - {{ $price->quantity_to }}</td>
                                            <td>€{{ number_format($price->price, 2) }}</td>
                                            <td>€{{ number_format($price->unit_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                            Esta tabla es complementaria. El precio final se calcula con el sistema de configurador.
                        </small>
                    </div>
                @endif
            </x-admin.card>
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <x-admin.card title="Imágenes">
                @if($product->images && count($product->images) > 0)
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($product->getImagesUrls() as $index => $imageUrl)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ $imageUrl }}" class="d-block w-100" alt="Imagen {{ $index + 1 }}">
                                </div>
                            @endforeach
                        </div>
                        @if(count($product->images) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        @endif
                    </div>
                @else
                    <p class="text-muted text-center">No hay imágenes disponibles</p>
                @endif
            </x-admin.card>

            @if($product->model_3d_file)
                <x-admin.card title="Modelo 3D">
                    <div class="mb-3" style="background-color: #f0f0f0; border-radius: 8px; overflow: hidden;">
                        <model-viewer
                            src="{{ $product->getModel3dUrl() }}"
                            alt="{{ $product->name }} - Modelo 3D"
                            camera-controls
                            auto-rotate
                            style="width: 100%; height: 400px; display: block; background-color: #e0e0e0;"
                            id="product-model-viewer"
                            loading="eager"
                            reveal="auto">
                        </model-viewer>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" onclick="resetCamera()">
                            <i class="bi bi-arrow-counterclockwise"></i> Resetear Vista
                        </button>
                        <a href="{{ $product->getModel3dUrl() }}" class="btn btn-outline-primary btn-sm" download>
                            <i class="bi bi-download"></i> Descargar
                        </a>
                    </div>
                    <small class="text-muted d-block text-center mt-2">
                        <i class="bi bi-info-circle"></i> Arrastra para rotar, pellizca para zoom, mantén presionado para mover
                    </small>
                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-lightbulb"></i> 
                            Si el modelo no se muestra correctamente, asegúrate de que el archivo GLB/GLTF sea válido y contenga geometría 3D.
                        </small>
                    </div>
                </x-admin.card>
            @endif

            <x-admin.card title="Información Adicional" class="">
                <p class="mb-2">
                    <small class="text-muted">Creado:</small><br>
                    {{ $product->created_at->format('d/m/Y H:i') }}
                </p>
                <p class="mb-0">
                    <small class="text-muted">Última actualización:</small><br>
                    {{ $product->updated_at->format('d/m/Y H:i') }}
                </p>
            </x-admin.card>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-12">
            <hr>
            <div class="d-flex justify-content-between">
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-delete" 
                            data-item-name="{{ $product->name }}">
                        <i class="bi bi-trash"></i> Eliminar Producto
                    </button>
                </form>
                
                <div>
                    <a href="{{ route('admin.configurator.show', $product) }}" class="btn btn-success me-2">
                        <i class="bi bi-gear-fill"></i> Abrir Configurador
                    </a>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #product-model-viewer {
        --poster-color: transparent;
        border-radius: 8px;
        background: #f8f9fa !important;
    }
    
    .progress-bar {
        display: block;
        width: 100%;
        height: 4px;
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 2px;
        overflow: hidden;
        position: absolute;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
    }
    
    .progress-bar.hide {
        visibility: hidden;
        transition: visibility 0.3s;
    }
    
    .update-bar {
        background-color: var(--primary-color);
        height: 100%;
        width: 0%;
        border-radius: 2px;
        animation: progress-bar 2s linear infinite;
    }
    
    @keyframes progress-bar {
        from { width: 0%; }
        to { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
// Reset 3D model camera function
function resetCamera() {
    const modelViewer = document.querySelector('model-viewer');
    if (modelViewer) {
        modelViewer.resetTurntableRotation();
        modelViewer.fieldOfView = 45;
        modelViewer.cameraOrbit = '0deg 75deg 105%';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 [3D Viewer] DOMContentLoaded');

    // Check if model-viewer script is loaded
    if (customElements.get('model-viewer')) {
        console.log('✅ [3D Viewer] model-viewer web component is registered');
    } else {
        console.error('❌ [3D Viewer] model-viewer web component NOT registered');
    }

    // Wait a bit for model-viewer to load
    setTimeout(() => {
        const modelViewer = document.querySelector('#product-model-viewer');
        console.log('🔍 [3D Viewer] Looking for model viewer...');

        if (modelViewer) {
            console.log('✅ [3D Viewer] Model viewer element found!');
            console.log('📦 [3D Viewer] Source URL:', modelViewer.src);
            console.log('📐 [3D Viewer] Element dimensions:', {
                width: modelViewer.offsetWidth,
                height: modelViewer.offsetHeight,
                display: window.getComputedStyle(modelViewer).display
            });

            modelViewer.addEventListener('load', () => {
                console.log('✅ [3D Viewer] Model loaded successfully!');
            });

            modelViewer.addEventListener('error', (event) => {
                console.error('❌ [3D Viewer] Model load error:', event);
                console.error('❌ [3D Viewer] Error details:', event.detail);
            });

            modelViewer.addEventListener('progress', (event) => {
                const percent = event.detail.totalProgress * 100;
                console.log(`📊 [3D Viewer] Loading: ${percent.toFixed(0)}%`);
            });
        } else {
            console.error('❌ [3D Viewer] Model viewer element NOT found in DOM');
            console.log('🔍 [3D Viewer] Checking if condition passed...');
            @if($product->model_3d_file)
                console.log('🔍 [3D Viewer] Product has model_3d_file: YES');
            @else
                console.log('🔍 [3D Viewer] Product has model_3d_file: NO');
            @endif
        }
    }, 500);

    // Existing code for delete button...
    // Manejar botón de eliminar producto con SweetAlert2
    const deleteButton = document.querySelector('.btn-delete');
    if (deleteButton) {
        deleteButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productName = this.dataset.itemName;
            const form = this.closest('form');
            const productId = form.action.split('/').pop();
            
            // Mostrar loading mientras se verifica dependencias
            Swal.fire({
                title: 'Verificando dependencias...',
                text: 'Por favor espere',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Verificar dependencias via AJAX
            fetch(`{{ url('admin/products') }}/${productId}/dependencies`)
                .then(response => response.json())
                .then(data => {
                    let html = `¿Está seguro de eliminar el producto <strong>"${productName}"</strong>?`;
                    let canDelete = data.can_delete;
                    
                    if (!canDelete) {
                        html += `<br><br><div class="alert alert-warning text-start mt-3 mb-0">`;
                        html += `<strong><i class="bi bi-exclamation-triangle me-2"></i>¡Atención!</strong><br>`;
                        html += `Este producto está incluido en <strong>${data.order_items_count} pedido(s)</strong>:<br><br>`;
                        
                        // Mostrar hasta 5 pedidos
                        data.orders.slice(0, 5).forEach(order => {
                            html += `• Pedido: ${order.order_number}<br>`;
                        });
                        
                        if (data.order_items_count > 5) {
                            html += `• Y ${data.order_items_count - 5} pedido(s) más<br>`;
                        }
                        
                        html += `<br><small>Los productos con historial de pedidos no pueden eliminarse para mantener la integridad de los datos.</small>`;
                        html += `</div>`;
                    }
                    
                    Swal.fire({
                        title: canDelete ? '¿Eliminar Producto?' : 'No se puede eliminar',
                        html: html,
                        icon: canDelete ? 'warning' : 'error',
                        showCancelButton: true,
                        confirmButtonColor: canDelete ? '#dc3545' : '#6c757d',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: canDelete ? '<i class="bi bi-trash me-2"></i>Sí, eliminar' : '<i class="bi bi-check me-2"></i>Entendido',
                        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
                        focusCancel: true,
                        customClass: {
                            icon: canDelete ? 'swal-icon-warning' : 'swal-icon-error'
                        }
                    }).then((result) => {
                        if (result.isConfirmed && canDelete) {
                            form.submit();
                        }
                    });
                })
                .catch(error => {
                    console.error('Error verificando dependencias:', error);
                    Swal.fire({
                        title: '¿Eliminar Producto?',
                        html: `¿Está seguro de eliminar el producto <strong>"${productName}"</strong>?<br><small class="text-muted">No se pudieron verificar las dependencias</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash me-2"></i>Sí, eliminar',
                        cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
                        focusCancel: true,
                        customClass: {
                            icon: 'swal-icon-warning'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
        });
    }
});
</script>
@endpush