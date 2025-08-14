@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid">
    <x-admin.breadcrumb :items="$breadcrumbs" />

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3">{{ $product->name }}</h1>
            <div>
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

            <x-admin.card title="Especificaciones">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Materiales:</strong></div>
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

                <div class="row">
                    <div class="col-md-4"><strong>Colores de Impresión:</strong></div>
                    <div class="col-md-8">{{ $product->print_colors_count }} colores</div>
                </div>
            </x-admin.card>

            <x-admin.card title="Colores y Tallas">
                <div class="mb-3">
                    <strong>Colores Disponibles:</strong>
                    <div class="mt-2">
                        @php
                            $availableColors = \App\Models\AvailableColor::whereIn('name', $product->colors ?? [])->get();
                        @endphp
                        @foreach($availableColors as $color)
                            <span class="badge me-2 mb-2" 
                                  style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                {{ $color->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Colores de Impresión:</strong>
                    <div class="mt-2">
                        @php
                            $printColors = \App\Models\AvailablePrintColor::whereIn('name', $product->print_colors ?? [])->get();
                        @endphp
                        @foreach($printColors as $color)
                            <span class="badge me-2 mb-2" 
                                  style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                {{ $color->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div>
                    <strong>Tallas Disponibles:</strong>
                    <div class="mt-2">
                        @foreach($product->sizes ?? [] as $size)
                            <span class="badge bg-secondary me-2">{{ $size }}</span>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Tabla de Precios">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
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
                    <div class="text-center mb-3 position-relative">
                        <model-viewer 
                            src="{{ $product->getModel3dUrl() }}" 
                            alt="{{ $product->name }} - Modelo 3D"
                            auto-rotate 
                            camera-controls 
                            shadow-intensity="1"
                            exposure="1"
                            ar 
                            ar-modes="webxr scene-viewer quick-look"
                            style="width: 100%; height: 400px; background: linear-gradient(to bottom, #ffffff, #f8f9fa); border-radius: 0.375rem; border: 1px solid #dee2e6;"
                            loading="eager"
                            reveal="auto"
                            poster="{{ $product->getFirstImageUrl() }}">
                            <div class="slot">
                                <div class="progress-bar hide" slot="progress-bar">
                                    <div class="update-bar"></div>
                                </div>
                            </div>
                        </model-viewer>
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-info">
                                <i class="bi bi-box"></i> 3D
                            </span>
                        </div>
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
    model-viewer {
        --poster-color: transparent;
    }
    
    model-viewer::part(default-progress-bar) {
        height: 4px;
        background-color: #E3E3E3;
    }
    
    model-viewer::part(default-progress-bar-bar) {
        background-color: var(--primary-color);
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

// Monitor model loading
document.addEventListener('DOMContentLoaded', function() {
    const modelViewer = document.querySelector('model-viewer');
    if (modelViewer) {
        modelViewer.addEventListener('load', () => {
            console.log('3D model loaded successfully');
        });
        
        modelViewer.addEventListener('error', (event) => {
            console.error('Error loading 3D model:', event);
        });
        
        modelViewer.addEventListener('model-visibility', (event) => {
            console.log('Model visibility:', event.detail.visible);
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
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